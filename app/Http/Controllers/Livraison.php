<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\RetourCasier;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Livraison extends Controller
{
    public function indexLivraisons()
    {
        $depotId = (int) auth()->user()->depot_id;

        $commandesAPreparer = Vente::with(['client', 'detailVentes.produit.fournisseur'])
            ->where('statut', 'payer')
            ->whereHas('employe', fn ($query) => $query->where('depot_id', $depotId))
            ->orderBy('updated_at')
            ->get();

        $retoursLiees = RetourCasier::with('fournisseur')
            ->where('depot_id', $depotId)
            ->whereNotNull('vente_id')
            ->latest()
            ->get()
            ->groupBy('vente_id');

        $ventesAvecRetours = Vente::with(['client', 'detailVentes.produit.fournisseur'])
            ->whereIn('id', $retoursLiees->keys())
            ->get()
            ->keyBy('id');

        $historiqueRetours = $retoursLiees
            ->map(function (Collection $retours, $venteId) use ($ventesAvecRetours) {
                $vente = $ventesAvecRetours->get($venteId);
                if (!$vente) {
                    return null;
                }

                $rendusParFournisseur = $retours->groupBy('fournisseur_id')
                    ->map(fn (Collection $rows) => (int) $rows->sum('quantite_rendue'));
                $attendusParFournisseur = $vente->detailVentes
                    ->filter(fn ($detail) => $detail->produit
                        && preg_match('/casier|\\bcs\\b/i', $detail->produit->format_p))
                    ->groupBy(fn ($detail) => $detail->produit->id_fournisseur)
                    ->map(fn (Collection $rows) => (int) floor($rows->sum('quantite')));

                $supplierIds = $attendusParFournisseur->keys()
                    ->merge($rendusParFournisseur->keys())
                    ->unique();
                $supplierNames = Fournisseur::whereIn('id_fournisseur', $supplierIds)
                    ->pluck('nom_fournisseur', 'id_fournisseur');

                $detailsAttendus = $attendusParFournisseur->map(
                    fn ($quantity, $supplierId) => $quantity . ' ' . strtoupper($supplierNames->get($supplierId, 'Fournisseur'))
                )->implode(' | ');
                $detailsRendus = $rendusParFournisseur->map(
                    fn ($quantity, $supplierId) => $quantity . ' ' . strtoupper($supplierNames->get($supplierId, 'Fournisseur'))
                )->implode(' | ');
                $detailsRestants = $supplierIds->map(function ($supplierId) use (
                    $attendusParFournisseur,
                    $rendusParFournisseur,
                    $supplierNames
                ) {
                    $expected = (int) $attendusParFournisseur->get($supplierId, 0);
                    $returned = (int) $rendusParFournisseur->get($supplierId, 0);
                    $difference = $expected - $returned;
                    $status = $difference > 0
                        ? $difference . ' à rendre'
                        : ($difference < 0 ? '+' . abs($difference) . ' surplus' : 'soldé');

                    return strtoupper($supplierNames->get($supplierId, 'Fournisseur')) . ' : ' . $status;
                })->implode(' | ');

                return (object) [
                    'id_vente' => $vente->id,
                    'code_facture' => $vente->code_facture,
                    'date_livraison' => $vente->updated_at,
                    'nom_client' => $vente->client?->nom_u ?? 'Client comptoir',
                    'total_rendu' => $rendusParFournisseur->sum(),
                    'detail_rendus_text' => $detailsRendus ?: 'Aucun casier rendu',
                    'total_achete' => $attendusParFournisseur->sum(),
                    'detail_attendus_text' => $detailsAttendus ?: 'Aucun casier vendu',
                    'detail_restant_text' => $detailsRestants ?: 'Aucun casier consigné',
                ];
            })
            ->filter()
            ->sortByDesc('date_livraison')
            ->take(20)
            ->values();

        $retoursLibres = DB::table('retours_casiers')
            ->leftJoin('users as clients', 'retours_casiers.client_id', '=', 'clients.id_u')
            ->join('fournisseurs', 'retours_casiers.fournisseur_id', '=', 'fournisseurs.id_fournisseur')
            ->where('retours_casiers.depot_id', $depotId)
            ->whereNull('retours_casiers.vente_id')
            ->orderByDesc('retours_casiers.created_at')
            ->limit(30)
            ->get([
                'retours_casiers.id_rc',
                'retours_casiers.quantite_rendue',
                'retours_casiers.created_at',
                'clients.nom_u as nom_client',
                'fournisseurs.nom_fournisseur',
            ]);

        $fournisseurs = Fournisseur::orderBy('nom_fournisseur')->get();
        $clients = User::query()
            ->where('role_u', 'client')
            ->orderBy('nom_u')
            ->get(['id_u', 'nom_u']);

        return view('layout.personnel.livraison', compact(
            'commandesAPreparer',
            'historiqueRetours',
            'retoursLibres',
            'fournisseurs',
            'clients'
        ));
    }

    public function validerLivraisonEtRetourCasiers(Request $request, $id_vente)
    {
        $data = $request->validate([
            'rendus' => 'required|array',
            'rendus.*' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($data, $id_vente) {
            $userStaff = auth()->user();
            $depotId = (int) $userStaff->depot_id;
            $vente = Vente::query()
                ->where('statut', 'payer')
                ->whereHas('employe', fn ($query) => $query->where('depot_id', $depotId))
                ->lockForUpdate()
                ->find($id_vente);

            if (!$vente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette facture est déjà livrée, non payée ou rattachée à un autre dépôt.',
                ], 404);
            }

            $fournisseurIds = array_map('intval', array_keys($data['rendus']));
            if (Fournisseur::whereIn('id_fournisseur', $fournisseurIds)->count() !== count(array_unique($fournisseurIds))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un fournisseur sélectionné est introuvable.',
                ], 422);
            }

            $totalRendu = 0;
            foreach ($data['rendus'] as $fournisseurId => $quantite) {
                $quantite = (int) $quantite;
                if ($quantite === 0) {
                    continue;
                }

                DB::table('retours_casiers')->insert([
                    'client_id' => $vente->client_id,
                    'employe_id' => $userStaff->id_u,
                    'depot_id' => $depotId,
                    'fournisseur_id' => (int) $fournisseurId,
                    'vente_id' => $vente->id,
                    'quantite_rendue' => $quantite,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Fournisseur::where('id_fournisseur', $fournisseurId)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stock_vides_f', $quantite);

                DB::table('fournisseur_depot_stocks')->insertOrIgnore([
                    'depot_id' => $depotId,
                    'fournisseur_id' => (int) $fournisseurId,
                    'stock_vides' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('fournisseur_depot_stocks')
                    ->where('depot_id', $depotId)
                    ->where('fournisseur_id', $fournisseurId)
                    ->increment('stock_vides', $quantite, ['updated_at' => now()]);

                $totalRendu += $quantite;
            }

            $vente->update(['statut' => 'livrer']);

            if ($vente->client_id) {
                DB::table('notifications_systeme')->insert([
                    'user_id' => $vente->client_id,
                    'depot_id' => $depotId,
                    'titre_n' => 'Commande récupérée',
                    'message_n' => "Le bon {$vente->code_facture} a quitté le dépôt. Casiers reçus : {$totalRendu}.",
                    'icone_n' => 'bi-truck',
                    'couleur_n' => 'text-info',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Sortie de quai validée. {$totalRendu} casier(s) enregistré(s) au dépôt.",
            ]);
        });
    }

    public function enregistrerRetourLibreCasiers(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'nullable|integer|exists:users,id_u',
            'rendus' => 'required|array',
            'rendus.*' => 'required|integer|min:0',
        ]);

        $fournisseurIds = array_map('intval', array_keys($data['rendus']));
        if (Fournisseur::whereIn('id_fournisseur', $fournisseurIds)->count() !== count(array_unique($fournisseurIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Un fournisseur sélectionné est introuvable.',
            ], 422);
        }

        $totalRendu = array_sum(array_map('intval', $data['rendus']));
        if ($totalRendu < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Saisissez au moins un casier à restituer.',
            ], 422);
        }

        return DB::transaction(function () use ($data, $fournisseurIds, $totalRendu) {
            $userStaff = auth()->user();
            $clientId = $data['client_id'] ?? null;
            $client = $clientId
                ? User::query()
                    ->where('role_u', 'client')
                    ->lockForUpdate()
                    ->find($clientId)
                : null;

            if ($clientId && !$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le compte client est introuvable.',
                ], 422);
            }

            foreach ($data['rendus'] as $fournisseurId => $quantite) {
                $quantite = (int) $quantite;
                if ($quantite === 0) {
                    continue;
                }

                DB::table('retours_casiers')->insert([
                    'client_id' => $client?->id_u,
                    'employe_id' => $userStaff->id_u,
                    'depot_id' => $userStaff->depot_id,
                    'fournisseur_id' => (int) $fournisseurId,
                    'vente_id' => null,
                    'quantite_rendue' => $quantite,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Fournisseur::where('id_fournisseur', $fournisseurId)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stock_vides_f', $quantite);

                DB::table('fournisseur_depot_stocks')->insertOrIgnore([
                    'depot_id' => $userStaff->depot_id,
                    'fournisseur_id' => (int) $fournisseurId,
                    'stock_vides' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('fournisseur_depot_stocks')
                    ->where('depot_id', $userStaff->depot_id)
                    ->where('fournisseur_id', $fournisseurId)
                    ->increment('stock_vides', $quantite, ['updated_at' => now()]);
            }

            if ($client) {
                DB::table('notifications_systeme')->insert([
                    'user_id' => $client->id_u,
                    'depot_id' => $userStaff->depot_id,
                    'titre_n' => 'Restitution d’emballages',
                    'message_n' => "Le dépôt a enregistré la réception de {$totalRendu} casier(s) vide(s).",
                    'icone_n' => 'bi-arrow-left-right',
                    'couleur_n' => 'text-success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Restitution de {$totalRendu} casier(s) enregistrée au dépôt.",
            ]);
        });
    }
}
