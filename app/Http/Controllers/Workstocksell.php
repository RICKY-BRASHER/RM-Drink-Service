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
use Illuminate\Support\Facades\Storage;
use App\Services\PromotionService;
use Illuminate\Validation\ValidationException;

class Workstocksell extends Controller
{
    /**
     * Extrait toutes les boissons d'un fournisseur regroupées par Dépôt pour le modal de masse
     */
    public function getProduitsTousDepots($id_fournisseur)
    {
        $produits = Produit::with('depot')
            ->where('id_fournisseur', $id_fournisseur)
            ->orderBy('nom_p', 'asc')
            ->get()
            ->groupBy(function($produit) {
                // Regroupe par le nom réel du dépôt associé (ex: Logpom, Akwa, Bonabéri...)
                return $produit->depot?->nom_depot ?? 'Dépôt Central'; // Si le dépôt est null, on le regroupe sous "Dépôt Central"
            });

        return response()->json(['data' => $produits], 200);// Retourne les produits regroupés par dépôt au format JSON
    }

    public function updateStockInline(Request $request, $id)
    {
        // 1. SÉCURITÉ COMPTABLE : Seul l'admin a le droit de modifier
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Action interdite : Seule la direction peut réajuster les écritures de stocks.'
            ], 403);
        }

        // 2. VALIDATION STRICTE DES ENTRÉES
        $request->validate([
            'quantite_p' => 'required|numeric|min:0',
            'seuil_alerte' => 'required|integer|min:1'
        ], [
            // Messages pour la quantité physique
            'quantite_p.required' => 'La quantité physique réelle est obligatoire.',
            'quantite_p.numeric' => 'La quantité doit être un nombre.',
            'quantite_p.min' => 'La quantité physique ne peut pas être négative.',

            // Messages pour le seuil d'alerte
            'seuil_alerte.required' => 'Le seuil minimal d\'alerte est obligatoire.',
            'seuil_alerte.integer' => 'Le seuil doit être un nombre entier.',
            'seuil_alerte.min' => 'Le seuil minimal d\'alerte doit être d\'au moins 1 bouteille.',
        ]);


        // 3. MISE À JOUR EN BDD
        $produit = Produit::find($id);
        if (!$produit) {
            return response()->json(['success' => false, 'message' => 'Boisson introuvable.'], 404);
        }

        $produit->quantite_p = $request->input('quantite_p');
        $produit->seuil = $request->input('seuil_alerte');
        $produit->save();
        session()->flash('success','Donnees mis a jour avec success');
        // 4. RETOUR DES DONNÉES SYNCHRONISÉES POUR LE JQUERY
        return response()->json([
            'success' => true,
            'redirect' => route('stock')
        ], 200);
    }

    /**
     * 1. CHARGE LES DÉPÔTS DISPONIBLES POUR UN FOURNISSEUR
     * 
     * Cette fonction regarde quels sont les dépôts qui possèdent au moins
     * un produit appartenant au fournisseur sélectionné.
     */
    public function getDepotsFournisseur($id_fournisseur)   
    {
        // On cherche les IDs des dépôts qui ont des produits de ce fournisseur
        $depotIds = Produit::where('id_fournisseur', $id_fournisseur)
            ->pluck('depot_id') // Extrait uniquement la colonne depot_id
            ->unique()          // Supprime les doublons (ex: si 20 produits sont à Logpom, on ne garde qu'une fois Logpom)
            ->toArray();

        // On récupère les vrais noms et informations de ces dépôts depuis la table depots
        $depots = Depot::whereIn('id', $depotIds)
            ->orderBy('nom_depot', 'asc')
            ->get();

        // On renvoie la liste au script jQuery au format JSON
        return response()->json([
            'depots' => $depots
        ], 200);
    }

    /**
     * 2. CHARGE LES PRODUITS DU FOURNISSEUR POUR UN DÉPÔT PRÉCIS
     * 
     * Cette fonction extrait la liste exacte des boissons filtrée par le fournisseur
     * ET par le dépôt sélectionné, pour que le formulaire affiche une feuille blanche propre.
     */
    public function getProduitsFiltres($id_fournisseur, $id_depot)
    {
        // On cherche les produits qui correspondent STRICTEMENT aux deux critères
        $produits = Produit::where('id_fournisseur', $id_fournisseur)
            ->where('depot_id', $id_depot)
            ->orderBy('nom_p', 'asc') // Classement de A à Z
            ->get();

        // On renvoie les boissons trouvées au script jQuery pour dessiner les inputs
        return response()->json([
            'produits' => $produits
        ], 200);
    }

    public function storeRestock(Request $request)
    {
        // 1. SÉCURITÉ COMPTABLE
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            return response()->json([
                'success' => false, 
                'message' => 'Accès refusé : Seule la direction peut valider les bons de livraison.'
            ], 403);
        }

        // 2. VALIDATION DES ENTRÉES
        $request->validate([
            'id_fournisseur' => 'required|integer|exists:fournisseurs,id_fournisseur',
            'depot_id' => 'required|integer|exists:depots,id',
            'stocks' => 'required|array|min:1',
            'stocks.*' => 'required|integer|min:0',
        ]);

        $depotId = $request->input('depot_id');
        $idFournisseur = $request->input('id_fournisseur');
        $stocksSaisis = $request->input('stocks');
        $produitIds = array_keys($stocksSaisis);
        $produits = Produit::query()
            ->where('depot_id', $depotId)
            ->where('id_fournisseur', $idFournisseur)
            ->whereIn('id_produit', $produitIds)
            ->get()
            ->keyBy('id_produit');

        if ($produits->count() !== count($produitIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Un ou plusieurs produits ne correspondent pas au fournisseur et au dépôt sélectionnés.',
            ], 422);
        }

        // 💡 ÉTAPE ÉLIMINATOIRE : On filtre et on calcule le total AVANT toute écriture SQL
        $totalBouteilles = 0;
        $lignesValides = [];

        foreach ($stocksSaisis as $id_produit => $quantite) {
            $quantite = (int)$quantite;
            if ($quantite > 0) {
                $lignesValides[$id_produit] = $quantite;
                $totalBouteilles += $quantite;
            }
        }

        // 🛑 SI LE TOTAL EST ÉGAL À 0, ON S'ARRÊTE ICI ! (Pas de ligne créée en BDD)
        if ($totalBouteilles === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Action annulée : Vous devez saisir au moins une quantité supérieure à 0 pour valider un approvisionnement.'
            ], 422); // Code 422 : Erreur de validation des données
        }

        // 3. SÉCURISATION TRANSACTIONNELLE
        DB::beginTransaction();

        try {
            // 💡 LE BON N'EST CRÉÉ QU'ICI, CAR ON EST SÛR QU'IL Y A DES PRODUITS
            $id_app = DB::table('approvisionnements')->insertGetId([
                'id_u_admin' => Auth::user()->id_u,
                'id_fournisseur' => $idFournisseur,
                'depot_id' => $depotId,
                'total_boissons' => $totalBouteilles, // Le total exact calculé à l'avance
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. ENREGISTREMENT DES DÉTAILS ET DES STOCKS PHYSICQUES
            foreach ($lignesValides as $id_produit => $quantite) {
                $produit = Produit::query()
                    ->where('depot_id', $depotId)
                    ->where('id_fournisseur', $idFournisseur)
                    ->lockForUpdate()
                    ->findOrFail($id_produit);
                $produit->quantite_p += $quantite;
                $produit->save();

                DB::table('detail_approvisionnements')->insert([
                    'approvisionnement_id' => $id_app,
                    'produit_id' => $id_produit,
                    'quantite_ajoutee' => $quantite,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            session()->flash('success', "Approvisionnement enregistré avec succès ! $totalBouteilles produit(s) ajouté(s) au dépôt.");

            return response()->json([
                'success' => true,
                'message' => "Approvisionnement enregistré avec succès ! $totalBouteilles produit(s) ajouté(s) au dépôt."
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Erreur de traitement comptable : ' . $e->getMessage()
            ], 500);
        }
    }
     public function effectuerRecharge(Request $request)
    {
        $request->validate([
            'client_id'      => 'required|integer',
            'montant_r'      => 'required|numeric|min:500',
            'moyen_recharge' => 'required|in:especes,momo,om'
        ]);

        $idClient = $request->input('client_id');
        $montant  = $request->input('montant_r');

        DB::beginTransaction();

        try {
            $client = User::find($idClient);
            if (!$client) {
                return response()->json(['success' => false, 'message' => 'Client introuvable.'], 404);
            } 

            $soldeAvant = $client->solde_u;
            $nouveauSolde = $soldeAvant + $montant;

            // 1. Génération d'une référence unique (Ex: TX-20260711-4FA2B)
            $reference = 'RX-' . date('md') . '-' . strtoupper(Str::random(5));

            DB::table('notifications_systeme')->insert([
                'user_id'    => $client->id_u, // 📱 Envoyé directement sur le smartphone du client concerné
                'depot_id'   => auth()->user()->depot_id, // Lié au dépôt émetteur
                'titre_n'    => " Recharge Epargne",
                'message_n'  => " compte  crédité de " . number_format($request->input('montant_r'), 0, ',', ' ') . " FCFA. Nouveau solde : " . number_format($client->solde_u + $request->input('montant_r'), 0, ',', ' ') . " F.",
                'icone_n'    => "bi-cash-coin",
                'couleur_n'  => "text-success", // Vert Fintech pour la trésorerie positive
                'created_at' => now(),
                'updated_at' => now()
            ]);
            // Action A : Mise à jour du solde
            $client->solde_u = $nouveauSolde;
            $client->save();

            // Action B : Écriture de la preuve de traçabilité complète
            DB::table('recharges')->insert([
                'reference_tx'   => $reference,
                'client_id'      => $idClient,
                'employe_id'     => Auth::user()->id_u,
                'montant_r'      => $montant,
                'solde_avant_r'  => $soldeAvant, // Preuve 1
                'solde_apres_r'  => $nouveauSolde, // Preuve 2
                'moyen_recharge' => $request->input('moyen_recharge'),
                'ip_adresse'     => $request->ip(), // Preuve 3 (Adresse IP de la machine)
                'created_at'     => now(),
                'updated_at'     => now()
            ]);
            // 🛰️ SIGNALEMENT DE TRÉSORERIE : Notification instantanée du dépôt
            


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Recharge validée sous la référence {$reference}."
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur de caisse.'], 500);
        }
    }

    public function ajouterArticle(Request $request, PromotionService $promotionService)
    {
        $data = $request->validate([
            'produit_id' => 'required|integer|exists:produits,id_produit',
            'quantite' => 'required|numeric|min:0.25',
            'client_id' => 'required|string',
            'vente_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $quantite = (float) $data['quantite'];
        $fraction = round($quantite - floor($quantite), 2);

        if (!in_array($fraction, [0.00, 0.25, 0.50, 0.75], true)) {
            throw ValidationException::withMessages([
                'quantite' => 'Seuls les casiers entiers, demi-casiers, quarts ou trois-quarts sont autorisés.',
            ]);
        }

        if ($data['client_id'] !== 'walkin') {
            $clientExists = User::query()
                ->where('id_u', $data['client_id'])
                ->where('role_u', 'client')
                ->where('depot_id', $user->depot_id)
                ->exists();

            if (!$clientExists) {
                throw ValidationException::withMessages([
                    'client_id' => 'Le client sélectionné n’appartient pas à ce dépôt.',
                ]);
            }
        }

        return DB::transaction(function () use ($data, $user, $quantite, $promotionService) {
            $product = Produit::query()
                ->where('depot_id', $user->depot_id)
                ->lockForUpdate()
                ->find($data['produit_id']);

            if (!$product) {
                throw ValidationException::withMessages([
                    'produit_id' => 'Cette référence ne fait pas partie du dépôt affecté.',
                ]);
            }

            $sale = null;
            if (!empty($data['vente_id'])) {
                $sale = Vente::query()
                    ->where('employe_id', $user->id_u)
                    ->where('statut', 'draft')
                    ->lockForUpdate()
                    ->find($data['vente_id']);

                if (!$sale) {
                    abort(404, 'Brouillon de vente introuvable.');
                }

                if ((string) $sale->client_id !== ($data['client_id'] === 'walkin' ? '' : $data['client_id'])) {
                    abort(422, 'Le client de cette facture ne peut pas être modifié.');
                }

                $duplicate = DB::table('detail_ventes')
                    ->where('vente_id', $sale->id)
                    ->where('produit_id', $product->id_produit)
                    ->where('est_offert', false)
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'produit_id' => 'Cette référence est déjà dans le panier. Supprimez sa ligne pour modifier la quantité.',
                    ]);
                }
            } else {
                $clientId = $data['client_id'] === 'walkin' ? null : $data['client_id'];
                $saleId = DB::table('ventes')->insertGetId([
                    'client_id' => $clientId,
                    'employe_id' => $user->id_u,
                    'code_facture' => 'FACT-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'montant_total' => 0,
                    'mode_paiement' => 'Espece',
                    'statut' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sale = Vente::findOrFail($saleId);
            }

            $quantityAlreadyInSale = (float) DB::table('detail_ventes')
                ->where('vente_id', $sale->id)
                ->where('produit_id', $product->id_produit)
                ->where('est_offert', false)
                ->sum('quantite');

            if ($quantityAlreadyInSale + $quantite > (float) $product->quantite_p) {
                throw ValidationException::withMessages([
                    'quantite' => "Stock insuffisant pour {$product->nom_p}.",
                ]);
            }

            DB::table('detail_ventes')->insert([
                'vente_id' => $sale->id,
                'produit_id' => $product->id_produit,
                'quantite' => $quantite,
                'prix_unitaire' => $product->prix_p,
                'total_ligne' => $quantite * (float) $product->prix_p,
                'est_offert' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $promotionService->syncGiftLines($sale, (int) $user->depot_id);
            $sale->load('detailVentes.produit');

            return response()->json([
                'success' => true,
                'vente_id' => $sale->id,
                'lines' => $this->formatSaleLines($sale),
                'message' => 'Article ajouté au panier.',
            ]);
        });
    }

    public function addLine(Request $request, PromotionService $promotionService)
    {
        return $this->ajouterArticle($request, $promotionService);
    }

    public function getCurrent()
    {
        $sale = Vente::query()
            ->with('detailVentes.produit')
            ->where('employe_id', Auth::user()->id_u)
            ->where('statut', 'draft')
            ->latest()
            ->first();

        return response()->json([
            'vente_id' => $sale?->id,
            'montant_total' => $sale?->montant_total ?? 0,
            'lines' => $sale ? $this->formatSaleLines($sale) : [],
        ]);
    }

    /**
     * Supprime un article du panier
     */
    public function supprimerArticle($id, PromotionService $promotionService)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($id, $user, $promotionService) {
            $detail = DB::table('detail_ventes')->lockForUpdate()->where('id', $id)->first();

            if (!$detail || $detail->est_offert) {
                abort(404, 'Ligne de vente introuvable.');
            }

            $sale = Vente::query()
                ->where('id', $detail->vente_id)
                ->where('employe_id', $user->id_u)
                ->where('statut', 'draft')
                ->lockForUpdate()
                ->firstOrFail();

            DB::table('detail_ventes')->where('id', $id)->delete();

            $remainingLines = DB::table('detail_ventes')
                ->where('vente_id', $sale->id)
                ->where('est_offert', false)
                ->count();

            if ($remainingLines === 0) {
                DB::table('ventes')->where('id', $sale->id)->delete();

                return response()->json([
                    'success' => true,
                    'vente_id' => null,
                    'lines' => [],
                ]);
            }

            $promotionService->syncGiftLines($sale, (int) $user->depot_id, false);
            $sale->load('detailVentes.produit');

            return response()->json([
                'success' => true,
                'vente_id' => $sale->id,
                'lines' => $this->formatSaleLines($sale),
            ]);
        });
    }

    private function formatSaleLines(Vente $sale): array
    {
        return $sale->detailVentes->map(fn (DetailVente $detail) => [
            'id' => $detail->id,
            'produit_id' => $detail->produit_id,
            'nom_produit' => strtoupper($detail->produit->nom_p),
            'format' => $detail->produit->format_p,
            'quantite' => (float) $detail->quantite,
            'prix_unitaire' => (float) $detail->prix_unitaire,
            'total_ligne' => (float) $detail->total_ligne,
            'est_offert' => (bool) $detail->est_offert,
        ])->all();
    }

    /**
     * Encaissement final, validation comptable et ajustement des stocks physiques
     */
    public function cloturerVente(Request $request, PromotionService $promotionService)
    {
        $request->validate([
            'vente_id'       => 'required|integer',
            'mode_paiement'  => 'required|in:Espece,OM-MOMO,Epargne',
            'statut'         => 'required|in:payer,attente',
            'montant_verse'  => 'required|numeric|min:0', 
            'rendre_monnaie' => 'nullable|integer|in:0,1'   
        ]);

        $venteId               = $request->input('vente_id');
        $statutFacture         = $request->input('statut');
        $cashPhysique          = (float)$request->input('montant_verse');
        $rendreMonnaiePhysique = $request->input('rendre_monnaie') == 1;

        DB::beginTransaction();
        try {
            $vente = Vente::query()
                ->where('employe_id', Auth::user()->id_u)
                ->where('statut', 'draft')
                ->lockForUpdate()
                ->find($venteId);

            if (!$vente) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Facture introuvable ou déjà clôturée.'], 404);
            }

            if ($request->input('mode_paiement') === 'Epargne' && is_null($vente->client_id)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Le paiement par épargne exige un client identifié.'], 422);
            }

            $promotionService->syncGiftLines($vente, (int) Auth::user()->depot_id);
            $vente->load('detailVentes.produit');

            $stockLines = DB::table('detail_ventes')
                ->where('vente_id', $vente->id)
                ->select('produit_id', DB::raw('SUM(quantite) as quantite'))
                ->groupBy('produit_id')
                ->get();

            if ($stockLines->isEmpty()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Le panier est vide.'], 422);
            }

            foreach ($stockLines as $stockLine) {
                $qte = (float) $stockLine->quantite;
                $partieDecimale = round($qte - floor($qte), 2);
                if (!in_array($partieDecimale, [0.00, 0.25, 0.50, 0.75])) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Fraction de casier interdite détectée."], 422);
                }

                $produit = Produit::query()
                    ->where('depot_id', Auth::user()->depot_id)
                    ->lockForUpdate()
                    ->find($stockLine->produit_id);

                if (!$produit || $produit->quantite_p < $qte) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuffisant ou référence hors du dépôt pour finaliser la facture.',
                    ], 422);
                }

                $produit->quantite_p -= $qte;
                $produit->save();
                $produitApresVente = DB::table('produits')
                    ->where('id_produit', $stockLine->produit_id)
                    ->first();

                if ($produitApresVente && $produitApresVente->quantite_p <= $produitApresVente->seuil) {
                    $alerteExiste = DB::table('alertes_stocks')
                        ->where('produit_id', $produitApresVente->id_produit)
                        ->where('depot_id', Auth::user()->depot_id)
                        ->where('statut_lecture', 'non_lu')
                        ->exists();

                    if (!$alerteExiste) {
                        DB::table('alertes_stocks')->insert([
                            'produit_id'        => $produitApresVente->id_produit,
                            'depot_id'          => Auth::user()->depot_id,
                            'quantite_restante' => $produitApresVente->quantite_p,
                            'seuil_limite'      => $produitApresVente->seuil,
                            'created_at'        => now(),
                            'updated_at'        => now()
                        ]);
                    }
                }
            }

            // Une facture en attente conserve uniquement l’acompte réellement encaissé.
            if ($statutFacture === 'attente') {
                $vente->montant_verse = min($cashPhysique, (float) $vente->montant_total);
                $vente->mode_paiement = $request->input('mode_paiement');
                $vente->statut         = 'attente';
                $vente->save();
            } 
            // BLOC FACTURE PAYÉE IMMÉDIATEMENT
            else {
                $montantFacture = (float)$vente->montant_total;
                $montantPreleveEpargne = 0;
                $cashNetConserve = $cashPhysique;

                if (!is_null($vente->client_id)) {
                    $client = User::query()->lockForUpdate()->findOrFail($vente->client_id);

                    if ($client->solde_u < 0 && $cashPhysique < ($montantFacture + abs($client->solde_u))) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Le client doit apurer sa dette active.'], 422);
                    }

                    if ($cashPhysique < $montantFacture && $client->solde_u > 0) {
                        $manquant = $montantFacture - $cashPhysique;
                        $montantPreleveEpargne = min($manquant, $client->solde_u);
                    }

                    if ($rendreMonnaiePhysique) {
                        $nouveauSolde = ($client->solde_u < 0) ? 0.00 : $client->solde_u;
                        $cashNetConserve = $montantFacture + ($client->solde_u < 0 ? abs($client->solde_u) : 0);
                    } else {
                        $nouveauSolde = $client->solde_u + $cashPhysique - $montantFacture;
                    }

                    $epargneDisponible = $client->solde_u > 0 ? $client->solde_u : 0;
                    $detteActive       = $client->solde_u < 0 ? abs($client->solde_u) : 0;
                    $typeMouvement = ($cashPhysique >= ($montantFacture + $detteActive - $epargneDisponible)) ? 'achat_direct' : 'creation_dette';

                    DB::table('recharges')->insert([
                        'reference_tx'   => 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                        'type_mouvement' => $typeMouvement,
                        'client_id'      => $client->id_u,
                        'vente_id'       => $vente->id,
                        'employe_id'     => Auth::user()->id_u,
                        'montant_r'      => $cashNetConserve,
                        'solde_avant_r'  => $client->solde_u,
                        'solde_apres_r'  => $nouveauSolde,
                        'moyen_recharge' => $request->input('mode_paiement') === 'OM-MOMO' ? 'momo' : 'especes',
                        'ip_adresse'     => $request->ip(),
                        'created_at'     => now(),
                        'updated_at'     => now()
                    ]);

                    $client->solde_u = $nouveauSolde;
                    $client->save();
                }

                if ($rendreMonnaiePhysique && ($cashPhysique > $montantFacture)) {
                    $vente->montant_verse = $montantFacture;
                } else {
                    $vente->montant_verse = $cashPhysique + $montantPreleveEpargne;
                }

                $vente->mode_paiement = $request->input('mode_paiement');
                $vente->statut  = 'payer';
                $vente->save();
            }
            


            DB::commit();

            // 💡 RETOUR LÉGER ET FIABLE : On transmet uniquement l'ID de la vente validée
            return response()->json([
                'success' => true,
                'message' => "Transaction enregistrée avec succès.",
                'vente_id' => $vente->id 
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erreur critique : ' . $e->getMessage()], 500);
        }
    }


    public function telechargerFacture(Request $request, $id)
    {
        // Récupération du format demandé dans l'URL (par défaut 'ticket' si vide)
        $formatPapier = $request->query('format', 'ticket');

        $vente = Vente::with(['detailVentes.produit', 'client', 'employe'])->findOrFail($id);
        $nomDepot = DB::table('depots')->where('id', $vente->employe->depot_id ?? null)->value('nom_depot') ?? 'Dépôt Central';
        $logFinancier = DB::table('recharges')->where('vente_id', $id)->first();

        $data = [
            'vente' => $vente,
            'nomDepot' => strtoupper($nomDepot),
            'logFinancier' => $logFinancier,
            'formatPapier' => $formatPapier
        ]; 

        // Compilation DomPDF à partir du fichier Blade
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('layout.export.facture', $data);
        
        // Configuration de la taille du papier selon le format
        if ($formatPapier === 'a4') {
            $pdf->setPaper('a4', 'portrait');
        } elseif ($formatPapier === 'a5') {
            $pdf->setPaper('a5', 'portrait');
        } else {
            $pdf->setPaper([0, 0, 226, 600], 'portrait'); 
        }

        // ====================================================================
        // 💾 SAUVEGARDE AUTOMATIQUE SUR LE SERVEUR (POUR RÉCUPÉRATION ULTÉRIEURE)
        // ====================================================================
        
        // 1. Définition du nom unique du fichier et du dossier cible
        $nomFichier = 'facture_' . $vente->code_facture . '.pdf';
        $cheminDossier = 'public/factures/'; // Pointe vers storage/app/public/factures/ 
        
        // 2. Extraction du flux binaire brut généré par DomPDF
        $fluxPdfContenu = $pdf->output();
        
        // 3. Écriture physique sécurisée du fichier dans le stockage local
        Storage::disk('public')->put('factures/' . $nomFichier, $fluxPdfContenu);


        // ====================================================================

        // Envoi du flux d'affichage d'origine au navigateur du caissier
        return $pdf->stream($nomFichier);
    }
    public function annulerBrouillon($id)
    {
        DB::beginTransaction();
        try {
            // Recherche de la vente en statut draft par sécurité
            $vente = DB::table('ventes')->where('id', $id)->where('statut', 'draft')->first();

            if ($vente) {
                // La suppression de la vente supprime en cascade ses detail_ventes associés
                DB::table('ventes')->where('id', $id)->delete();
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'La facture en cours a été annulée avec succès.'
                ], 200);
            }

            return response()->json(['success' => false, 'message' => 'Brouillon introuvable.'], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

/*public function receptionnerCommandeEnLigne(Request $request, $id_facture)
{
    $request->validate(['action' => 'required|in:valider,refuser']);
    $action = $request->input('action');

    // Sécurité : On cible uniquement les bons officiellement transmis ('envoyer')
    $vente = Vente::with(['detailVentes.produit', 'client'])->find($id_facture);
    if (!$vente || $vente->statut !== 'envoyer') {
        return response()->json(['success' => false, 'message' => 'Cette commande mobile est introuvable ou déjà interceptée.'], 404);
    }

    DB::beginTransaction();
    try {
        // --- CAS ANNULATION DE COMMANDE MOBILE ---
        if ($action === 'refuser') {
            $vente->statut = 'annuler';
            $vente->employe_id = Auth::user()->id_u; // On marque l'employé qui a annulé
            $vente->updated_at = now();
            $vente->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Commande en ligne annulée et classée.'], 200);
        }

        // --- CAS VALIDATION / RECEPTION COMMANDE MOBILE ---
        if ($action === 'valider') {
            
            // ====================================================================
            // 🚨 LE VERROU DU CRÉDIT CLIENT : Vérification de la dette antérieure
            // ====================================================================
            if (!is_null($vente->client_id)) {
                // On récupère l'état réel et actuel du compte du client
                $clientCompte = DB::table('users')->where('id_u', $vente->client_id)->first();
                
                if ($clientCompte && $clientCompte->solde_u < -5000) {
                    $detteActuelle = abs($clientCompte->solde_u);
                    return response()->json([
                        'success' => false,
                        'message' => "🔒 Réception refusée ! Le client " . strtoupper($clientCompte->nom_u) . " possède déjà une ardoise financière de " . number_format($detteActuelle, 0, ',', ' ') . " FCFA au dépôt (Plafond de tolérance : 5 000 F). Veuillez rejeter son bon ou lui demander d'apurer sa dette au guichet avant de réserver ses casiers."
                    ], 422);
                } else {
                    $client = User::find($vente->client_id);
                    if ($client->solde_u >= $vente->montant_total) {
                        $client->solde_u -= $vente->montant_total;
                        $client->save();
                        $vente->statut = 'payer';
                    } else {
                        $vente->statut = 'attente';
                    }
                }
            }

            // --------------------------------------------------------------------
            // Si le client est solvable ou sous le seuil, la boucle des stocks démarre :
            // --------------------------------------------------------------------
            foreach ($vente->detailVentes as $detail) {
                $produit = DB::table('produits')->where('id_produit', $detail->produit_id)->first();
                
                if (!$produit || $produit->quantite_p < $detail->quantite) {
                    return response()->json([
                        'success' => false, 
                        'message' => "Rupture ! Le stock de " . ($produit->nom_p ?? 'Article') . " est insuffisant au dépôt."
                    ], 422);
                }

                // 📦 Déduction physique réelle du stock du dépôt dans la table produits
                DB::table('produits')->where('id_produit', $detail->produit_id)->decrement('quantite_p', $detail->quantite);
                
                // 💡 CAPTEUR INDUSTRIEL : Vérification et écriture dans la double-cloche (Stocks + Messages)
                $produitApresVente = DB::table('produits')->where('id_produit', $detail->produit_id)->first();
                if ($produitApresVente && $produitApresVente->quantite_p <= $produitApresVente->seuil) {
                    
                    $alerteExiste = DB::table('alertes_stocks')
                        ->where('produit_id', $produitApresVente->id_produit)
                        ->where('depot_id', $vente->depot_id ?? auth()->user()->depot_id)
                        ->where('statut_lecture', 'non_lu')
                        ->exists();

                    if (!$alerteExiste) {
                        $depotIdActuel = $vente->depot_id ?? auth()->user()->depot_id;

                        // 1. Enregistrement dans le registre technique
                        DB::table('alertes_stocks')->insert([
                            'produit_id'        => $produitApresVente->id_produit,
                            'depot_id'          => $depotIdActuel,
                            'quantite_restante' => $produitApresVente->quantite_p,
                            'seuil_limite'      => $produitApresVente->seuil,
                            'created_at'        => now(),
                            'updated_at'        => now()
                        ]);

                        // 2. Message d'alerte pour allumer la cloche de la secrétaire/admin
                        DB::table('notifications_systeme')->insert([
                            'depot_id'   => $depotIdActuel,
                            'titre_n'    => "🚨 Alerte Seuil Critique",
                            'message_n'  => "Le stock de " . strtoupper($produitApresVente->nom_p) . " est tombé sous la limite de sécurité au dépôt.",
                            'icone_n'    => "bi-exclamation-triangle-fill",
                            'couleur_n'  => "text-danger",
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            $userStaff = Auth::user();
            $vente->employe_id = $userStaff->id_u; // On marque l'employé qui a validé la réception
            $vente->save();

            // 🔄 Re-charger proprement la vente avec l'employé rattaché pour la structure PDF
            $venteFacturee = Vente::with(['detailVentes.produit', 'client', 'employe'])->find($vente->id);

            // ====================================================================
            // 💾 1. GÉNÉRATION AUTOMATIQUE DU PDF COMPTABLE SUR LE SERVEUR
            // ====================================================================
            $nomDepot = DB::table('depots')->where('id', $userStaff->depot_id ?? null)->value('nom_depot') ?? 'Dépôt Central';
            $logFinancier = DB::table('recharges')->where('vente_id', $vente->id)->first();

            $pdfData = [
                'vente' => $venteFacturee,
                'nomDepot' => strtoupper($nomDepot),
                'logFinancier' => $logFinancier,
                'formatPaper' => 'ticket' // Format ticket par défaut pour archivage local
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('layout.export.facture', $pdfData);
            $pdf->setPaper([0, 0, 226, 600], 'portrait'); // Standard 80mm
            
            // Stockage local physique dans storage/app/public/factures/
            $nomFichierPdf = 'facture_' . $venteFacturee->code_facture . '.pdf';
            Storage::put('public/factures/' . $nomFichierPdf, $pdf->output());
            // ====================================================================

            // Ajustement dynamique de l'alerte cloche selon le statut financier
            $messageNotif = ($venteFacturee->statut === 'payer')
                ? "Votre bon " . $venteFacturee->code_facture . " a été soldé par votre compte épargne. La facture PDF est archivée. Vos casiers sont prêts à la livraison."
                : "Votre bon " . $venteFacturee->code_facture . " a été intercepté et validé au dépôt. Vous pouvez passer au guichet pour le règlement.";

            // Envoi de l'accusé de réception sur l'application mobile du client
            DB::table('notifications_systeme')->insert([
                'user_id'    => $venteFacturee->client_id,
                'titre_n'    => ($venteFacturee->statut === 'payer') ? "✅ Commande Soldée" : "✅ Commande Réceptionnée",
                'message_n'  => $messageNotif,
                'icone_n'    => "bi-check-circle-fill",
                'couleur_n'  => "text-success",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            // Message AJAX adapté pour l'affichage de la secrétaire
            $messageJson = ($venteFacturee->statut === 'payer')
                ? "La commande a été payée automatiquement via le solde client. Le stock a été déduit et la facture PDF a été générée avec succès."
                : "La commande en ligne a été réceptionnée. Les casiers sont déduits du stock. La facture PDF a été générée et attend le règlement au guichet.";

            return response()->json([
                'success' => true, 
                'message' => $messageJson
            ], 200);
        }

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
    }
}
*/

public function traiterCommandeEnAttente(Request $request, $id_facture)
{
    $request->validate([
        'action' => 'required|in:valider,refuser',
        'montant_verse' => 'nullable|numeric|min:0' // C'est le COMPLÉMENT d'argent physique reçu
    ]);

    $action = $request->input('action');
    $complementCash = (float)$request->input('montant_verse', 0);

    DB::beginTransaction();
    try {
        $depotId = (int) Auth::user()->depot_id;
        $vente = Vente::query()
            ->where('statut', 'attente')
            ->where(function ($query) use ($depotId) {
                $query->whereHas('client', fn ($client) => $client->where('depot_id', $depotId))
                    ->orWhereHas('employe', fn ($employee) => $employee->where('depot_id', $depotId));
            })
            ->lockForUpdate()
            ->find($id_facture);

        if (!$vente) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Cette facture est introuvable, hors dépôt ou déjà clôturée.'], 404);
        }

        $vente->load('detailVentes.produit');

        // ====================================================================
        // ❌ CAS A : L'ANULATION FINALE AU COMPTOIR (RETOUR EN RAYON)
        // ====================================================================
        if ($action === 'refuser') {
            $stockLines = $vente->detailVentes->groupBy('produit_id');
            foreach ($stockLines as $productId => $details) {
                $product = Produit::query()
                    ->where('depot_id', $depotId)
                    ->lockForUpdate()
                    ->findOrFail($productId);
                $product->quantite_p += $details->sum('quantite');
                $product->save();
            }

            // Gestion de la restitution comptable de l'avance (Inaltérée)
            $avanceLaissee = (float)$vente->montant_verse; 
            if ($avanceLaissee > 0 && !is_null($vente->client_id)) {
                $client = User::query()->lockForUpdate()->findOrFail($vente->client_id);
                $soldeAvant = $client->solde_u;
                $nouveauSolde = $soldeAvant + $avanceLaissee;

                DB::table('recharges')->insert([
                    'reference_tx'   => 'REMB-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'client_id'      => $client->id_u,
                    'vente_id'       => $vente->id,
                    'employe_id'     => Auth::user()->id_u,
                    'montant_r'      => $avanceLaissee,
                    'solde_avant_r'  => $soldeAvant,
                    'solde_apres_r'  => $nouveauSolde,
                    'moyen_recharge' => 'annulation_facture',
                    'ip_adresse'     => $request->ip(),
                    'created_at'     => now(),
                    'updated_at'     => now()
                ]);

                $client->solde_u = $nouveauSolde;
                $client->save();
            }

            $vente->statut = 'annuler';
            $vente->updated_at = now();
            $vente->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Facture comptoir annulée avec succès. Stocks restitués.'], 200);
        }

        // ====================================================================
        // ✅ CAS B : ENCAISSEMENT ET CLÔTURE FINALE
        // ====================================================================
        if ($action === 'valider') {
            $montantFacture = (float)$vente->montant_total;
            $totalCashPhysiqueRecu = (float)$complementCash;
            $avanceAnterieure = (float)$vente->montant_verse;
            $resteApyerFacture = max(0, $montantFacture - $avanceAnterieure);
            if (!is_null($vente->client_id)) {
                $client = User::query()->lockForUpdate()->findOrFail($vente->client_id);
                

                // 🧮 CALCUL PRÉVISIONNEL DU SOLDE FINAL APPRÈS ENCAISSEMENT
                // Formule : Solde actuel + Nouveau cash apporté - Reste à payer de la facture
                $resteApyerFacture = max(0, $montantFacture - $avanceAnterieure);
                $soldeFinalPrevisionnel = $client->solde_u + $totalCashPhysiqueRecu - $resteApyerFacture;

                // 🚨 ====================================================================
                // PROTOCOLE DE VÉRIFICATION DU CRÉDIT ET DES DETTES
                // ====================================================================
                
                // ÉTAPE A : Le client possède déjà une dette traînante au dépôt (Solde négatif au départ)
                if ($client->solde_u < 0) {
                    
                    // Condition 1 : Le règlement effectué couvre-t-il au moins l'intégralité du reste de la facture ?
                    $couvreLaFactureDuJour = ($totalCashPhysiqueRecu >= $resteApyerFacture);
                    
                    // Condition 2 : La dette finale cumulée reste-t-elle sous le plafond strict de 10 000 F ?
                    $detteFinaleSousLePlafond = ($soldeFinalPrevisionnel >= -10000);

                    // Si AUCUNE des deux conditions n'est respectée, le logiciel bloque la transaction
                    if (!$couvreLaFactureDuJour && !$detteFinaleSousLePlafond) {
                        $dettePrevisionnelleTotale = abs($soldeFinalPrevisionnel);
                        
                        // Calcul mathématique du montant minimal que le client doit donner pour redescendre à -10 000 F
                        $montantMinimumAverser = abs($client->solde_u) + $resteApyerFacture - 10000;
                        
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "🔒 Clôture refusée ! Ce client possède déjà une dette. Le cash reçu ne couvre pas la facture du jour (" . number_format($resteApyerFacture, 0, ',', ' ') . " F) et la dette finale grimperait à " . number_format($dettePrevisionnelleTotale, 0, ',', ' ') . " F (Plafond autorisé : 10 000 F). Veuillez réclamer au minimum : " . number_format($montantMinimumAverser, 0, ',', ' ') . " FCFA."
                        ], 422);
                    }
                }
                
                // ÉTAPE B : Si le client n'avait pas de dette préalable (solde >= 0), 
                // ou s'il a franchi l'une des deux barrières ci-dessus, le code continue normalement.

                $nouveauSolde = $soldeFinalPrevisionnel;
                $typeMouvement = ($nouveauSolde < 0) ? 'creation_dette' : 'achat_direct';

                // Insertion de la ligne historique de recharge dans le grand livre
                DB::table('recharges')->insert([
                    'reference_tx'   => 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                    'type_mouvement' => $typeMouvement, 
                    'client_id'      => $client->id_u,
                    'vente_id'       => $vente->id,
                    'employe_id'     => Auth::user()->id_u,
                    'montant_r'      => $totalCashPhysiqueRecu, 
                    'solde_avant_r'  => $client->solde_u,       
                    'solde_apres_r'  => $nouveauSolde,       
                    'moyen_recharge' => $vente->mode_paiement === 'OM-MOMO' ? 'momo' : 'especes',
                    'ip_adresse'     => $request->ip(),
                    'created_at'     => now(),
                    'updated_at'     => now()
                ]);

                // Mise à jour de la fiche solde du client
                $client->solde_u = $nouveauSolde;
                $client->save();

                // 💡 RECTIFICATION COMPTABLE : Calcul du montant versé réel sur la facture
                $verseCumule = $avanceAnterieure + $totalCashPhysiqueRecu;

                // Le montant versé sur la facture ne peut jamais dépasser le prix de la facture elle-même
                if ($verseCumule > $montantFacture) {
                    $vente->montant_verse = $montantFacture;
                } else {
                    $vente->montant_verse = $verseCumule;
                }
            } else {
                // ====================================================================
                // 👤 COMPTOIR : LOGIQUE DU CLIENT PASSAGER (SANS COMPTE BDD)
                // ====================================================================
                
                // 1. Verrou strict : L'argent donné doit couvrir au moins le reste à payer
                if ($totalCashPhysiqueRecu < $resteApyerFacture) {
                    $manquant = $resteApyerFacture - $totalCashPhysiqueRecu;
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "⛔ Opération refusée ! Un client passager n'a pas droit au crédit. Le cash reçu (" . number_format($totalCashPhysiqueRecu, 0, ',', ' ') . " F) est inférieur au reste à payer (" . number_format($resteApyerFacture, 0, ',', ' ') . " F). Veuillez réclamer le complément de : " . number_format($manquant, 0, ',', ' ') . " FCFA."
                    ], 422);
                }

                // 2. Gestion du surplus / monnaie à rendre en espèces
                $monnaieArendre = 0;
                if ($totalCashPhysiqueRecu > $resteApyerFacture) {
                    $monnaieArendre = $totalCashPhysiqueRecu - $resteApyerFacture;
                }

                // Pour un client passager, la facture passe à 100% payée
                $vente->montant_verse = $montantFacture;

                $vente->statut = 'payer';
                $vente->save();

                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => "Facture soldée et prête à être remise au client. Monnaie à rendre : " . number_format($monnaieArendre, 0, ',', ' ') . " F."
                ], 200);
            }

            $vente->statut = 'payer';
            $vente->save();

            // 📱 FLUX 1 : Émission immédiate du reçu numérique sur le smartphone du client
            DB::table('notifications_systeme')->insert([
                'user_id'        => $vente->client_id,
                'depot_id'       => auth()->user()->depot_id,
                'titre_n'        => "✅ Reçu de Facture Soldée",
                'message_n'      => "Le paiement du bon " . $vente->code_facture . " a été validé au guichet. Votre reçu PDF officiel est disponible.",
                'icone_n'        => "bi-shield-check-fill",
                'couleur_n'      => "text-success",
                'statut_lecture' => 'non_lu',
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            // 👑 FLUX 2 : Émission de la copie de contrôle pour la cloche centrale de l'Admin
            /*DB::table('notifications_systeme')->insert([
                'user_id'        => 1, // 💡 Mettre ici l'id_u de ton administrateur général en BDD
                'depot_id'       => auth()->user()->depot_id,
                'titre_n'        => "📈 Trésorerie : Commande Soldée",
                'message_n'      => "Dépôt " . session('depot_actif_nom', 'Logpom') . " : Facture " . $vente->code_facture . " clôturée. Encaissé : " . number_format($totalCashPhysiqueRecu, 0, ',', ' ') . " F.",
                'icone_n'        => "bi-cash-coin",
                'couleur_n'      => "text-primary",
                'statut_lecture' => 'non_lu',
                'created_at'     => now(),
                'updated_at'     => now()
            ]);*/

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => "Facture soldée avec succès. Dette finale stabilisée à : " . number_format(abs($nouveauSolde), 0, ',', ' ') . " F."
            ], 200);
        }

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Erreur de caisse : ' . $e->getMessage()], 500);
    }
}


