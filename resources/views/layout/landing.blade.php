<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f6f7f1">
    <meta name="description" content="Distrib237, votre partenaire de proximité pour la distribution de boissons au Cameroun.">
    <title>Distrib237 — La boisson, en mouvement</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @vite(['resources/css/showcase.css'])
</head>
<body class="showcase-page">
    <nav class="site-nav" aria-label="Navigation principale">
        <div class="site-nav__inner">
            <a class="brand-lockup" href="{{ route('LandingPage') }}" aria-label="Distrib237, accueil">
                <span class="brand-mark" aria-hidden="true"><i class="bi bi-droplet-half"></i></span>
                <span class="brand-name">Distrib<span>237</span></span>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-menu" aria-label="Ouvrir le menu">
                <span></span><span></span>
            </button>

            <div class="site-nav__menu" id="site-menu">
                <a href="#depot">Notre dépôt</a>
                <a href="#expertise">Notre expertise</a>
                <a href="#engagement">Notre engagement</a>
                <a href="#contact">Nous contacter</a>
                <a class="nav-login" href="{{ route('login.pro') }}">
                    Espace personnel <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <div class="hero-grain" aria-hidden="true"></div>
            <div class="hero-inner">
                <div class="hero-copy">
                    <div class="eyebrow reveal">
                        <span class="eyebrow__dot"></span>
                        Ancrés au Cameroun. Toujours en mouvement.
                    </div>
                    <h1 class="hero-title reveal">
                        La boisson,<br>
                        <span class="hero-title__accent">en mouvement.</span>
                    </h1>
                    <p class="hero-description reveal">
                        Du dépôt jusqu’à votre comptoir, Distrib237 facilite la distribution de boissons avec un service de proximité, attentif et bien organisé.
                    </p>
                    <div class="hero-actions reveal">
                        <a class="button button--primary" href="#expertise">
                            Découvrir Distrib237 <i class="bi bi-arrow-down-right"></i>
                        </a>
                        <a class="button button--quiet" href="{{ route('login.pro') }}">
                            Accès équipe <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                    <div class="hero-footnote reveal">
                        <span class="flag-mark" aria-hidden="true"><i></i><i></i><i></i></span>
                        <span>Une énergie locale, au service de vos boissons.</span>
                    </div>
                </div>

                <div class="hero-art reveal">
                    <figure class="hero-photo-frame">
                        <img
                            class="hero-photo"
                            src="{{ asset('img/depot1.jpg') }}"
                            alt="Dépôt de boissons Distrib237 avec des casiers en devanture"
                            fetchpriority="high"
                        >
                        <span class="hero-photo-shade"></span>
                        <figcaption class="hero-photo-caption">
                            <span class="hero-photo-caption__icon"><i class="bi bi-box-seam"></i></span>
                            <span><strong>Du dépôt à votre comptoir</strong><small>Votre partenaire boisson au quotidien</small></span>
                        </figcaption>
                    </figure>
                    <div class="hero-photo-note">
                        <span class="hero-photo-note__icon"><i class="bi bi-geo-alt-fill"></i></span>
                        <span><strong>Distrib237</strong><small>Proche de vos besoins</small></span>
                    </div>
                    <span class="hero-photo-index" aria-hidden="true">DISTRIB237 <i></i> EN MOUVEMENT</span>
                </div>
            </div>
            <a class="scroll-cue" href="#expertise" aria-label="Défiler vers notre expertise">
                <span>Défiler pour découvrir</span><i class="bi bi-arrow-down"></i>
            </a>
        </section>

        <section class="ticker" aria-label="Distrib237, votre partenaire boisson">
            <div class="ticker__track" aria-hidden="true">
                <span>La proximité en plus</span><i class="bi bi-asterisk"></i>
                <span>Le service en mouvement</span><i class="bi bi-asterisk"></i>
                <span>La proximité en plus</span><i class="bi bi-asterisk"></i>
                <span>Le service en mouvement</span><i class="bi bi-asterisk"></i>
                <span>La proximité en plus</span><i class="bi bi-asterisk"></i>
                <span>Le service en mouvement</span><i class="bi bi-asterisk"></i>
            </div>
        </section>

        <section class="depot-section section-wrap" id="depot">
            <div class="depot-gallery reveal">
                <figure class="depot-photo depot-photo--main">
                    <img
                        src="{{ asset('img/depot2.jpg') }}"
                        alt="Casiers de boissons rangés dans le dépôt"
                        loading="lazy"
                    >
                    <figcaption class="depot-photo__label">
                        <span class="depot-photo__label-icon"><i class="bi bi-boxes"></i></span>
                        <span><strong>Le dépôt</strong><small>Le point de départ du service</small></span>
                    </figcaption>
                    <span class="depot-photo__number">01<span> / LE STOCK</span></span>
                </figure>
                <figure class="depot-photo depot-photo--detail">
                    <img
                        src="{{ asset('img/depot3.jpg') }}"
                        alt="Rayon de boissons présenté en magasin"
                        loading="lazy"
                    >
                    <figcaption class="depot-photo__badge"><i class="bi bi-cup-straw"></i> Un large choix</figcaption>
                </figure>
            </div>
            <div class="depot-copy reveal">
                <span class="section-kicker">Au cœur de la distribution</span>
                <h2>Tout commence<br>au <span>dépôt.</span></h2>
                <p>Un approvisionnement bien suivi, des boissons disponibles et une équipe à l’écoute : chaque détail compte pour servir les clients et les professionnels.</p>
                <div class="depot-points">
                    <div><i class="bi bi-check2"></i><span>Des stocks organisés et suivis</span></div>
                    <div><i class="bi bi-check2"></i><span>Un service de proximité pour vos commandes</span></div>
                    <div><i class="bi bi-check2"></i><span>Une équipe mobilisée au quotidien</span></div>
                </div>
                <a class="depot-link" href="{{ route('login.pro') }}">L’espace de notre équipe <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </section>

        <section class="expertise-section section-wrap" id="expertise">
            <div class="section-heading reveal">
                <div>
                    <span class="section-kicker">À vos côtés, chaque jour</span>
                    <h2>La bonne boisson.<br><span>Au bon endroit.</span></h2>
                </div>
                <p>Du choix des boissons à leur mise à disposition, nous faisons de la proximité et du service les priorités de chaque étape.</p>
            </div>

            <div class="expertise-grid">
                <article class="expertise-card expertise-card--dark reveal">
                    <div class="expertise-card__top"><span>01 / APPROVISIONNER</span><i class="bi bi-box-seam"></i></div>
                    <div>
                        <h3>Un dépôt bien organisé</h3>
                        <p>Une gestion attentive des entrées et des stocks pour préparer les commandes sereinement.</p>
                    </div>
                    <span class="card-decoration card-decoration--bottle" aria-hidden="true"><i class="bi bi-boxes"></i></span>
                </article>
                <article class="expertise-card expertise-card--lime reveal">
                    <div class="expertise-card__top"><span>02 / CONSEILLER</span><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <h3>Un service de proximité</h3>
                        <p>Une équipe disponible pour accompagner les clients et les professionnels au quotidien.</p>
                    </div>
                    <span class="card-decoration card-decoration--route" aria-hidden="true"><i class="bi bi-signpost-split"></i></span>
                </article>
                <article class="expertise-card expertise-card--orange reveal">
                    <div class="expertise-card__top"><span>03 / DISTRIBUER</span><i class="bi bi-lightning-charge"></i></div>
                    <div>
                        <h3>Des commandes mieux suivies</h3>
                        <p>Des outils simples pour coordonner les ventes, les commandes et les équipes du dépôt.</p>
                    </div>
                    <span class="card-decoration card-decoration--spark" aria-hidden="true"><i class="bi bi-stars"></i></span>
                </article>
            </div>
        </section>

        <section class="promise-section" id="engagement">
            <div class="promise-inner section-wrap">
                <div class="promise-stamp reveal"><i class="bi bi-heart-fill"></i><span>Fait avec<br>le cœur</span></div>
                <div class="promise-copy reveal">
                    <span class="section-kicker section-kicker--light">Notre engagement</span>
                    <h2>Le sens du service.<br><span>L’énergie du pays.</span></h2>
                    <p>Distrib237 porte une ambition simple : rapprocher les boissons de celles et ceux qui les partagent, avec une expérience humaine et une organisation fiable.</p>
                    <a href="{{ route('login.pro') }}" class="promise-link">Rejoindre l’équipe <i class="bi bi-arrow-up-right"></i></a>
                </div>
                <div class="promise-orb promise-orb--one" aria-hidden="true"></div>
                <div class="promise-orb promise-orb--two" aria-hidden="true"></div>
                <div class="promise-lines" aria-hidden="true"></div>
            </div>
        </section>

        <section class="contact-section section-wrap" id="contact">
            <div class="contact-card reveal">
                <div>
                    <span class="section-kicker">On avance ensemble ?</span>
                    <h2>Votre prochain moment<br>commence ici.</h2>
                </div>
                <a class="button button--light" href="{{ route('login.pro') }}">
                    Accéder à Distrib237 <i class="bi bi-arrow-up-right"></i>
                </a>
                <span class="contact-card__watermark" aria-hidden="true"><i class="bi bi-droplet-half"></i></span>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <a class="brand-lockup brand-lockup--footer" href="{{ route('LandingPage') }}">
            <span class="brand-mark" aria-hidden="true"><i class="bi bi-droplet-half"></i></span>
            <span class="brand-name">Distrib<span>237</span></span>
        </a>
        <p>Distribution de boissons, avec l’esprit du Cameroun.</p>
        <div class="site-footer__credits">
            <span>Images du dépôt et des produits Distrib237.</span>
            <span class="site-footer__copyright">&copy; {{ date('Y') }} Distrib237. Tous droits réservés.</span>
        </div>
    </footer>

    <script>
        document.documentElement.classList.add('has-motion');

        const revealElements = document.querySelectorAll('.reveal');

        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.14 });

            revealElements.forEach((element, index) => {
                element.style.setProperty('--reveal-delay', `${(index % 4) * 90}ms`);
                revealObserver.observe(element);
            });
        } else {
            revealElements.forEach((element) => element.classList.add('is-visible'));
        }

        const navToggle = document.querySelector('.nav-toggle');
        const navMenu = document.querySelector('.site-nav__menu');

        navToggle.addEventListener('click', () => {
            const isOpen = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', String(!isOpen));
            navToggle.setAttribute('aria-label', isOpen ? 'Ouvrir le menu' : 'Fermer le menu');
            navMenu.classList.toggle('is-open', !isOpen);
        });

        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Ouvrir le menu');
                navMenu.classList.remove('is-open');
            });
        });
    </script>
</body>
</html>
