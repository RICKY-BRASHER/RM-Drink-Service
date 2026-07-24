<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>R~M Drink Manager - Console de Direction</title>
    
    <!-- Liens Bootstrap 5.3.8 fournis -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.min.css" rel="stylesheet">
    @vite(['resources/css/depot.css'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body class="erp-body">
    <!-- Conteneur des Toasts (Fixé en haut à droite) -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="registerSuccessToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>
                        <strong class="d-block">Reussi !</strong>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <!-- BARRE LATÉRALE GAUCHE DISCRÈTE & SOMBRE (Comme sur l'image) -->
    <aside class="erp-sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-box-seam-fill text-danger me-2"></i>
            <div>
                <h1 class="m-0">DISTRIB237</h1>
                <span>MANAGER</span>
            </div>
        </div>

        <div class="sidebar-menu">
            @if(Auth::user()->role_u === 'admin')
                <a href="{{ route('accueil.admin') }}" class="menu-item {{ Route::is('accueil.admin') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span> <span>Accueil</span>
                </a>
                <a href="{{ route('controldepot') }}" class="menu-item {{ Route::is('controldepot') ? 'active' : '' }}">
                    <span class="nav-icon">🏢</span> <span>Mes depots</span>
                </a>
                <a href="{{ route('employe') }}" class="menu-item {{ Route::is('employe') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span> <span>Employés</span>
                </a>
            @elseif(Auth::user()->role_u === 'employe')
                <a href="{{ route('dashboard') }}" class="menu-item {{ Route::is('dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">🏠</span> <span>Accueil</span>
                </a>
            @endif
            <a href="{{ route('stock') }}" class="menu-item {{ Route::is('stock') ? 'active' : '' }}">
                <span class="nav-icon">🏭</span> <span>Stock</span>
            </a>
            @if(Auth::user()->role_u === 'employe')
            <a href="{{ route('produit') }}" class="menu-item {{ Route::is('produit') ? 'active' : '' }}">
                <span class="nav-icon">📦</span> <span>Produits</span>
            </a>
            
            <a href="{{ route('vente') }}" class="menu-item {{ Route::is('vente') ? 'active' : '' }}">
                <span class="nav-icon">💳</span> <span>Ventes</span>
            </a>
            @endif
            @if(Auth::user()->role_u === 'admin')
                <a href="{{ route('transaction') }}" class="menu-item {{ Route::is('transaction') ? 'active' : '' }}">
                    <span class="nav-icon">🪙</span> <span>Transaction Monetaire</span>
                </a>
            @elseif(Auth::user()->role_u === 'employe')
                <a href="{{ route('commandes') }}" class="menu-item {{ Route::is('commandes') ? 'active' : '' }}">
                    <span class="nav-icon">📥</span> <span>Commandes</span>
                </a>
            @endif
            <a href="{{ route('client') }}" class="menu-item {{ Route::is('client') ? 'active' : '' }}">
                <span class="nav-icon">👥</span> <span>Clients</span>
            </a>
            
            <a href="{{ route('historique') }}" class="menu-item {{ Route::is('historique') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> <span>Historique</span>
            </a>
        </div>


        <div class="sidebar-footer">
            <div class="user-meta">
                <strong class="text-white d-block small">{{ auth()->user()->nom_u ?? 'Takougang D.' }}</strong>
                <span class="text-primary text-uppercase font-monospace" style="font-size: 0.65rem;">{{ auth()->user()->role_u ?? 'Admin' }}</span>
            </div>
            <a href="{{ route('logout') }}" class="logout-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-power"></i>
            </a>
            <form id="logout-form" action="{{ route('deconnect') }}" method="POST" class="d-none">@csrf</form>
        </div>
    </aside>

    <!-- ZONE DE TRAVAIL DROITE (BARRE SUPÉRIEURE + CONTENU) -->
    <div class="erp-container">
        
        <!-- BARRE SUPÉRIEURE BLANCHE ÉPURÉE INTERACTIVE MULTI-FLUX -->
        <nav class="erp-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none shadow-sm" id="sidebarToggleBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="topbar-search">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" placeholder="Rechercher des données, des ventes, des boissons...">
                </div>
                
                {{-- ==================================================================== --}}
                {{-- 🚨 BLOC 1 : CLOCHE DES ALERTES LOGISTIQUES DE STOCKS CRITIQUES       --}}
                {{-- ==================================================================== --}}
                @if(Auth::user()->role_u !== 'client')
                    <div class="dropdown me-1">
                        <button class="btn btn-light border position-relative rounded-circle p-2" type="button" id="notifStockBell" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px;">
                            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                            @if(count($notificationsAlertes) > 0)
                                <!-- Compteur d'alertes de stocks critiques -->
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-white" style="padding: 4px 6px; font-size: 0.65rem;">
                                    {{ count($notificationsAlertes) }}
                                </span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 rounded-3" aria-labelledby="notifStockBell" style="width: 320px; font-size: 0.88rem;">
                            <li class="dropdown-header fw-bold text-danger border-bottom pb-2 mb-2"><i class="bi bi-exclamation-triangle-fill"></i> ALERTES SEUILS CRITIQUES</li>
                            
                            <div style="max-height: 250px; overflow-y: auto;">
                                @forelse($notificationsAlertes as $notif)
                                    @php
                                        $qteRestante = ($notif->quantite_restante == floor($notif->quantite_restante)) ? (int)$notif->quantite_restante : number_format($notif->quantite_restante, 1, ',', ' ');
                                    @endphp
                                    <li class="p-2 bg-danger bg-opacity-10 rounded-3 mb-2 border border-danger border-opacity-10">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="text-danger text-uppercase d-block" style="font-size:0.8rem;">{{ $notif->nom_p }}</strong>
                                                <small class="text-muted d-block">{{ $notif->format_p }} @if(isset($notif->nom_depot)) • 📍 {{ $notif->nom_depot }} @endif</small>
                                                <span class="text-dark small d-block mt-1">Stock restant : <b class="font-monospace text-danger">{{ $qteRestante }}</b> (Seuil: {{ $notif->seuil_limite }})</span>
                                            </div>
                                            <!-- Bouton d'extinction de l'alerte stock -->
                                            <button type="button" class="btn p-0 text-success dismiss-stock-btn" data-id="{{ $notif->id_alerte }}" title="Marquer comme lu">
                                                <i class="bi bi-check2-circle fs-5"></i>
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-center py-4 text-muted small">
                                        <i class="bi bi-shield-check display-6 text-success d-block mb-1"></i>
                                        Aucun seuil critique atteint. Stocks optimaux.
                                    </li>
                                @endforelse
                            </div>
                        </ul>
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-10 px-3 py-2 rounded-pill fw-bold small d-none d-sm-inline-block">
                    🏢 Dépôt : {{ session('depot_actif_nom', 'Logpom') }}
                </span>
                
                {{-- ==================================================================== --}}
                {{-- 📱 BLOC 2 : CLOCHE DES NOTIFICATIONS MESSAGES & COMMANDES EN LIGNE   --}}
                {{-- ==================================================================== --}}
                <div class="dropdown">
                    <!-- Remplacement du div simple par un bouton dropdown doté d'un compteur dynamique -->
                    <button class="btn btn-light border position-relative rounded-circle p-2 topbar-icon-btn-custom" type="button" id="notifMessageBell" data-bs-toggle="dropdown" aria-expanded="false" style="width: 40px; height: 40px; border-radius: 50%;">
                        <i class="bi bi-bell text-secondary"></i>
                        @if(count($notificationsMessages) > 0)
                            <!-- 💡 LE NOUVEAU COMPTEUR DE NOTIFICATIONS COMPACT REÇUES ET NON LUES -->
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-primary border border-white" style="padding: 4px 6px; font-size: 0.65rem;">
                                {{ count($notificationsMessages) }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 rounded-3" aria-labelledby="notifMessageBell" style="width: 320px; font-size: 0.88rem;">
                        <li class="dropdown-header fw-bold text-primary border-bottom pb-2 mb-2"><i class="bi bi-chat-left-text-fill"></i> MESSAGES & COMMANDES CLIENTS</li>
                        
                        <div style="max-height: 250px; overflow-y: auto;">
                            @forelse($notificationsMessages as $msg)
                                <li class="p-2.5 bg-light rounded-3 mb-2 border border-secondary border-opacity-10">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="pe-2">
                                            <strong class="{{ $msg->couleur_n }} text-uppercase d-block" style="font-size:0.78rem;">
                                                <i class="bi {{ $msg->icone_n }} me-1"></i> {{ $msg->titre_n }}
                                            </strong>
                                            <span class="text-dark small d-block mt-1" style="font-size:0.8rem; line-height:1.2;">{{ $msg->message_n }}</span>
                                            <small class="text-muted font-monospace mt-1 d-block" style="font-size:0.68rem;">{{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}</small>
                                        </div>
                                        <!-- Bouton d'extinction du message 
                                        <button type="button" class="btn p-0 text-success dismiss-msg-btn" data-id="{{ $msg->id_notif }}" title="Marquer comme lu">
                                            <i class="bi bi-check2-circle fs-5"></i>
                                        </button>-->
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-4 text-muted small">
                                    <i class="bi bi-bell-slash display-6 text-secondary opacity-30 d-block mb-1"></i>
                                    Aucun message ou commande en attente.
                                </li>
                            @endforelse
                        </div>
                    </ul>
                </div>
                
                <div class="topbar-icon-btn"><i class="bi bi-gear"></i></div>
                <div class="topbar-avatar">
                    <i class="bi bi-person-fill text-secondary"></i>
                </div>
            </div>
        </nav>


        <!-- ZONE D'INJECTION DYNAMIQUE DES PAGES -->
        <main class="erp-main">
            @yield('content')
        </main>

    </div>

    <!-- Script global d'interaction de la Navbar Mobile -->
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

            @if(session('success'))// Si le message de succès est présent, on affiche le toast
                var toastEl = document.getElementById('registerSuccessToast');
                var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
                toast.show();
            @endif
            // Gère le clic sur le bouton menu Hamburger sur mobile
            $('#sidebarToggleBtn').on('click', function(e) {
                e.stopPropagation();
                $('.erp-sidebar').toggleClass('open');
            });
            // Ferme automatiquement la barre latérale si on clique n'importe où en dehors sur l'écran mobile
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.erp-sidebar').length) {
                    $('.erp-sidebar').removeClass('open');
                }
            });

            // 1. Marquer comme lue une alerte de stock technique
            $(document).on('click', '.dismiss-stock-btn', function(e) {
                e.stopPropagation();
                var button = $(this);
                var id = button.data('id');

                $.ajax({
                    url: '/Distrib237/caissier/marquer-alerte-lue/' + id,
                    type: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() },
                    success: function(response) {
                        if (response.success) {
                            button.closest('li').fadeOut(200, function() { $(this).remove(); location.reload(); });
                        }
                    }
                });
            });

            // 2. Marquer comme lu un message système ou de commande
            $(document).on('click', '.dismiss-msg-btn', function(e) {
                e.stopPropagation();
                var button = $(this);
                var id = button.data('id');

                $.ajax({
                    url: '/Distrib237/notification/marquer-lue/' + id,
                    type: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() },
                    success: function(response) {
                        if (response.success) {
                            button.closest('li').fadeOut(200, function() { $(this).remove(); location.reload(); });
                        }
                    }
                });
            });


        });

    </script>
    @Stack('scripts')
</body>
</html>
