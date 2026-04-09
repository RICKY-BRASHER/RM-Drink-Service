<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Depot Biere</title>
    <link rel="stylesheet" href="{{asset('css/depot.css')}}">
    <script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
</head>
<body>
<aside id="dashboard">
    <div id="logo">
        <h1>R~M DRINK<br>MANAGER</h1>
        <span>/ Votre Stock, Une Priorite /</span>
    </div>

    <!-- Tableau de Bord -->
    <div class="nav {{ Route::is('dashboard') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('dashboard') }}'">
        <span class="nav-icon">📊</span> Tableau de Bord
    </div>

    <!-- Stock -->
    <div class="nav {{ Route::is('stock') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('stock') }}'">
        <span class="nav-icon">🏭</span> Stock
    </div>

    <!-- Produits -->
    <div class="nav {{ Route::is('produit') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('produit') }}'">
        <span class="nav-icon">📦</span> Produits
    </div>

    <!-- Ventes -->
    <div class="nav {{ Route::is('vente') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('vente') }}'">
        <span class="nav-icon">💳</span> Ventes
    </div>

    <!-- Ristounes -->
    <div class="nav">
        <span class="nav-icon">🏭</span> Ristounes
    </div>

    <!-- Clients -->
    <div class="nav {{ Route::is('client') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('client') }}'">
        <span class="nav-icon">👥</span> Clients
    </div>

    <!-- Historique -->
    <div class="nav {{ Route::is('historique') ? 'active' : '' }}" 
         onclick="window.location.href='{{ route('historique') }}'">
        <span class="nav-icon">📊</span> Historique
    </div>

    <div class="nav-footer">
        <p><span style="color: rgb(71, 3, 134);">{{ $info_u->nom }}</span>
        <br><span id="deconnect" onclick="window.location.href='{{ route('logout') }}'"> Se Deconnecter</span></p>
    </div>
</aside>

    