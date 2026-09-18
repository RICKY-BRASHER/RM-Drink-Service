<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depot Takou&Chris</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        :root {
            --primary-indigo: #4f46e5;
            --dark-navy: #0f172a;
            --light-bg: #185ba291;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--light-bg);
            color: #334155;
        }
        .navbar-brand fw-bold {
            color: var(--dark-navy);
        }
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.2;
            color: var(--dark-navy);
        }
        .feature-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        }
        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .footer-note {
            background: var(--dark-navy);
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- 1. BARRE DE NAVIGATION -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#">
                <i class="bi bi-bezier2 text-primary me-2"></i>Ets.<span class="text-primary"> DISTRIB 237</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#approche">Notre Approche</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#services">Services en ligne</a></li>
                    <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#epargne">Concept Épargne</a></li>
                </ul>
                <div class="d-flex gap-2">
                    
                    <a href="{{ route('login.pro') }}" class="btn btn-primary px-4 fw-semibold shadow-sm">Espace Personnel</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 2. SECTION EN-TÊTE (HERO) -->
    <header class="py-5 bg-white border-bottom">
        <div class="container py-4">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6 text-center text-lg-start">
                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill mb-3">Plateforme Intégrée sur Mesure</span>
                    <h1 class="hero-title mb-4">
                        La digitalisation au service de la distribution de boissons
                    </h1>
                    <p class="lead text-secondary mb-5">
                        Bienvenue sur l'écosystème numérique officiel des établissements de <strong>M. Takougang Dimitri & Mme Hélène Christine</strong>. Une solution unifiée pour la gestion de nos dépôts et le service client de proximité.
                    </p>
                    <div class="d-sm-flex justify-content-center justify-content-lg-start gap-3">
                        <a href="#approche" class="btn btn-primary btn-lg px-4 py-3 fw-bold shadow">Découvrir la solution</a>
                        <a href="#" class="btn btn-light btn-lg px-4 py-3 fw-bold border mt-2 mt-sm-0">Accéder aux services</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <!-- Aperçu logique d'un tableau de bord moderne structuré -->
                    <div class="p-4 bg-light rounded-4 border shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-bold m-0 text-dark"><i class="bi bi-building me-2"></i>Réseau Multi-Dépôts</h6>
                            <span class="badge bg-success">Actif</span>
                        </div>
                        <div class="bg-white p-3 rounded-3 border mb-3 shadow-sm">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-secondary text-sm">Dépôt Logpom</span>
                                <span class="text-success fw-bold">Opérationnel</span>
                            </div>
                            <small class="text-muted">Gestion des stocks & ventes directes synchronisées</small>
                        </div>
                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-secondary text-sm">E-Portefeuille & Épargne Client</span>
                                <span class="text-primary fw-bold">Sécurisé</span>
                            </div>
                            <small class="text-muted">Commandes fluides via le solde client prépayé</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- 3. SECTION LA DOUBLE APPROCHE -->
    <section id="approche" class="py-5 bg-light">
        <div class="container py-4">
            <div class="text-center max-w-2xl mx-auto mb-5">
                <h2 class="fw-bold text-dark mb-3">Une architecture双-face innovante</h2>
                <p class="text-secondary lead fs-6">Notre outil répond simultanément aux exigences internes de l'entreprise et aux besoins de flexibilité de notre clientèle.</p>
            </div>

            <div class="row g-4">
                <!-- Bloc Interne / Administration -->
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="icon-box bg-primary-subtle text-primary mb-3">
                            🔒 
                        </div>
                        <h4 class="fw-bold text-dark mb-3">1. Espace Administration & Dépôts</h4>
                        <p class="text-secondary mb-4">
                            Dédié aux administrateurs (M. Dimitri & Mme Christine) et aux équipes de terrain. Permet le pilotage global, le suivi minutieux des stocks par zone, la gestion comptable stricte et la restriction des accès selon les horaires d'ouverture pour contrer toute fraude.
                        </p>
                        <ul class="list-unstyled text-secondary ps-0 mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Supervision centralisée multi-sites</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Clôture de caisse et sécurité horaire</li>
                            <li><i class="bi bi-check2-circle text-success me-2"></i>Validation des rechargements d'épargne</li>
                        </ul>
                    </div>
                </div>

                <!-- Bloc Externe / Portail Client & Épargne -->
                <div class="col-md-6">
                    <div class="feature-card p-4 h-100">
                        <div class="icon-box bg-success-subtle text-success mb-3">
                            👥
                        </div>
                        <h4 class="fw-bold text-dark mb-3">2. Portail B2B / Clients & Épargne</h4>
                        <p class="text-secondary mb-4">
                            Dédié aux partenaires, bars et clients fidèles. Il permet de passer commande en ligne instantanément auprès du dépôt le plus proche et propose un système de compte épargne pour provisionner son budget de façon rationnelle.
                        </p>
                        <ul class="list-unstyled text-secondary ps-0 mb-0">
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Commandes 24h/24 sans déplacement</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i>Paiement en un clic via le compte épargne</li>
                            <li><i class="bi bi-check2-circle text-success me-2"></i>Application automatique des promotions</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER NOTE DE PROPRIÉTÉ -->
    <footer class="footer-note py-4 text-center">
        <div class="container">
            <p class="m-0 text-sm">
                &copy; 2026 Établissements Takougang & Christine. Projet d'application web de soutenance de fin de cycle.
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://jsdelivr.net"></script>
</body>
</html>
