<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Depot;
use App\Models\Vente;
use App\Models\DetailVente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\RetourCasier;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Session;
class Livraison extends Controller
{
    

/**
 * 🚚 PAGE CENTRALE : Affiche l'interface unifiée des Livraisons
 */
public function indexLivraisons()
{
    $depotId = auth()->user()->depot_id;

    // 1. Charger les factures PAYÉES (Prêtes à sortir du quai)
    $commandesAPreparer = Vente::with(['client', 'detailVentes.produit.fournisseur'])
        ->join('users', 'ventes.client_id', '=', 'users.id_u')
        ->where('users.depot_id', $depotId)
        ->where('ventes.statut', 'payer')
        ->select('ventes.*')
        ->orderBy('ventes.updated_at', 'asc')
        ->get();

        // 2. 📜 HISTORIQUE CONSOLIDÉ : Filtrage strict sur le mot "casier" pour isoler les consignes
            // 2. 📜 HISTORIQUE CONSOLIDÉ : Version corrigée pour MariaDB sans bug de parenthèses
    $historiqueRetours = DB::table('retours_casiers')
        ->join('ventes', 'retours_casiers.vente_id', '=', 'ventes.id')
        ->join('users as clients', 'retours_casiers.client_id', '=', 'clients.id_u')
        ->join('fournisseurs', 'retours_casiers.fournisseur_id', '=', 'fournisseurs.id_fournisseur')
        ->where('retours_casiers.depot_id', $depotId)
        ->select(
            'ventes.id as id_vente',
            'ventes.code_facture',
            'ventes.updated_at as date_livraison',
            'clients.nom_u as nom_client',
            
            // Somme brute des casiers rendus
            DB::raw('SUM(retours_casiers.quantite_rendue) as total_rendu'),
            
            // Composition du texte d'audit des rendus
            DB::raw("GROUP_CONCAT(CONCAT(retours_casiers.quantite_rendue, ' ', UPPER(fournisseurs.nom_fournisseur)) SEPARATOR ' | ') as detail_rendus_text"),
            
            // Somme brute des casiers initialement achetés
            DB::raw("(SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')) as total_achete"),
            
                       // 💡 REQUÊTE ULTIME : Génération de sous-badges HTML individuels brasserie par brasserie
            DB::raw("
                GROUP_CONCAT(
                    CONCAT(
                        '<span class=\"fw-bold font-monospace ',
                        IF(
                            (IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND p.id_fournisseur = fournisseurs.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - retours_casiers.quantite_rendue) > 0,
                            'text-danger', -- 🔴 Rouge si le client doit (Dette)
                            IF(
                                (IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND p.id_fournisseur = fournisseurs.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - retours_casiers.quantite_rendue) < 0,
                                'text-success', -- 🟢 Vert si le client a un surplus (Avance)
                                'text-muted' -- ⚪ Gris neutre si c'est égal à 0
                            )
                        ),
                        '\">',
                        IF(
                            (IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND p.id_fournisseur = fournisseurs.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - retours_casiers.quantite_rendue) < 0,
                            CONCAT('+', ABS(CAST(IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND p.id_fournisseur = fournisseurs.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - retours_casiers.quantite_rendue AS SIGNED))),
                            CAST(IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN produits p ON dv.produit_id = p.id_produit WHERE dv.vente_id = ventes.id AND p.id_fournisseur = fournisseurs.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - retours_casiers.quantite_rendue AS SIGNED)
                        ),
                        ' ',
                        UPPER(fournisseurs.nom_fournisseur),
                        '</span>'
                    ) SEPARATOR ' <span class=\"text-secondary mx-1\">|</span> '
                ) as detail_restant_text
            ")

        )
        ->groupBy('ventes.id', 'ventes.code_facture', 'ventes.updated_at', 'clients.nom_u')
        ->orderBy('ventes.updated_at', 'desc')
        ->take(20)
        ->get();




    // 3. Récupérer la liste des brasseries pour le formulaire de retour libre
    $fournisseurs = DB::table('fournisseurs')->select('id_fournisseur', 'nom_fournisseur')->get();

    return view('layout.personnel.livraison', compact('commandesAPreparer', 'historiqueRetours', 'fournisseurs'));
}

/**
 * ⚡ BOUCLE DYNAMIQUE : Traite le changement de statut 'livrer' et enregistre les lignes de retours
 */
public function validerLivraisonEtRetourCasiers(Request $request, $id_vente)
{
    // Le formulaire envoie un tableau associatif 'rendus' où la clé est l'ID du fournisseur (ex: rendus[1] = 5)
    $request->validate([
        'rendus' => 'required|array',
        'rendus.*' => 'required|numeric|min:0'
    ]);

    $vente = Vente::with('detailVentes.produit')->find($id_vente);
    if (!$vente || $vente->statut !== 'payer') {
        return response()->json(['success' => false, 'message' => 'Cette commande n\'est pas en attente de sortie.'], 404);
    }

    DB::beginTransaction();
    try {
        $userStaff = auth()->user();
        $inputsRendus = $request->input('rendus', []); // Tableau [id_fournisseur => quantite]
        $totalCasiersRendusCeJour = 0;

        // 🔄 LA BOUCLE INTELLIGENTE : On parcourt chaque saisie de retour faite par la secrétaire
        foreach ($inputsRendus as $idFournisseur => $quantiteRendue) {
            $quantiteRendue = (float)$quantiteRendue;

            // Si pour ce fournisseur le client a ramené au moins 1 casier, on crée l'enregistrement dédié
            if ($quantiteRendue > 0) {
                // 1. Insertion d'une ligne d'historique normalisée propre
                DB::table('retours_casiers')->insert([
                    'client_id'       => $vente->client_id,
                    'employe_id'      => $userStaff->id_u,
                    'depot_id'        => $userStaff->depot_id,
                    'fournisseur_id'  => $idFournisseur,
                    'vente_id'        => $vente->id, // Lié directement à cette vente
                    'quantite_rendue' => $quantiteRendue,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);

                // 2. Augmentation en temps réel du stock physique de vides de cette brasserie au dépôt
                DB::table('fournisseurs')
                    ->where('id_fournisseur', $idFournisseur)
                    ->increment('stock_vides_f', $quantiteRendue);

                $totalCasiersRendusCeJour += $quantiteRendue;
            }
        }

        // 3. Clôture logistique définitive de la facture de vente
        $vente->statut = 'livrer';
        $vente->updated_at = now();
        $vente->save();

        // 4. Émettre un signalement dans la cloche du smartphone du client
        DB::table('notifications_systeme')->insert([
            'user_id'    => $vente->client_id,
            'depot_id'   => $userStaff->depot_id,
            'titre_n'    => "🚚 Commande Récupérée",
            'message_n'  => "Le bon " . $vente->code_facture . " a été sorti du quai. Total casiers vides restitués : " . $totalCasiersRendusCeJour . " cs. Fiche logistique à jour.",
            'icone_n'    => "bi-truck",
            'couleur_n'  => "text-info",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => "Sortie de quai validée ! Statut mis à jour et " . $totalCasiersRendusCeJour . " ligne(s) d'emballages enregistrée(s)."
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Erreur de boucle logistique : ' . $e->getMessage()], 500);
    }
}

public function enregistrerRetourLibreCasiers(Request $request)
{
    $request->validate([
        'client_id' => 'required|exists:users,id_u',
        'rendus' => 'required|array',
        'rendus.*' => 'required|integer|min:0'
    ]);

    $clientId = $request->input('client_id');
    $inputsRendus = $request->input('rendus', []);
    $userStaff = auth()->user();
    $totalCasiersRendus = 0;

    DB::beginTransaction();
    try {
        // Boucle d'enregistrement dynamique en lignes formulée suivant ta vision
        foreach ($inputsRendus as $idFournisseur => $quantiteRendue) {
            $quantiteRendue = (int)$quantiteRendue;

            if ($quantiteRendue > 0) {
                // 1. Insertion de la ligne au grand livre avec vente_id = NULL
                DB::table('retours_casiers')->insert([
                    'client_id'       => $clientId,
                    'employe_id'      => $userStaff->id_u,
                    'depot_id'        => $userStaff->depot_id,
                    'fournisseur_id'  => $idFournisseur,
                    'vente_id'        => null, // 💡 Retour libre pur sans achat lié
                    'quantite_rendue' => $quantiteRendue,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);

                // 2. Incrémentation immédiate du stock de vides de cette brasserie au dépôt
                DB::table('fournisseurs')
                    ->where('id_fournisseur', $idFournisseur)
                    ->increment('stock_vides_f', $quantiteRendue);

                $totalCasiersRendus += $quantiteRendue;
            }
        }

        if ($totalCasiersRendus > 0) {
            // 3. Émission de l'accusé de réception sur le smartphone du client
            DB::table('notifications_systeme')->insert([
                'user_id'    => $clientId,
                'depot_id'   => $userStaff->depot_id,
                'titre_n'    => "🔄 Restitution d'emballages",
                'message_n'  => "Le dépôt a validé la réception de " . $totalCasiersRendus . " casier(s) vide(s) déposé(s) au comptoir. Vos fiches de reliquats ont été recalculées.",
                'icone_n'    => "bi-arrow-left-right",
                'couleur_n'  => "text-success",
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        DB::commit();
        Session::flash('success', "Restitution de " . $totalCasiersRendus . " emballage(s) enregistrée au grand livre logistique !");
        return response()->json([
            'success' => true,
            'message' => "Restitution de " . $totalCasiersRendus . " emballage(s) enregistrée au grand livre logistique !"
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Erreur lors du déchargement : ' . $e->getMessage()], 500);
    }
}
}
