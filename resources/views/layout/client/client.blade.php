<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DISTRIB 237 - Client Blue Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Lien vers ton fichier CSS personnalisé -->
    @vite(['resources/css/home.css'])
</head>
<body>

    <!-- Barre supérieure de l'application -->
    <header class="maxit-header sticky-top">
        <!-- Logo Officiel -->
        <div class="maxit-logo" onclick="window.location.href='/Distrib237/client/accueil'" style="cursor: pointer;">
            <i class="bi bi-shield-check"></i> DISTRIB 237
        </div>
        
        <div class="header-icons d-flex align-items-center gap-2">
            <!-- 🔍 Bouton Recherche Rapide -->
            <div class="icon-btn" id="clientHeaderSearchBtn" title="Rechercher"><i class="bi bi-search"></i></div> 
            
            
            <!-- 🔔 BOUTON CLOCHE CONNECTÉ VERS LA PAGE NOTIFICATIONS -->
            <div class="icon-btn text-white position-relative" id="clientHeaderNotifBtn" onclick="window.location.href='/Distrib237/client/notifications'" style="cursor: pointer; overflow: hidden; width: 34px; height: 34px; border-radius: 50%; background-color: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;" title="Notifications">
                
                <!-- Icône de la cloche centrée -->
                <i class="bi bi-bell fs-5"></i>
                
                <!-- 💡 PASTILLE BLEUE BOOTSTRAP DE NOTIFICATION NON LUE SUPERPOSÉE -->
                @if(isset($notificationsMessages) && count($notificationsMessages) > 0)
                    <span class="position-absolute p-1 bg-primary border border-dark rounded-circle" 
                        style="top: 5px; right: 5px;" 
                        id="notifBadge">
                    </span>
                @endif
            </div>

            
            

            <!-- 👤 BOUTON AVATAR CONNECTÉ VERS LA PAGE PROFIL -->
            <div class="icon-btn text-white" id="clientHeaderProfileBtn" onclick="window.location.href='/Distrib237/client/profil'" style="cursor: pointer; overflow: hidden; width: 34px; height: 34px; border-radius: 50%; background-color: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;" title="Mon Profil">
                @if(auth()->user()->avatar_u)
                    <!-- Affiche la vraie photo de profil de l'utilisateur si elle existe -->
                    <img src="{{ asset('storage/avatars/' . auth()->user()->avatar_u) }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    <i class="bi bi-person-circle fs-5"></i>
                @endif
            </div>
        </div>
    </header>


    <!-- Zone dynamique de vos pages -->
    <main>
        @yield('client_content')
    </main>

    <!-- 📱 NAVBAR INFÉRIEURE FIXE ULTRA-DYNAMIQUE (CYBER BLUE) -->
    <div class="maxit-bottom-bar">
        <div class="container-fluid">
            <div class="row text-center">
                
                <!-- 🏠 ONGLET ACCUEIL -->
                <div class="col">
                    <a href="{{ route('client.accueil') }}" class="maxit-nav-item {{ Route::is('client.accueil') ? 'active' : '' }}">
                        <i class="bi bi-grid-fill"></i>Accueil
                    </a>
                </div>
                
                <!-- 🍺 ONGLET COMMANDER -->
                <div class="col">
                    <a href="{{ route('client.commande') }}" class="maxit-nav-item {{ Route::is('client.commande') ? 'active' : '' }}">
                        <i class="bi bi-send"></i>Commander
                    </a>
                </div>
                
                <!-- 📦 ONGLET SUIVI -->
                <div class="col">
                    <a href="{{ route('client.suivie') }}" class="maxit-nav-item {{ Route::is('client.suivie') ? 'active' : '' }}">
                        <i class="bi bi-box-seam-fill"></i>Suivi
                    </a>
                </div>
                
                <!-- ⚙️ ONGLET PROFIL -->
                <div class="col">
                    <a href="{{ route('client.profil') }}" class="maxit-nav-item {{ Route::is('client.profil') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i>Profil
                    </a>
                </div>

            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            // Optionnel : Effet visuel lors du clic sur le bouton de recherche
            $('#clientHeaderSearchBtn').on('click', function() {
                alert("Moteur d'indexation Distrib237 : Recherche de fiches boissons...");
            });

        });

    </script>
    @stack('scripts')
</body>
</html>
