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
            'id_fournisseur' => 'required|integer',
            'depot_id' => 'required|integer',
            'stocks' => 'required|array'
        ]);

        $depotId = $request->input('depot_id');
        $idFournisseur = $request->input('id_fournisseur');
        $stocksSaisis = $request->input('stocks');

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
                $produit = Produit::find($id_produit);

                if ($produit) {
                    // A. Augmentation du stock physique réel
                    $produit->quantite_p += $quantite;
                    $produit->save();

                    // B. Écriture de la ligne de traçabilité dans l'historique détaillé
                    DB::table('detail_approvisionnements')->insert([
                        'approvisionnement_id' => $id_app,
                        'produit_id' => $id_produit,
                        'quantite_ajoutee' => $quantite,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
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

    public function ajouterArticle(Request $request)
    {
        $produitId    = $request->input('produit_id');
        $quantite     = (float)$request->input('quantite');
        $prixUnitaire = (float)$request->input('prix_unitaire');
        $totalLigne   = $quantite * $prixUnitaire;
        $employeId    = Auth::user()->id_u; // 💡 On capture le caissier connecté

        $produit = Produit::find($produitId);
        if (!$produit || $produit->quantite_p < $quantite) {
            return response()->json(['success' => false, 'message' => 'Stock insuffisant.'], 422);
        }

        DB::beginTransaction();
        try {
            
            $venteId = $request->input('vente_id');
            if (!empty($venteId)) {
                // 💡 SÉCURITÉ COMPTABLE : On cherche si la boisson existe déjà dans ce brouillon
                $articleExiste = DB::table('detail_ventes')
                    ->where('vente_id', $venteId)
                    ->where('produit_id', $produitId)
                    ->exists();

                if ($articleExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cette boisson est déjà présente dans le panier. Veuillez supprimer la ligne existante pour modifier sa quantité.'
                    ], 422); // Code 422 : Erreur de validation
                }
            }
            // 💡 Si c'est le 1er produit, on crée l'en-tête de facture reliée à cet employé
            if (empty($venteId)) {
                $codeFacture = 'FACT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                $clientId = $request->input('client_id') === 'walkin' ? null : $request->input('client_id');

                $venteId = DB::table('ventes')->insertGetId([
                    'client_id' => $clientId,
                    'employe_id' => $employeId, // 💡 Injection de la colonne
                    'code_facture' => $codeFacture,
                    'montant_total' => 0,
                    'mode_paiement' => 'Espece',
                    'statut' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Insertion du produit dans le sous-détail
            $detailId = DB::table('detail_ventes')->insertGetId([
                'vente_id' => $venteId,
                'produit_id' => $produitId,
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'total_ligne' => $totalLigne,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Recalcul de la facture générale
            $totalFacture = DB::table('detail_ventes')->where('vente_id', $venteId)->sum('total_ligne');
            DB::table('ventes')->where('id', $venteId)->update(['montant_total' => $totalFacture]);

            DB::commit();

            return response()->json([
                'success' => true,
                'vente_id' => $venteId,
                'detail_id' => $detailId,
                'nom_produit' => strtoupper($produit->nom_p),
                'total_ligne' => $totalLigne
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprime un article du panier
     */
    public function supprimerArticle($id)
    {
        DB::beginTransaction();
        try {
            $detail = DB::table('detail_ventes')->where('id', $id)->first();
            if ($detail) {
                $venteId = $detail->vente_id;
                DB::table('detail_ventes')->where('id', $id)->delete();

                $reste = DB::table('detail_ventes')->where('vente_id', $venteId)->count();
                if ($reste === 0) {
                    DB::table('ventes')->where('id', $venteId)->delete(); // Supprime l'en-tête si vide
                } else {
                    $total = DB::table('detail_ventes')->where('vente_id', $venteId)->sum('total_ligne');
                    DB::table('ventes')->where('id', $venteId)->update(['montant_total' => $total]);
                }
            }
            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) { DB::rollBack(); return response()->json(['success' => false], 500); }
    }

    /**
     * Encaissement final, validation comptable et ajustement des stocks physiques
     */
    public function cloturerVente(Request $request)
    {
        $request->validate([
            'vente_id'       => 'required|integer',
            'mode_paiement'  => 'required',
            'statut'         => 'required|in:payer,attente',
            'montant_verse'  => 'required|numeric|min:0', 
            'rendre_monnaie' => 'nullable|integer|in:0,1'   
        ]);

        $venteId               = $request->input('vente_id');
        $statutFacture         = $request->input('statut');
        $cashPhysique          = (float)$request->input('montant_verse');
        $rendreMonnaiePhysique = $request->input('rendre_monnaie') == 1;

        $vente = Vente::find($venteId);
        if (!$vente) {
            return response()->json(['success' => false, 'message' => 'Facture introuvable.'], 404);
        }

        DB::beginTransaction();
        try {
            // STOCK DES BOISSONS
            foreach ($grid = $vente->detailVentes as $detail) {
                $qte = (float)$detail->quantite;
                
                // Validation PHP des décimales autorisées
                $partieDecimale = round($qte - floor($qte), 2);
                if (!in_array($partieDecimale, [0.00, 0.25, 0.50, 0.75])) {
                    return response()->json(['success' => false, 'message' => "Fraction de casier interdite détectée."], 422);
                }

                $produit = $detail->produit;
                if ($produit->quantite_p < $detail->quantite) {
                    return response()->json(['success' => false, 'message' => "Stock insuffisant pour {$produit->nom_p}."], 422);
                }
                $produit->quantite_p -= $detail->quantite;
                $produit->save();
                // 💡 CAPTEUR INDUSTRIEL : Vérification automatique du seuil d'alerte après déduction
                $produitApresVente = DB::table('produits')->where('id_produit', $detail->produit_id)->first();

                if ($produitApresVente && $produitApresVente->quantite_p <= $produitApresVente->seuil) {
                    
                    // On vérifie si une alerte non lue existe déjà pour éviter de dupliquer la même notification
                    $alerteExiste = DB::table('alertes_stocks')
                        ->where('produit_id', $produitApresVente->id_produit)
                        ->where('depot_id', $vente->depot_id ?? auth()->user()->depot_id)
                        ->where('statut_lecture', 'non_lu')
                        ->exists();

                    if (!$alerteExiste) {
                        // Insertion chirurgicale de la notification d'alerte critique
                        DB::table('alertes_stocks')->insert([
                            'produit_id'        => $produitApresVente->id_produit,
                            'depot_id'          => $vente->depot_id ?? auth()->user()->depot_id,
                            'quantite_restante' => $produitApresVente->quantite_p,
                            'seuil_limite'      => $produitApresVente->seuil,
                            'created_at'        => now(),
                            'updated_at'        => now()
                        ]);
                    }
                }
            }

            // BLOC COMMANDE EN ATTENTE
            if ($statutFacture === 'attente') {
                $montantFacture = (float)$vente->montant_total;
                $virtuelPreleveEpargne = 0;

                if (!is_null($vente->client_id)) {
                    $client = User::find($vente->client_id);
                    if ($cashPhysique < $montantFacture && $client->solde_u > 0) {
                        $manquant = $montantFacture - $cashPhysique;
                        $virtuelPreleveEpargne = min($manquant, $client->solde_u); // 
                    }
                }

                $vente->montant_verse = $cashPhysique + $virtuelPreleveEpargne; 
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
                    $client = User::find($vente->client_id);

                    if ($client->solde_u < 0 && $cashPhysique < ($montantFacture + abs($client->solde_u))) {
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
        Storage::put($cheminDossier . $nomFichier, $fluxPdfContenu);

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

    // 🔒 SÉCURITÉ : On cible uniquement les bons déjà présents au comptoir ('attente')
    $vente = Vente::with('detailVentes.produit')->find($id_facture);
    if (!$vente || $vente->statut !== 'attente') {
        return response()->json(['success' => false, 'message' => 'Cette facture est introuvable ou a déjà été clôturée.'], 404);
    }

    DB::beginTransaction();
    try {

        // ====================================================================
        // ❌ CAS A : L'ANULATION FINALE AU COMPTOIR (RETOUR EN RAYON)
        // ====================================================================
        if ($action === 'refuser') {
            // Remise physique des casiers annulés en stock rayon
            foreach ($vente->detailVentes as $detail) {
                $produit = $detail->produit;
                if ($produit) {
                    $produit->quantite_p += $detail->quantite;
                    $produit->save();
                }
            }

            // Gestion de la restitution comptable de l'avance (Inaltérée)
            $avanceLaissee = (float)$vente->montant_verse; 
            if ($avanceLaissee > 0 && !is_null($vente->client_id)) {
                $client = User::find($vente->client_id);
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
            $montantFacture = (float)$vente->montant_total;
            $avanceAnterieure = (float)$vente->montant_verse;
            $resteApyerFacture = $montantFacture - $avanceAnterieure;
            if (!is_null($vente->client_id)) {
                $client = User::find($vente->client_id);
                

                // 🧮 CALCUL PRÉVISIONNEL DU SOLDE FINAL APPRÈS ENCAISSEMENT
                // Formule : Solde actuel + Nouveau cash apporté - Reste à payer de la facture
                $resteApyerFacture = $montantFacture - $avanceAnterieure;
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

                $vente->statut = 'livrer'; // Le client passager n'a pas de compte, donc on ne peut pas créer de dette
                $vente->save();

                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => "Facture soldée avec succès. Monnaie à rendre : " . number_format($monnaieArendre, 0, ',', ' ') . " F."
                ], 200);
            }

            


            // Clôture définitive pour livraison de la boisson
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
