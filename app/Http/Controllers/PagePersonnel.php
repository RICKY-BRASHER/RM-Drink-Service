<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\Depot;
use App\Models\Fournisseur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage; 
class PagePersonnel extends Controller
{
     public function showpagedashboard()
    {
        $user = Auth::user();
        $depotId = $user->depot_id;

        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // ----------------------------------------------------------------
        // 💰 SECTION A : ACTIVITÉS DE VENTE JOURNALIÈRES (ZÉRO COLLISION)
        // ----------------------------------------------------------------
        
        // 1. Chiffre d'Affaires Journalier Encaissé
        $caJournalier = DB::table('recharges')
            ->join('users', 'recharges.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereDate('recharges.created_at', $today)
            ->whereIn('recharges.type_mouvement', ['achat_direct', 'creation_dette'])
            ->sum('recharges.montant_r');

        // 2. Nombre de Factures en Dette créées aujourd'hui
        $nbDettesJour = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereDate('ventes.created_at', $today)
            ->where('ventes.statut', 'attente')
            ->where('ventes.montant_total', '>', 0)
            ->count();

        // 3. Commandes Mobiles Reçues en Ligne à Préparer (Statut 'envoyer')
        $nbCommandesWeb = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->where('ventes.statut', 'envoyer')
            ->count();

        // 4. Volume global de Boissons Vendues aujourd'hui
        $volumeVenduesJour = DB::table('detail_ventes')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereDate('ventes.created_at', $today)
            ->whereIn('ventes.statut', ['attente', 'payer','livrer'])
            ->sum('detail_ventes.quantite');

        // ----------------------------------------------------------------
        // 📦 SECTION B : RÉSUMÉ DES STOCKS & INVENTAIRE LOCAL
        // ----------------------------------------------------------------
        
        $depotId = auth()->user()->depot_id;

        // Compteur 1 : Casiers de 12
        $stockCasiers12 = DB::table('produits')
            ->where('depot_id', $depotId)
            ->where(function($query) {
                $query->where('format_p', 'LIKE', '%12%')
                      ->orWhere('format_p', 'LIKE', '%casier 12%');
            })->sum('quantite_p');

        // Compteur 2 : Casiers de 24
        $stockCasiers24 = DB::table('produits')
            ->where('depot_id', $depotId)
            ->where('format_p', 'LIKE', '%24%')
            ->sum('quantite_p');

        // Compteur 3 : Uniquement les Bidons / Fûts
        $stockBidons = DB::table('produits')
            ->where('depot_id', $depotId)
            ->where('format_p', 'LIKE', '%bidon%')
            ->sum('quantite_p');

        // Compteur 4 : Uniquement les Packs (ex: Pack 6)
        $stockPacks = DB::table('produits')
            ->where('depot_id', $depotId)
            ->where('format_p', 'LIKE', '%pack%')
            ->sum('quantite_p');

        // Compteur 5 : Autres formats marginaux s'il y en a
        $stockAutres = DB::table('produits')
            ->where('depot_id', $depotId)
            ->where(function($query) {
                $query->where('format_p', 'NOT LIKE', '%12%')
                      ->where('format_p', 'NOT LIKE', '%24%')
                      ->where('format_p', 'NOT LIKE', '%bidon%')
                      ->where('format_p', 'NOT LIKE', '%pack%');
            })->sum('quantite_p');

        // Grand total physique
        $totalStockPhysique = DB::table('produits')->where('depot_id', $depotId)->sum('quantite_p');

        // 6. Boissons Attendues (Somme des entrées du jour)
        $totalApprovisionneJour = DB::table('detail_approvisionnements')
            ->join('approvisionnements', 'detail_approvisionnements.approvisionnement_id', '=', 'approvisionnements.id_app')
            ->where('approvisionnements.depot_id', $depotId)
            ->whereDate('approvisionnements.created_at', $today)
            ->sum('detail_approvisionnements.quantite_ajoutee');

        // 7. Nombre de références en Rupture / Stock Faible (quantite_p <= seuil)
        $nbRuptures = DB::table('produits')
            ->where('depot_id', $depotId)
            ->whereRaw('quantite_p <= seuil')
            ->count();

        // 8. Total des références distinctes au catalogue du dépôt
        $totalReferences = DB::table('produits')
            ->where('depot_id', $depotId)
            ->count();

        // ----------------------------------------------------------------
        // 🍺 SECTION C : TOP 3 DES MEILLEURES VENTES BRASSERIES (DU MOIS)
        // ----------------------------------------------------------------
        $topVentes = DB::table('detail_ventes')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereMonth('ventes.created_at', $currentMonth)
            ->whereYear('ventes.created_at', $currentYear)
            ->whereIn('ventes.statut', ['attente', 'payer', 'livrer'])
            ->select('produits.nom_p', 'produits.format_p', DB::raw('SUM(detail_ventes.quantite) as total_vendu'))
            ->groupBy('produits.id_produit', 'produits.nom_p', 'produits.format_p')
            ->orderBy('total_vendu', 'desc')
            ->take(3)
            ->get();

        // ----------------------------------------------------------------
        // 📈 SECTION D : CHIFFRE D'AFFAIRES & ANALYSE DE TRÉSORERIE MENSUELLE
        // ----------------------------------------------------------------
        $depotId = auth()->user()->depot_id;
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // 1. Chiffre d'Affaires Mensuel Brut (Somme de toutes les ventes 'payer' et 'attente' du mois)
        $caMensuelBrut = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u')
            ->where('users.depot_id', $depotId)
            ->whereMonth('ventes.created_at', $currentMonth)
            ->whereYear('ventes.created_at', $currentYear)
            ->whereIn('ventes.statut', [ 'payer', 'livrer'])
            ->sum('ventes.montant_total');

        // 2. Crédits (Dettes) Cumulés sur le mois en cours (La part non payée des factures)
        // Somme des (Montant Total - Montant Versé) pour les factures du mois
        $creditsMensuels = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u')
            ->where('users.depot_id', $depotId)
            ->whereMonth('ventes.created_at', $currentMonth)
            ->whereYear('ventes.created_at', $currentYear)
            ->whereIn('ventes.statut', ['payer', 'livrer'])
            ->select(DB::raw('SUM(montant_total - montant_verse) as total_credits'))
            ->first()->total_credits ?? 0;

        // 3. Trésorerie Nette Encaissée (L'argent réel qui est entré dans la caisse)
        $cashEncaisseMensuel = $caMensuelBrut - $creditsMensuels;

        // 4. Taux de Recouvrement / Liquidité Nette (Ratio de cash réel)
        $tauxLiquidite = $caMensuelBrut > 0 ? round(($cashEncaisseMensuel / $caMensuelBrut) * 100) : 100;

        // ----------------------------------------------------------------
        // 💎 SECTION E : CLASSEMENT DE LA CLIENTÈLE LA PLUS FIDÈLE
        // ----------------------------------------------------------------
        $clientsFideles = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereIn('ventes.statut', ['payer', 'livrer'])
            ->select(
                'users.nom_u',
                'users.tel_u',          // 💡 Correction ici : 'tel_u' au lieu de 'telephone_u'
                'users.localisation_u', // 💡 Correction ici : 'localisation_u' au lieu de 'adresse_u'
                DB::raw('COUNT(ventes.id) as total_achats'),
                DB::raw('SUM(ventes.montant_total) as capital_investi')
            )
            ->groupBy('users.id_u', 'users.nom_u', 'users.tel_u', 'users.localisation_u')
            ->orderBy('capital_investi', 'desc')
            ->take(5)
            ->get();
        // 📊 GRAPH REPARTITION DES VENTES PAR FOURNISSEUR (DU MOIS)
        $ventesParFournisseur = DB::table('detail_ventes')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
            ->join('fournisseurs', 'produits.id_fournisseur', '=', 'fournisseurs.id_fournisseur')
            ->whereIn('ventes.statut', ['attente', 'payer', 'livrer']) // On prend aussi les attentes comptoir pour alimenter le graphe
            ->whereMonth('ventes.created_at', Carbon::now()->month)
            ->whereYear('ventes.created_at', Carbon::now()->year)
            ->select('fournisseurs.nom_fournisseur', DB::raw('SUM(detail_ventes.quantite) as volume_total'))
            ->groupBy('fournisseurs.id_fournisseur', 'fournisseurs.nom_fournisseur')
            ->get();

        // 💡 Sécurité : Si le tableau est vide, on injecte des données de simulation pour l'affichage initial
        if ($ventesParFournisseur->isEmpty()) {
            $labelsFournisseurs = ['SABC', 'GUINNESS', 'UCB'];
            $donneesVentes = [0, 0, 0] ; // Sera géré proprement par le script
        } else {
            $labelsFournisseurs = $ventesParFournisseur->pluck('nom_fournisseur')->toArray();
            $donneesVentes = $ventesParFournisseur->pluck('volume_total')->map(function($val) {
                return (int)$val;
            })->toArray();
        }



        return view('layout.Personnel.dashboard', compact(
            'caJournalier', 'nbDettesJour', 'nbCommandesWeb', 'volumeVenduesJour',
            'totalStockPhysique', 'totalApprovisionneJour', 'nbRuptures', 'totalReferences',
            'topVentes', 'clientsFideles', 'stockCasiers12', 'stockCasiers24', 'caMensuelBrut', 'creditsMensuels', 'cashEncaisseMensuel',
            'tauxLiquidite', 'labelsFournisseurs', 'donneesVentes','stockBidons','stockAutres','stockPacks'
        ));
    }
    public function showpageclient()
    {
        $query = User::where('role_u', 'client');

        // Si ce n'est pas l'admin général, on restreint l'affichage à son dépôt d'affectation
        if (Auth::user()->role_u !== 'admin') {
            $query->where('depot_id', Auth::user()->depot_id);
        }

        $depotId = auth()->user()->depot_id;

    // 💡 SÉCURISATION DES SOUS-REQUÊTES : Niveau unique pour éviter l'erreur 1054 de MariaDB
    $clients = DB::table('users')
        ->where('users.depot_id', $depotId)
        ->where('users.role_u', 'client')
        ->select(
            '*',
            
            // 🔄 COMPILATION COMPTABLE DU BILAN DE CHAQUE FOURNISSEUR POUR CE CLIENT
            DB::raw("
                (SELECT GROUP_CONCAT(CONCAT(f.nom_fournisseur, ':', 
                    -- Formule : TOTAL ACHETÉ - TOTAL RENDU
                    (IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN ventes v ON dv.vente_id = v.id JOIN produits p ON dv.produit_id = p.id_produit WHERE v.client_id = users.id_u AND v.statut = 'livrer' AND p.id_fournisseur = f.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) - 
                     IFNULL((SELECT SUM(rc.quantite_rendue) FROM retours_casiers rc WHERE rc.client_id = users.id_u AND rc.fournisseur_id = f.id_fournisseur), 0)),
                    ':',
                    -- Indicateur d'activité : TOTAL ACHETÉ + TOTAL RENDU
                    (IFNULL((SELECT SUM(dv.quantite) FROM detail_ventes dv JOIN ventes v ON dv.vente_id = v.id JOIN produits p ON dv.produit_id = p.id_produit WHERE v.client_id = users.id_u AND v.statut = 'livrer' AND p.id_fournisseur = f.id_fournisseur AND (p.format_p LIKE '%casier%' OR p.format_p LIKE '%cs%')), 0) + 
                     IFNULL((SELECT SUM(rc.quantite_rendue) FROM retours_casiers rc WHERE rc.client_id = users.id_u AND rc.fournisseur_id = f.id_fournisseur), 0))
                ) SEPARATOR '|') 
                 FROM fournisseurs f
                 -- On force SQL à n'inclure le bloc dans la chaîne que si la somme activité est supérieure à 0 (Règle stricte demandée)
                 HAVING COUNT(f.id_fournisseur) > 0
                ) as bilan_consignes_global
            ")
        )
        ->orderBy('users.nom_u', 'asc')
        ->get();

    return view('layout.personnel.client', compact('clients'));

    }
    public function showpagehistorique()
    {
        $user = Auth::user();
        $role = $user->role_u;
        $depotId = $user->depot_id;

        // 1. Liste des dépôts pour le bandeau horizontal de filtrage de l'admin
        $depots = DB::table('depots')->orderBy('nom_depot', 'asc')->get();

        $approvisionnements = collect();
        $ventes = collect();

        // ====================================================================
        // 🛡️ SÉCURITÉ SOUVERAINE : FLUX DE L'ADMINISTRATEUR (ACCÈS TOUT RÉSEAU)
        // ====================================================================
        if ($role === 'admin') {
    
            // 💡 MISE À JOUR : Remplacement du gestionnaire par le Nom du Fournisseur
            $approvisionnements = DB::table('detail_approvisionnements')
                ->join('approvisionnements', 'detail_approvisionnements.approvisionnement_id', '=', 'approvisionnements.id_app')
                ->join('produits', 'detail_approvisionnements.produit_id', '=', 'produits.id_produit')
                ->join('depots', 'approvisionnements.depot_id', '=', 'depots.id')
                ->join('fournisseurs', 'approvisionnements.id_fournisseur', '=', 'fournisseurs.id_fournisseur') // Jointure fournisseur
                ->select(
                    'approvisionnements.id_app',
                    'approvisionnements.created_at',
                    'approvisionnements.total_boissons',
                    'depots.nom_depot',
                    'fournisseurs.nom_fournisseur', // Extraction de la brasserie
                    // Compilation des boissons du bon
                    DB::raw("GROUP_CONCAT(CONCAT('• ', UPPER(produits.nom_p), ' (', produits.format_p, ') : <b>', CAST(detail_approvisionnements.quantite_ajoutee AS UNSIGNED), '</b> ') SEPARATOR '|') as liste_produits_html")
                )
                ->groupBy(
                    'approvisionnements.id_app',
                    'approvisionnements.created_at',
                    'approvisionnements.total_boissons',
                    'depots.nom_depot',
                    'fournisseurs.nom_fournisseur' // Regroupement propre
                )
                ->orderBy('approvisionnements.created_at', 'desc')
                ->get();

            // L'admin charge l'intégralité des factures de sorties
            $ventes = Vente::with(['client.depot', 'employe', 'detailVentes.produit'])
                ->whereIn('statut', ['livrer', 'payer', 'annuler'])
                ->orderBy('created_at', 'desc')
                ->get();
        }



        // ====================================================================
        // 🛡️ SÉCURITÉ SOUVERAINE : FLUX DE L'EMPLOYÉ (ACCÈS COUPE RESTREINT)
        // ====================================================================
        else {
            // L'employé n'a pas le droit d'interroger la table des approvisionnements (Reste vide)
            $approvisionnements = collect();

            // L'employé ne récupère STRICTEMENT que les ventes liées à des clients de son dépôt
            $ventes = Vente::with(['client.depot', 'employe', 'detailVentes.produit'])
                ->whereIn('statut', ['livrer', 'payer', 'annuler'])
                ->whereHas('employe', function($query) use ($depotId) {
                    $query->where('depot_id', $depotId);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            
        }
        $ventes->map(function($vente) {
            $nomFichier = 'facture_' . $vente->code_facture . '.pdf';
            $cheminFichier = 'factures/' . $nomFichier;

            if (Storage::disk('public')->exists($cheminFichier)) {
                $vente->url_pdf = asset('storage/factures/' . $nomFichier);
            } else {
                $vente->url_pdf = null;
            }
            return $vente;
        });

        return view('layout.Personnel.historique', compact('approvisionnements', 'ventes', 'depots'));
    }
    public function showpageproduit() {
        // 1. Récupération des fournisseurs pour votre liste déroulante du formulaire
        $fournisseurs = Fournisseur::orderBy('nom_fournisseur', 'asc')->get();

        // 2. Récupération de TOUS les produits du dépôt avec leur fournisseur en 1 seule requête
        $produitsParFournisseur = Produit::with('fournisseur')
            ->where('depot_id', Auth::user()->depot_id)
            ->join('fournisseurs', 'produits.id_fournisseur', '=', 'fournisseurs.id_fournisseur')
            ->orderBy('fournisseurs.nom_fournisseur', 'asc') // Tri par fournisseur
            ->orderBy('produits.nom_p', 'asc')               // Puis par nom de boisson
            ->select('produits.*')                           // Évite les conflits d'ID
            ->get()
            ->groupBy(function($produit) {
                // Regroupe automatiquement par le nom du fournisseur (ex: "SABC", "GUINNESS")
                return strtoupper($produit->fournisseur?->nom_fournisseur ?? 'AUTRES');
            });

        return view('layout.Personnel.produit', compact('fournisseurs', 'produitsParFournisseur'));
    }

    public function showpagestock()
    {
        $today = Carbon::today();

        // 💡 INJECTION ANALYTIQUE DES APPROVISIONNEMENTS ET DES VENTES DU JOUR
        $ProduitsParDepot = Produit::with([
            'depot', 
            // 1. Vos entrées de stock d'origine (Inaltérées)
            'details_approvisionnements' => function($query) use ($today) {
                $query->whereDate('created_at', $today);
            },
            // 2. 🛰️ NOUVEAU : Chargement des lignes de ventes validées ou en attente d'aujourd'hui
            'detailVentes' => function($query) use ($today) {
                $query->whereDate('updated_at', $today)
                    ->whereHas('vente', function($subQuery) {
                        // On ne filtre strictement que sur les bons 'attente' ou 'payer'
                        $subQuery->whereIn('statut', ['attente', 'payer']);
                    });
            }
        ])
        ->orderBy('nom_p', 'asc')
        ->get()
        ->groupBy(function($produit) {
            return $produit->depot?->nom_depot ?? 'Dépôt Central';
        });

        $fournisseurs = Fournisseur::orderBy('nom_fournisseur', 'asc')->get();
        
        return view('layout.Personnel.stock', compact('fournisseurs', 'ProduitsParDepot'));
    }
    public function showpageventes()
    {
        $user = Auth::user();

        // Votre facture 'draft' en cours d'origine reste inchangée
        $venteEnCours = Vente::with('detailVentes.produit')
            ->where('employe_id', $user->id_u)
            ->where('statut', 'draft')
            ->latest()
            ->first();

        $clientsRaw = User::where('role_u', 'client')
            ->where('depot_id', $user->depot_id)
            ->orderBy('nom_u', 'asc')
            ->get();

        // 💡 AJUSTEMENT LOGIQUE : On extrait l'état réel du panier du client Connecté
        $clients = $clientsRaw->map(function($client) {
            $bonActif = Vente::where('client_id', $client->id_u)
                ->whereIn('statut', ['draft', 'envoyer'])
                ->first();

            // On sauvegarde le statut exact ('brouillon', 'envoyer' ou null si rien)
            $client->statut_mobile = $bonActif ? $bonActif->statut : null;
            $client->est_bloque_caisse = $bonActif ? 1 : 0;
            return $client;
        });

        $produitsDuDepot = Produit::with('fournisseur')
            ->where('depot_id', $user->depot_id)
            ->orderBy('nom_p', 'asc')
            ->get();

        return view('layout.Personnel.vente', compact('clients', 'produitsDuDepot', 'venteEnCours'));
    }


    public function showpagecommandes(){
        // On récupère le dépôt de la secrétaire actuellement connectée
        $depotId = auth()->user()->depot_id;

        // 1. BLOC EN LIGNE : Paniers reçus des clients (Statut 'envoyer')
        $commandesRecues = Vente::with(['client', 'detailVentes.produit'])
            ->whereHas('client', function($query) use ($depotId) {
                $query->where('depot_id', $depotId); // Uniquement les clients de son dépôt
            })
            ->where('statut', 'envoyer')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. BLOC COMPTOIR : Commandes mises de côté au guichet (Statut 'attente')
        $commandesEnAttente = Vente::with(['client', 'detailVentes.produit'])
            ->where('statut', 'attente')
            ->where('montant_total', '>', 0) // Évite les paniers vides
            ->orderBy('created_at', 'desc')
            ->get();

        // 🚨 LE CORRECTIF ICI : On injecte les deux variables attendues par la vue me-1
        return view('layout.Personnel.commande', compact('commandesRecues', 'commandesEnAttente'));
    }
    public function afficherFactureArchivee($id)
{
    $vente = Vente::findOrFail($id);
    $nomFichier = 'facture_' . $vente->code_facture . '.pdf';
    
    // 💡 CHEMIN SUR-MESURE : On pointe exactement vers ton dossier actuel
    $cheminFichier = 'private/public/factures/' . $nomFichier;

    if (!Storage::exists($cheminFichier)) {
        abort(404, "Le fichier de la facture n'existe pas dans les archives.");
    }

    // Récupère le chemin physique absolu du fichier sur le serveur
    $cheminAbsolu = Storage::path($cheminFichier);

    // Renvoyer le fichier directement à l'écran de manière étanche
    return response()->file($cheminAbsolu, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $nomFichier . '"'
    ]);
}
}
