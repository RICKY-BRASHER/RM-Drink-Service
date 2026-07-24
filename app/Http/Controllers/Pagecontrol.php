<?php

namespace App\Http\Controllers;
use App\Models\Depot;
use App\Models\User;
use App\Models\Recharge;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class Pagecontrol extends Controller
{
    public function showLandingPage()
    {
        return view('layout.landing');
    }
    public function showLoginClient(){
        $depot = Depot::all();// Récupère tous les dépôts de la table 'depot'
        return view('auth.client.connect', compact('depot'));
    }
    public function showLoginForm()
    {
        return view('auth.personnel.loginpro');
    }
    public function showaccueiladmin()
    {
       // 1. SÉCURITÉ ACCÈS : Réservé strictement à la Direction Générale
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            abort(403, 'Accès réservé à la direction générale.');
        }

        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // ====================================================================
        // 📈 SECTION 1 : LES GRANDES MASSES FINANCIÈRES (CUMUL RÉSEAU)
        // ====================================================================
        
        // CA Journalier Cumulé (Somme de montant_r du jour pour les ventes)
        $caGlobalJour = DB::table('recharges')
            ->whereDate('created_at', $today)
            ->whereIn('type_mouvement', ['achat_direct', 'creation_dette'])
            ->sum('montant_r');

        // CA Mensuel Cumulé (Trésorerie Centrale)
        $caGlobalMois = DB::table('recharges')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereIn('type_mouvement', ['achat_direct', 'creation_dette'])
            ->sum('montant_r');

        // Somme géante de toutes les dettes actives non recouvrées du Cameroun
        $detteTotaleResseau = DB::table('users')
            ->where('role_u', 'client')
            ->where('solde_u', '<', 0)
            ->sum('solde_u');
        $detteTotaleResseau = abs($detteTotaleResseau); 

        // Nombre de paniers mobiles actuellement en attente d'interception par les secrétariats
        $totalCommandesWebEnAttente = DB::table('ventes')
            ->where('statut', 'envoyer')
            ->count();

        // ====================================================================
        // 📍 SECTION 2 : PERFORMANCES ET RENTABILITÉ COMPARÉE DES DÉPÔTS
        // ====================================================================
        // Jointure chirurgicale sur depots.id et users.depot_id
        $performancesDepots = DB::table('depots')
            ->leftJoin('users', 'depots.id', '=', 'users.depot_id')
            ->leftJoin('recharges', function($join) use ($currentMonth, $currentYear) {
                $join->on('users.id_u', '=', 'recharges.client_id')
                     ->whereMonth('recharges.created_at', $currentMonth)
                     ->whereYear('recharges.created_at', $currentYear)
                     ->whereIn('recharges.type_mouvement', ['achat_direct', 'creation_dette']);
            })
            ->select(
                'depots.id as depot_id_real',
                'depots.nom_depot',
                'depots.adresse_depot',
                DB::raw('IFNULL(SUM(recharges.montant_r), 0) as ca_mensuel_depot'),
                DB::raw('(SELECT COUNT(id_produit) FROM produits WHERE depot_id = depots.id) as total_references'),
                DB::raw('(SELECT COUNT(id_u) FROM users WHERE depot_id = depots.id AND role_u = "employe") as nb_agents')
            )
            ->groupBy('depots.id', 'depots.nom_depot', 'depots.adresse_depot')
            ->orderBy('ca_mensuel_depot', 'desc')
            ->get();

        // ====================================================================
        // 🦁 SECTION 3 : PART DE MARCHÉ DES BRASSEURS (VOLUME DU MOIS)
        // ====================================================================
        // Jointure sur detail_ventes.produit_id -> produits.id_produit -> fournisseurs.id_fournisseur
        $partsBrasseurs = DB::table('detail_ventes')
            ->join('ventes', 'detail_ventes.vente_id', '=', 'ventes.id')
            ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
            ->join('fournisseurs', 'produits.id_fournisseur', '=', 'fournisseurs.id_fournisseur')
            ->whereMonth('ventes.created_at', $currentMonth)
            ->whereYear('ventes.created_at', $currentYear)
            ->whereIn('ventes.statut', ['attente', 'payer'])
            ->select('fournisseurs.nom_fournisseur', DB::raw('SUM(detail_ventes.quantite) as volume_vendu')) // nom_fournisseur selon votre structure
            ->groupBy('fournisseurs.id_fournisseur', 'fournisseurs.nom_fournisseur')
            ->orderBy('volume_vendu', 'desc')
            ->get();

        // ====================================================================
        // 🏆 SECTION 4 : TOP 5 DES PLUS GROS ACHETEURS (LE HIT-PARADE NATIONAL)
        // ====================================================================
        // Jointure sur ventes.client_id -> users.id_u -> depots.id
        $topAcheteursNationaux = DB::table('ventes')
            ->join('users', 'ventes.client_id', '=', 'users.id_u')
            ->join('depots', 'users.depot_id', '=', 'depots.id')
            ->whereIn('ventes.statut', ['attente', 'payer'])
            ->select(
                'users.nom_u',
                'users.tel_u',          // tel_u de votre BDD
                'users.solde_u',        // solde_u de votre BDD
                'depots.nom_depot',
                DB::raw('COUNT(ventes.id) as total_factures'),
                DB::raw('SUM(ventes.montant_total) as chiffre_affaire_genere')
            )
            ->groupBy('users.id_u', 'users.nom_u', 'users.tel_u', 'users.solde_u', 'depots.nom_depot')
            ->orderBy('chiffre_affaire_genere', 'desc')
            ->take(5)
            ->get();

        return view('layout.admin.Accueil', compact(
            'caGlobalJour', 'caGlobalMois', 'detteTotaleResseau', 'totalCommandesWebEnAttente',
            'performancesDepots', 'partsBrasseurs', 'topAcheteursNationaux'
        ));
    }
    public function showpageemploye()
    {
        // On récupère TOIS les dépôts de la table 'depot' par ordre alphabétique
        $depots = Depot::orderBy('nom_depot', 'asc')->get();
        $employes = User::where('role_u', 'employe')->get(); // Récupère tous les employés
        return view('layout.admin.employe', compact('depots', 'employes'));
    }
   public function showpagecontroldepot()
    {
        // 💡 INJECTION DU COMPTEUR : Charge les dépôts en comptant les users au rôle 'employe'
        $depots = Depot::withCount('employes')
            ->orderBy('nom_depot', 'asc')
            ->get();

        // On envoie la variable bonifiée à la vue Blade
        return view('layout.admin.controldepot', compact('depots'));
    }
    public function showtransaction()
    {
        if (!Auth::check() || Auth::user()->role_u !== 'admin') {
            abort(403, 'Accès réservé à la direction générale.');
        }

        // 💡 AJUSTEMENT COMPTABLE : On charge de force la relation 'vente' pour auditer les totaux
        $recharges = Recharge::with(['client.depot', 'employe', 'vente'])
            ->orderBy('created_at', 'desc')
            ->get();

        $depots = Depot::orderBy('nom_depot', 'asc')->get();

        // Calcul des indicateurs inchangés (KPI)
        $totalEspeces  = $recharges->where('moyen_recharge', 'especes')->sum('montant_r');
        $totalMobile   = $recharges->whereIn('moyen_recharge', ['momo', 'om'])->sum('montant_r');
        $volumeGeneral = $recharges->sum('montant_r');

        return view('layout.admin.Transaction', compact(
            'recharges', 
            'depots', 
            'totalEspeces', 
            'totalMobile', 
            'volumeGeneral'
        ));
    }

    
}
