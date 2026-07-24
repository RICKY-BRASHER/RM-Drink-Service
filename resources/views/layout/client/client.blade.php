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
            
            <!-- 🔔 COMPOSANT DROPDOWN DE LA CLOCHE DES MESSAGES CLIENT -->
            <div class="dropdown">
                <button class="btn icon-btn position-relative p-0 border-0 bg-transparent text-white" type="button" id="clientNotifBell" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <!-- 💡 Badge numérique dynamique branché sur les messages non lus du client -->
                    @if(count($notificationsMessages) > 0)
                        <span class="icon-badge position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger font-monospace" style="font-size: 0.62rem; padding: 3px 5px; min-width: 16px;">
                            {{ count($notificationsMessages) }}
                        </span>
                    @endif
                </button>
                
                <!-- Menu déroulant des messages formaté pour écran de smartphone -->
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3 rounded-4 mt-2" aria-labelledby="clientNotifBell" style="width: 310px; background-color: #111111; font-size: 0.85rem; border: 1px solid #1f2022 !important;">
                    <li class="dropdown-header fw-bold text-white-50 pb-2 mb-2 border-bottom border-secondary border-opacity-20 ps-0" style="font-size:0.75rem; letter-spacing:0.5px;">
                        🔔 MON FIL D'ACTUALITÉ
                    </li>
                    
                    <div style="max-height: 240px; overflow-y: auto;" id="clientNotifDropdownBody">
                        @forelse($notificationsMessages as $msg)
                            <li class="p-2.5 rounded-3 mb-2 border border-white border-opacity-5" style="background-color: #161719;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="pe-2">
                                        <strong class="{{ $msg->couleur_n }} text-uppercase d-block" style="font-size:0.75rem;">
                                            <i class="bi {{ $msg->icone_n }} me-1"></i> {{ $msg->titre_n }}
                                        </strong>
                                        <span class="text-white-50 small d-block mt-1" style="font-size:0.78rem; line-height:1.2;">{{ $msg->message_n }}</span>
                                        <small style="color: #71767b; font-size: 0.65rem;" class="font-monospace d-block mt-1">
                                            {{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}
                                        </small>
                                    </div>
                                    <!-- Clic sur la coche éteint le message en AJAX -->
                                    <button type="button" class="btn p-0 text-success dismiss-client-msg-btn" data-id="{{ $msg->id_notif }}" title="Marquer comme lu">
                                        <i class="bi bi-check2-circle fs-5"></i>
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-4 text-muted small">
                                <i class="bi bi-bell-slash display-6 d-block mb-1 opacity-20"></i>
                                Aucun nouveau message reçu.
                            </li>
                        @endforelse
                    </div>
                </ul>
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
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // ====================================================================
            // ✉️ EXTINCTION SÉCURISÉE DES MESSAGES DANS LA CLOCHE DU SMARTPHONE
            // ====================================================================
            $(document).on('click', '.dismiss-client-msg-btn', function(e) {
                e.stopPropagation(); // Empêche la fermeture brusque du volet dropdown mobile
                
                var button = $(this);
                var idNotif = button.data('id');

                $.ajax({
                    url: '/Distrib237/notification/marquer-lue/' + idNotif,
                    type: 'POST',
                    data: {
                        // Utilise le jeton CSRF présent dans les balises meta de l'application
                        _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            // Animation fluide de disparition de la bulle
                            button.closest('li').fadeOut(200, function() {
                                $(this).remove();
                                location.reload(); // Synchronise le badge de notification
                            });
                        }
                    }
                });
            });

            // Optionnel : Effet visuel lors du clic sur le bouton de recherche
            $('#clientHeaderSearchBtn').on('click', function() {
                alert("Moteur d'indexation Distrib237 : Recherche de fiches boissons...");
            });

        });

    </script>
    @stack('scripts')
</body>
</html>
