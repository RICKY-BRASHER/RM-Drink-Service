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
            ->whereIn('ventes.statut', ['attente', 'payer'])
            ->sum('detail_ventes.quantite');

        // ----------------------------------------------------------------
        // 📦 SECTION B : RÉSUMÉ DES STOCKS & INVENTAIRE LOCAL
        // ----------------------------------------------------------------
        
        // 5. Quantité totale de boissons actuellement en rayon au dépôt
        $totalStockPhysique = DB::table('produits')
            ->where('depot_id', $depotId)
            ->sum('quantite_p');

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
            ->whereIn('ventes.statut', ['attente', 'payer'])
            ->select('produits.nom_p', 'produits.format_p', DB::raw('SUM(detail_ventes.quantite) as total_vendu'))
            ->groupBy('produits.id_produit', 'produits.nom_p', 'produits.format_p')
            ->orderBy('total_vendu', 'desc')
            ->take(3)
            ->get();

        // ----------------------------------------------------------------
        // 📈 SECTION D : CHIFFRE D'AFFAIRES MENSUEL GLOBAL
        // ----------------------------------------------------------------
        $caMensuel = DB::table('recharges')
            ->join('users', 'recharges.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereMonth('recharges.created_at', $currentMonth)
            ->whereYear('recharges.created_at', $currentYear)
            ->whereIn('recharges.type_mouvement', ['achat_direct', 'creation_dette'])
            ->sum('recharges.montant_r');

        // ----------------------------------------------------------------
        // 💎 SECTION E : CLASSEMENT DE LA CLIENTÈLE LA PLUS FIDÈLE
        // ----------------------------------------------------------------
        $clientsFideles = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u') // Jointure sur id_u
            ->where('users.depot_id', $depotId)
            ->whereIn('ventes.statut', ['attente', 'payer'])
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

        return view('layout.personnel.dashboard', compact(
            'caJournalier', 'nbDettesJour', 'nbCommandesWeb', 'volumeVenduesJour',
            'totalStockPhysique', 'totalApprovisionneJour', 'nbRuptures', 'totalReferences',
            'topVentes', 'caMensuel', 'clientsFideles'
        ));
    }
    public function showpageclient()
    {
        $query = User::where('role_u', 'client');

        // Si ce n'est pas l'admin général, on restreint l'affichage à son dépôt d'affectation
        if (Auth::user()->role_u !== 'admin') {
            $query->where('depot_id', Auth::user()->depot_id);
        }

        $clients = $query->orderBy('nom_u', 'asc')->get();
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
            
            // 💡 REQUÊTE CONNECTÉE AUX 3 TABLES D'ENTRÉES : detail_approvisionnements + approvisionnements + produits
            $approvisionnements = DB::table('detail_approvisionnements')
                ->join('approvisionnements', 'detail_approvisionnements.approvisionnement_id', '=', 'approvisionnements.id_app')
                ->join('produits', 'detail_approvisionnements.produit_id', '=', 'produits.id_produit')
                ->join('depots', 'approvisionnements.depot_id', '=', 'depots.id') // Jointure sur depots.id selon votre migration
                ->join('users as employes', 'approvisionnements.id_u_admin', '=', 'employes.id_u') // Jointure sur id_u_admin
                ->select(
                    'detail_approvisionnements.quantite_ajoutee',
                    'approvisionnements.created_at',
                    'approvisionnements.id_app',
                    'produits.nom_p',
                    'produits.format_p',
                    'depots.nom_depot',
                    'employes.nom_u as nom_employe'
                )
                ->orderBy('approvisionnements.created_at', 'desc')
                ->get();

            // L'admin charge l'intégralité des factures de sorties validées ou en attente
            $ventes = Vente::with(['client.depot', 'employe', 'detailVentes.produit'])
                ->whereIn('statut', ['attente', 'payer'])
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
                ->whereIn('statut', ['attente', 'payer'])
                ->whereHas('client', function($query) use ($depotId) {
                    $query->where('depot_id', $depotId);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('layout.personnel.historique', compact('approvisionnements', 'ventes', 'depots'));
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

        return view('layout.personnel.produit', compact('fournisseurs', 'produitsParFournisseur'));
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
        
        return view('layout.personnel.stock', compact('fournisseurs', 'ProduitsParDepot'));
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

        return view('layout.personnel.vente', compact('clients', 'produitsDuDepot', 'venteEnCours'));
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
    
}
