<?php
use App\Models\Client;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use App\Http\Controllers\Authentification;
use App\Http\Controllers\Pagecontrol;



Route::middleware(['isAuth'])->group(function () {

    Route::get('/dashboard', [Pagecontrol::class, 'dashboard'])->name('dashboard');
    Route::get('/stock', [Pagecontrol::class, 'stock'])->name('stock');
    Route::get('/client', [Pagecontrol::class, 'client'])->name('client');
    Route::get('/vente', [Pagecontrol::class, 'vente'])->name('vente');
    Route::get('/historique', [Pagecontrol::class, 'historique'])->name('historique');
    Route::get('/produit', [Pagecontrol::class, 'produit'])->name('produit');

});
Route::middleware(['isAuth'])->group(function(){
    Route::post('/addclient', 'App\Http\Controllers\ActionAdd@addclient')->name('addclient');
    Route::post('/deleteclient/{id}', 'App\Http\Controllers\ActionAdd@deleteclient')->name('deleteclient');
    Route::get('/getclient/{id}', 'App\Http\Controllers\ActionAdd@getclient')->name('getclient');
    Route::post('/addproduit', 'App\Http\Controllers\ActionAdd@addproduit')->name('addproduit');
    Route::post('/deleteproduit/{id}', 'App\Http\Controllers\ActionAdd@deleteproduit')->name('deleteproduit');
    Route::get('/getproduit/{id}', 'App\Http\Controllers\ActionAdd@getproduit')->name('getproduit');
    Route::get('/logout', [Authentification::class, 'logout'])->name('logout');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/', 'App\Http\Controllers\Authentification@showLoginForm')->name('connexion');
    Route::get('/inscription',function(){
        return view('inscription');
    })->name('inscrit');
    Route::post('/inscrit','App\Http\Controllers\Authentification@inscrit');
    Route::post('/connecte', 'App\Http\Controllers\Authentification@connecte')->name('login.post');

});

require __DIR__.'/settings.php';
