<?php
use App\Http\Controllers\ClientShow;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Authentification;
use App\Http\Controllers\Pagecontrol;
use App\Http\Controllers\Workstocksell;
use App\Http\Controllers\PagePersonnel;
use App\Http\Controllers\ActionAdd;

// ==========================================
// 1. ROUTES SÉCURISÉES DE LA DIRECTION (ADMIN)
// ==========================================

Route::middleware(['is_admin'])->group(function () {
    Route::middleware(['is_secretariat'])->group(function () {
        Route::get('/gestion/personnel/dashboard', [PagePersonnel::class, 'showpagedashboard'])->name('dashboard');
        Route::get('/gestion/personnel/commandes', [PagePersonnel::class, 'showpagecommandes'])->name('commandes');
        Route::get('/gestion/ventes', [PagePersonnel::class, 'showpageventes'])->name('vente');
        Route::post('/admin/client/store', [ActionAdd::class, 'addmodclient'])->name('client.store');
        Route::post('/gestion/clients/recharger', [Workstocksell::class, 'effectuerRecharge'])->name('client.recharger');
        Route::post('/vente/add-line', [Workstocksell::class, 'addLine'])->name('vente.addLine');
        Route::get('/vente/current', [Workstocksell::class, 'getCurrent'])->name('vente.current');
        Route::post('/gestion/caisse/ajouter-article', [Workstocksell::class, 'ajouterArticle']);
        Route::delete('/gestion/caisse/supprimer-article/{id}', [Workstocksell::class, 'supprimerArticle']);
        Route::post('/gestion/caisse/cloturer-vente', [Workstocksell::class, 'cloturerVente']);
        Route::delete('/gestion/caisse/annuler-brouillon/{id}', [Workstocksell::class, 'annulerBrouillon']);
        // Route AJAX unique pour traiter la validation ou le refus
        Route::post('/gestion/commandes-attente/traiter/{id}', [Workstocksell::class, 'traiterCommandeEnAttente']);
        Route::get('/gestion/caisse/imprimer-pdf/{id}', [Workstocksell::class, 'telechargerFacture'])->name('facture.pdf');

        Route::get('/Distrib237/secretariat/commande/recuperer-edition/{id}', [Workstocksell::class, 'recupererDonneesModification']);
        Route::get('/Distrib237/secretariat/commande/liste-produits-depot', [Workstocksell::class, 'listeProduitsDepot']);
        Route::post('/Distrib237/secretariat/commande/sauvegarder-ajustements/{id}', [Workstocksell::class, 'sauvegarderAjustements']);
        Route::get('/gestion/produit', [PagePersonnel::class, 'showpageproduit'])->name('produit');
        Route::post('/admin/produit/store', [ActionAdd::class, 'addmodproduit'])->name('produit.store');


    });

    Route::middleware(['admin_uniq'])->group(function () {
        Route::get('/admin/gestion/dashboard', [Pagecontrol::class, 'showaccueiladmin'])->name('accueil.admin');
        Route::get('/admin/gestion/controldepot', [Pagecontrol::class, 'showpagecontroldepot'])->name('controldepot');
        Route::get('/admin/gestion/transaction', [Pagecontrol::class, 'showtransaction'])->name('transaction');
        Route::get('/admin/gestion/employe', [Pagecontrol::class, 'showpageemploye'])->name('employe');
        Route::post('/admin/gestion/createdepot', [ActionAdd::class, 'addmoddepot'])->name('depot.store');
        Route::post('/admin/personnel/store', [ActionAdd::class, 'addmodemploye'])->name('personnel.store');
        Route::post('/gestion/stocks/modifier/{id}', [Workstocksell::class, 'updateStockInline']);
        Route::post('/gestion/stocks/reapprovisionner', [Workstocksell::class, 'storeRestock'])->name('stock.store');
            Route::get('/gestion/fournisseur/{id}/produits-tous-depots', [Workstocksell::class, 'getProduitsTousDepots']);
        // 1. Pour charger la liste des dépôts du fournisseur choisi
        Route::get('/gestion/fournisseur/{id}/depots-disponibles', [Workstocksell::class, 'getDepotsFournisseur']);
         // 2. Pour charger la liste propre des boissons du fournisseur pour ce dépôt précis
        Route::get('/gestion/fournisseur/{id_f}/depot/{id_d}/produits', [Workstocksell::class, 'getProduitsFiltres']);
    });

    Route::get('/gestion/client', [PagePersonnel::class, 'showpageclient'])->name('client');
    Route::get('/gestion/historique', [PagePersonnel::class, 'showpagehistorique'])->name('historique');
    Route::get('/gestion/stock', [PagePersonnel::class, 'showpagestock'])->name('stock'); 
    // Route d'extinction du bloc des stocks
    Route::post('/Distrib237/caissier/marquer-alerte-lue/{id}', function($id) {
        \Illuminate\Support\Facades\DB::table('alertes_stocks')->where('id_alerte', $id)->update(['statut_lecture' => 'lu']);
        return response()->json(['success' => true]);
    });

    


});

// 2. ROUTES SÉCURISÉES DE L'ESPACE CLIENT
Route::middleware(['is_client'])->group(function () {
    // Routes réservées aux clients
    
    Route::get('/Distrib237/client/transactions', [ClientShow::class,'showtransactions'])->name('client.transactions');
    Route::get('/Distrib237/client/commande', [ClientShow::class,'showcommande'])->name('client.commande');
    Route::get('/Distrib237/client/accueil', [ClientShow::class,'showaccueil'])->name('client.accueil');
    Route::get('/Distrib237/client/suivie', [ClientShow::class,'showsuivie'])->name('client.suivie');
    Route::get('/Distrib237/client/profil', [ClientShow::class,'showprofil'])->name('client.profil');
    Route::post('/Distrib237/client/ajouter-brouillon', [ClientShow::class, 'ajouterAuPanierBrouillon'])->name('client.ajout.produit');
    Route::delete('/Distrib237/client/supprimer-produit/{id}', [ClientShow::class, 'supprimerDuPanier'])->name('client.supprimer.produit');
    Route::post('/Distrib237/client/finaliser-panier', [ClientShow::class, 'finaliserPanierEnvoyer'])->name('client.finaliser.produit');
    Route::get('/Distrib237/client/recuperer-details-bon/{id}', [ClientShow::class, 'recupererDetailsBon']);


});

// ROUTES PUBLIQUES : ACCESSIBLES UNIQUEMENT AUX PERSONNES NON CONNECTÉES
Route::middleware(['guest_custom'])->group(function () {
    
    Route::get('/', [Pagecontrol::class, 'showLandingPage'])->name('LandingPage');
    Route::get('connect_customer', [Pagecontrol::class, 'showLoginClient'])->name('login.client');
    Route::get('login_pro', [Pagecontrol::class, 'showLoginForm'])->name('login.pro');
    Route::post('register_client', [Authentification::class, 'register_client'])->name('register.client');
    Route::post('login_client', [Authentification::class, 'login_client'])->name('login.client.submit');
    Route::post('login_pro', [Authentification::class, 'login_pro'])->name('login.pro.submit');
    
});
// Route d'extinction du bloc des messages
Route::post('/Distrib237/notification/marquer-lue/{id}', function($id) {
    \Illuminate\Support\Facades\DB::table('notifications_systeme')->where('id_notif', $id)->update(['statut_lecture' => 'lu']);
    return response()->json(['success' => true]);
});
Route::post('/Distrib237/client/modifier-avatar', [ClientShow::class, 'modifierAvatarClient']);
Route::get('logout', [Authentification::class, 'logout'])->name('logout');

require __DIR__.'/settings.php';