/**
 * 🔓 METHODE DE BYPASS : Nettoie le panier fantôme d'un client par erreur
 */
public function forcerLiberationClient(Request $request)
{
    $request->validate(['client_id' => 'required|integer']);
    $clientId = $request->input('client_id');

    // 🔒 SÉCURITÉ ABSOLUE : On ne cible que les paniers STRICTEMENT au statut 'brouillon'
    $brouillonFantome = Vente::where('client_id', $clientId)
        ->where('statut', 'draft')
        ->first();

    if ($brouillonFantome) {
        DB::beginTransaction();
        try {
            DB::table('detail_ventes')->where('vente_id', $brouillonFantome->id)->delete();
            $brouillonFantome->delete();
            
            DB::commit();
            return response()->json(['success' => true, 'message' => "Le panier mobile a été nettoyé. Le client est débloqué !"]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Si le statut est 'envoyer', le serveur lève un bouclier et refuse l'effacement
    return response()->json([
        'success' => false, 
        'message' => "Interdit ! Ce client a déjà transmis officiellement sa commande. Veuillez la valider ou l'annuler depuis l'onglet des Commandes."
    ], 422);
} 

public function recupererDonneesModification($id)
{
    try {
        // Jointure explicite et sécurisée sur la table produits avec id_produit
        $lignes = DB::table('detail_ventes')
            ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
            ->where('detail_ventes.vente_id', $id)
            ->select(
                'detail_ventes.quantite', 
                'detail_ventes.total_ligne', 
                'produits.id_produit', 
                'produits.nom_p', 
                'produits.prix_p', 
                'produits.format_p', 
                'produits.quantite_p as stock_actuel'
            )
            ->get();

        $vente = DB::table('ventes')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'lignes' => $lignes,
            'total_global' => $vente ? $vente->montant_total : 0
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * 2. API : Renvoie la liste complète des boissons du dépôt pour les ajouts rapides
     */
public function listeProduitsDepot()
{
    try {
        $produits = DB::table('produits')
            ->where('depot_id', auth()->user()->depot_id)
            ->orderBy('nom_p', 'asc')
            ->get();

        return response()->json(['success' => true, 'produits' => $produits], 200);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * 3. ACTION : Écrase l'ancien detail_ventes et réinsère proprement avec id_produit
     */
/*public function sauvegarderAjustements(Request $request, $id)
{
    $request->validate(['lignes' => 'required|array']);
    $nouvellesLignes = $request->input('lignes');

    DB::beginTransaction();
    try {
        // Nettoyage de sécurité de l'ancien detail_ventes pour cette facture
        DB::table('detail_ventes')->where('vente_id', $id)->delete();

        $nouveauTotalGlobal = 0;

        // Ré-insertion chronologique des nouvelles lignes réajustées
        foreach ($nouvellesLignes as $ligne) {
            
            // Extraction sécurisée en Query Builder pur
            $produit = DB::table('produits')
                ->where('id_produit', intval($ligne['produit_id']))
                ->first();
            
            if (!$produit) {
                return response()->json(['success' => false, 'message' => "Le produit d'ID " . $ligne['produit_id'] . " est introuvable."], 422);
            }

            $totalLigne = (float)$ligne['quantite'] * (float)$produit->prix_p;
            $nouveauTotalGlobal += $totalLigne;

            // Insertion chirurgicale en BDD (Aucun risque d'erreur d'index)
            DB::table('detail_ventes')->insert([
                'vente_id'      => $id,
                'produit_id'    => $produit->id_produit, // Liaison exacte sur id_produit
                'quantite'      => (float)$ligne['quantite'],
                'prix_unitaire' => $produit->prix_p,
                'total_ligne'   => $totalLigne,
                'created_at'    => now(),
                'updated_at'    => now()
            ]);
        }

        // Fixation comptable du nouveau montant global sur le squelette de la vente
        DB::table('ventes')->where('id', $id)->update([
            'montant_total' => $nouveauTotalGlobal,
            'updated_at'    => now(),
            'statut' => 'attente'
        ]);
        // Alerte de réajustement logistique à envoyer sur le smartphone du client concerné
        $venteActuelle = DB::table('ventes')->where('id', $id)->first();
        DB::table('notifications_systeme')->insert([
            'user_id'    => $venteActuelle->client_id,
            'titre_n'    => "⚠️ Bon de commande réajusté et Validé",
            'message_n'  => "Votre réservation " . $venteActuelle->code_facture . " a été modifiée par le secrétariat en raison de l'état des stocks au dépôt.",
            'icone_n'    => "bi-exclamation-triangle-fill",
            'couleur_n'  => "text-warning",
            'created_at' => now()
        ]);


        DB::commit();
        return response()->json(['success' => true, 'nouveau_total' => $nouveauTotalGlobal], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Erreur de caisse : ' . $e->getMessage()], 500);
    }
}*/







}
