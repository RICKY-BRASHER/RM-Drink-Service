<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#183c2b">
    <title>Connexion équipe — Distrib237</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    @vite(['resources/css/showcase.css'])
</head>
<body class="login-page">
    <div class="login-page__glow" aria-hidden="true"></div>

    @if(session('toast_error'))
        <div class="login-toast" role="alert">
            <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
            <span>{{ session('toast_error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="login-toast" role="status">
            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <main class="login-shell">
        <section class="login-showcase" aria-label="Bienvenue chez Distrib237">
            <div class="login-showcase__lines" aria-hidden="true"></div>
            <a class="login-showcase__brand" href="{{ route('LandingPage') }}">
                <span class="brand-mark" aria-hidden="true"><i class="bi bi-droplet-half"></i></span>
                <span>Distrib<span>237</span></span>
            </a>

            <div class="login-showcase__copy">
                <span class="login-showcase__kicker"><i class="bi bi-stars"></i> L’énergie du service</span>
                <h1>Ensemble,<br><span>ça circule.</span></h1>
                <p>Retrouvez votre espace de travail et contribuez chaque jour à une distribution de boissons plus proche et mieux organisée.</p>
            </div>

            <div class="login-showcase__footer">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                Espace réservé à l’équipe Distrib237
            </div>
        </section>

        <section class="login-content" aria-labelledby="login-title">
            <a class="login-back" href="{{ route('LandingPage') }}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Retour au site
            </a>

            <header class="login-heading">
                <span class="login-heading__eyebrow">Connexion sécurisée</span>
                <h2 id="login-title">Content de vous revoir.</h2>
                <p>Connectez-vous pour accéder à votre espace professionnel.</p>
            </header>

            <form id="adminLoginForm" action="{{ route('login.pro.submit') }}" method="POST">
                @csrf
                <div class="login-alert" id="loginFeedback" role="alert" aria-live="polite"></div>

                <div class="login-field">
                    <label class="login-label" for="loginIdentifier">Email ou numéro de téléphone</label>
                    <div class="login-input-wrap">
                        <i class="bi bi-person" aria-hidden="true"></i>
                        <input
                            class="login-input"
                            id="loginIdentifier"
                            type="text"
                            name="email_or_phone"
                            value="{{ old('email_or_phone') }}"
                            placeholder="nom@entreprise.com ou 6XX XXX XXX"
                            autocomplete="username"
                            aria-describedby="identifierError"
                            required
                        >
                    </div>
                    <div class="login-error" id="identifierError"></div>
                </div>

                <div class="login-field">
                    <label class="login-label" for="loginPassword">Mot de passe</label>
                    <div class="login-input-wrap login-input-wrap--password">
                        <i class="bi bi-lock" aria-hidden="true"></i>
                        <input
                            class="login-input"
                            id="loginPassword"
                            type="password"
                            name="password"
                            placeholder="Saisissez votre mot de passe"
                            autocomplete="current-password"
                            aria-describedby="passwordError"
                            required
                        >
                        <button class="login-password-toggle" type="button" aria-label="Afficher le mot de passe" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="login-error" id="passwordError"></div>
                </div>

                <div class="login-options">
                    <label class="login-remember" for="secureSession">
                        <input type="checkbox" id="secureSession" name="remember" value="1">
                        <span>Se souvenir de moi</span>
                    </label>
                    <span class="login-remember">Accès réservé au personnel</span>
                </div>

                <button type="submit" id="adminLoginButton" class="login-submit">
                    <span id="admin_btnText">Ouvrir mon espace</span>
                    <span id="admin_btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-label="Connexion en cours"></span>
                    <i class="bi bi-arrow-right" id="admin_btnArrow" aria-hidden="true"></i>
                </button>
            </form>

            <div class="login-security">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <span>Vos accès professionnels sont personnels. Pensez à vous déconnecter après votre session.</span>
            </div>
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <script>
        $(function() {
            const $form = $('#adminLoginForm');
            const $submitButton = $('#adminLoginButton');
            const $buttonText = $('#admin_btnText');
            const $spinner = $('#admin_btnSpinner');
            const $arrow = $('#admin_btnArrow');
            const $feedback = $('#loginFeedback');

            function clearErrors() {
                $form.find('.login-input').removeClass('is-invalid').removeAttr('aria-invalid');
                $form.find('.login-error').text('').hide();
                $feedback.text('').hide();
            }

            function showFieldError(name, message) {
                const $input = $form.find('[name="' + name + '"]');
                if (!$input.length) {
                    return false;
                }

                $input.addClass('is-invalid').attr('aria-invalid', 'true');
                $input.closest('.login-field').find('.login-error').text(message).show();
                return true;
            }

            $form.on('input', '.login-input', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).closest('.login-field').find('.login-error').text('').hide();
                $feedback.text('').hide();
            });

            $('.login-password-toggle').on('click', function() {
                const isVisible = $('#loginPassword').attr('type') === 'text';
                $('#loginPassword').attr('type', isVisible ? 'password' : 'text');
                $(this)
                    .attr('aria-pressed', String(!isVisible))
                    .attr('aria-label', isVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe')
                    .find('i')
                    .toggleClass('bi-eye', isVisible)
                    .toggleClass('bi-eye-slash', !isVisible);
            });

            $form.on('submit', function(event) {
                event.preventDefault();
                clearErrors();
                $submitButton.prop('disabled', true);
                $buttonText.text('Connexion en cours…');
                $spinner.removeClass('d-none');
                $arrow.addClass('d-none');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData($form[0]),
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success && response.redirect) {
                            window.location.replace(response.redirect);
                            return;
                        }

                        $feedback.text('La connexion n’a pas abouti. Veuillez réessayer.').show();
                        $submitButton.prop('disabled', false);
                        $buttonText.text('Ouvrir mon espace');
                        $spinner.addClass('d-none');
                        $arrow.removeClass('d-none');
                    },
                    error: function(xhr, status, error) {
                        $submitButton.prop('disabled', false);
                        $buttonText.text('Ouvrir mon espace');
                        $spinner.addClass('d-none');
                        $arrow.removeClass('d-none');

                        const response = xhr.responseJSON || {};
                        if (xhr.status === 422 && response.errors) {
                            let firstInvalidField = null;
                            Object.keys(response.errors).forEach(function(key) {
                                if (showFieldError(key, response.errors[key][0]) && !firstInvalidField) {
                                    firstInvalidField = $form.find('[name="' + key + '"]')[0];
                                }
                            });

                            if (firstInvalidField) {
                                firstInvalidField.focus();
                            } else {
                                $feedback.text('Certains renseignements sont invalides. Vérifiez le formulaire.').show();
                            }
                            return;
                        }

                        if (xhr.status === 403 && response.message) {
                            if (!showFieldError('email_or_phone', response.message)) {
                                $feedback.text(response.message).show();
                            }
                            $form.find('[name="email_or_phone"]').trigger('focus');
                            return;
                        }

                        if (xhr.status === 419) {
                            $feedback.text('Votre session a expiré. Actualisez la page puis réessayez.').show();
                            return;
                        }

                        if (xhr.status === 429) {
                            $feedback.text('Trop de tentatives. Veuillez patienter un moment avant de réessayer.').show();
                            return;
                        }

                        console.error('Erreur de connexion Distrib237:', status, error);
                        $feedback.text('Une erreur technique empêche la connexion. Veuillez réessayer.').show();
                    }
                });
            });
        });
    </script>
</body>
</html>
