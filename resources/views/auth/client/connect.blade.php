<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Client - Login</title>
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-color: #4f46e5; /* Le violet/indigo de la charte */
            --dark-navy: #0f172a;
            --soft-bg: #9f9bc569;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: var(--soft-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
        }
        .auth-side-banner {
            background: linear-gradient(135deg, var(--primary-color), #312e81);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 20px;
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }
        .btn-auth {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .btn-auth:hover {
            background-color: #4338ca;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
<!-- Conteneur des Toasts (Fixé en haut à droite) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="registerSuccessToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>
                    <strong class="d-block">Inscription validée !</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<div class="container p-3 d-flex justify-content-center">
    <div class="auth-container row g-0">
        
        <!-- PANNEAU GAUCHE : BANNIÈRE MARKETING COMPTE ÉPARGNE -->
        <div class="col-lg-5 auth-side-banner d-none d-lg-flex">
            <div>
                <a class="text-white text-decoration-none fw-bold fs-4 d-block mb-5" href="/">
                    <i class="bi bi-bezier2 me-2"></i>Ets. Takougang
                </a>
                <h2 class="fw-extrabold display-6 lh-sm mb-3">Passez vos commandes en un clic.</h2>
                <p class="text-white-50 lead fs-6">
                    Rejoignez notre réseau de partenaires. Approvisionnez votre compte épargne pour des achats simplifiés, fluides et sans tracas de monnaie.
                </p>
            </div>
            <div>
                <div class="d-flex align-items-center gap-3 bg-white bg-opacity-10 p-3 rounded-4">
                    <i class="bi bi-shield-check text-success fs-3"></i>
                    <small class="text-white-50">Espace client sécurisé. Vos transactions et données d'épargne sont chiffrées de bout en bout.</small>
                </div>
            </div>
        </div>

        <!-- PANNEAU DROIT : FORMULAIRES FLUIDES -->
        <div class="col-lg-7 p-4 p-md-5">
            
            <!-- Sélecteur d'onglets (Connexion / Inscription) -->
            <ul class="nav nav-pills mb-5 bg-light p-2 rounded-3 gap-1" id="authTab" role="tablist">
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link w-100 active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login-content" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Connexion
                    </button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link w-100" id="register-tab" data-bs-toggle="pill" data-bs-target="#register-content" type="button" role="tab">
                        <i class="bi bi-person-plus me-2"></i>Inscription
                    </button>
                </li>
            </ul>

            <!-- Contenu des onglets -->
            <div class="tab-content" id="authTabContent">
                
                <!-- 1. FORMULAIRE DE CONNEXION -->
                <div class="tab-pane fade show active" id="login-content" role="tabpanel">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark">Ravi de vous revoir !</h4>
                        <p class="text-muted small">Connectez-vous pour accéder à vos commandes et votre solde.</p>
                    </div>
                    
                   <form id="clientLoginForm"  method="POST">
                        @csrf
                        
                        <!-- Champ Identifiant unique -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Adresse Email / Téléphone</label>
                            <input type="text" class="form-control" placeholder="nom@exemple.com ou 690408038" name="email_or_phone" >
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- Champ Mot de passe -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Mot de passe</label>
                                <a href="#" class="small text-decoration-none text-primary">Oublié ?</a>
                            </div>
                            <input type="password" name="mdp_connect" class="form-control" placeholder="••••••••" name="mdp_connect">
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <!-- Option Se souvenir de moi -->
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember_me" value="1">
                            <label class="form-check-label small text-muted" for="rememberMe">Se souvenir de moi</label>
                        </div>
                        
                        <!-- Bouton de validation avec identifiants uniques "login_" -->
                        <button type="submit" id="loginButton" class="btn btn-auth w-100 border-0 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <span id="login_btnText">Se connecter au portail</span>
                            <div id="login_btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></div>
                        </button>
                    </form>

                </div>

                <!-- 2. FORMULAIRE D'INSCRIPTION -->
                <div class="tab-pane fade" id="register-content" role="tabpanel">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark">Créer votre compte client</h4>
                        <p class="text-muted small">Devenez partenaire et commencez à planifier vos achats de boissons.</p>
                    </div>
                    
                    <form id="registerForm">
                        @csrf
                        
                        <!-- Champ Nom -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-secondary">Nom complet / Nom de votre établissement</label>
                                <input type="text" class="form-control @error('nom_u') is-invalid @enderror" name="nom_u" value="{{ old('nom_u') }}" placeholder="Ex: Bar de l'Avenir" >
                                <div class="invalid-feedback id-error-nom_u"></div>
                            </div>
                        </div>

                        <!-- Champ Type de Client -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary d-block mb-2">Vous êtes un client de type :</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="p-2 border rounded-3 bg-light text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input mb-2" type="radio" name="type_u" value="particulier" id="typeParticulier" {{ old('type_u', 'particulier') == 'particulier' ? 'checked' : '' }} >
                                        <label class="form-check-label small fw-bold text-secondary" for="typeParticulier"><i class="bi bi-person me-1"></i>Particulier</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded-3 bg-light text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input mb-2" type="radio" name="type_u" value="Depot" id="typeDepot" {{ old('type_u') == 'Depot' ? 'checked' : '' }} >
                                        <label class="form-check-label small fw-bold text-secondary" for="typeDepot"><i class="bi bi-shop me-1"></i>Dépôt</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded-3 bg-light text-center h-100 d-flex flex-column align-items-center justify-content-center">
                                        <input class="form-check-input mb-2" type="radio" name="type_u" value="tricycle" id="typeTricycle" {{ old('type_u') == 'tricycle' ? 'checked' : '' }} >
                                        <label class="form-check-label small fw-bold text-secondary" for="typeTricycle"><i class="bi bi-truck me-1"></i>Tricycle</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Champ Dépôt (Sélecteur dynamique) -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Choisissez votre dépôt</label>
                            <select class="form-select @error('depot_id') is-invalid @enderror" name="depot_id">
                                <option value="">-- Sélectionnez un dépôt --</option>
                                @foreach($depot as $d)
                                    <option value="{{ $d->id }}" {{ old('depot_id') == $d->id ? 'selected' : '' }}>{{ $d->nom_depot }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback id-error-depot_id"></div>
                        </div>

                        <!-- Champ Téléphone -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Numéro de Téléphone (Mobile Money)</label>
                            <input type="text" class="form-control @error('tel_u') is-invalid @enderror" name="tel_u" id="input_tel_u" value="{{ old('tel_u') }}" placeholder="Ex: 690408038 ou 237690408038" maxlength="12">
                            <div class="invalid-feedback id-error-tel_u"></div>
                        </div>

                        <!-- Champ Email -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Adresse mail</label>
                            <input type="email" class="form-control @error('email_u') is-invalid @enderror" name="email_u" value="{{ old('email_u') }}" placeholder="Ex: takou@gmail.com" >
                            <div class="invalid-feedback id-error-email_u"></div>
                        </div>

                        <!-- Champ Localisation -->
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Adresse/Localisation</label>
                            <input type="text" class="form-control @error('localisation_u') is-invalid @enderror" name="localisation_u" value="{{ old('localisation_u') }}" placeholder="Ex: Douala" >
                            <div class="invalid-feedback id-error-localisation_u"></div>
                        </div>

                        <!-- Champs Mots de passe -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Mot de passe</label>
                                <input type="password" class="form-control @error('mdp_u') is-invalid @enderror" name="mdp_u" placeholder="••••••••" >
                                <div class="invalid-feedback id-error-mdp_u"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Confirmer</label>
                                <input type="password" class="form-control @error('confirmer_mdp_u') is-invalid @enderror" name="confirmer_mdp_u" placeholder="••••••••" >
                                <div class="invalid-feedback id-error-confirmer_mdp_u"></div>
                            </div>
                        </div>

                        <!-- Bouton de validation dynamique -->
                        <button type="submit" id="submitBtn" class="btn btn-auth w-100 border-0 shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <span id="btnText">Créer mon compte</span>
                            <div id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </button>
                    </form>

                </div>


            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
        // Détecte si la session contient un message de succès après la redirection
        @if(session('success'))// Si le message de succès est présent, on affiche le toast
            var toastEl = document.getElementById('registerSuccessToast');
            var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
            toast.show();
        @endif
        // Utilisation de 'input' à la place de 'change' pour une vérification en temps réel pendant la saisie
        $(document).on('input', '#input_tel_u', function() {
            // 1. Nettoyage des espaces ou caractères parasites
            var tel = $(this).val().replace(/[\s\-\.\(\)]/g, ''); 
            var inputField = $(this);
            var errorDiv = $('.id-error-tel_u'); // Ciblage direct via votre classe personnalisée

            // 2. Expression régulière stricte pour le Cameroun :
            // ^6[0-9]{8}$    -> Commence par 6 et fait exactement 9 chiffres
            // ^2376[0-9]{8}$ -> Commence par 2376 et fait exactement 12 chiffres
            var regexCameroon = /^(6[0-9]{8}|2376[0-9]{8})$/;

            // 3. Condition de validation
            if (tel !== '' && !regexCameroon.test(tel)) {
                inputField.addClass('is-invalid').removeClass('is-valid');
                errorDiv.text('Le numéro doit contenir 9 chiffres commençant par 6, ou 12 chiffres commençant par 237.');
                
                // Bloque le bouton de soumission du formulaire pour éviter l'envoi
                inputField.closest('form').find('button[type="submit"]').prop('disabled', true);
            } else {
                // Le numéro est correct ou le champ est en train d'être vidé
                inputField.removeClass('is-invalid');
                if (tel !== '') {
                    inputField.addClass('is-valid');
                }
                errorDiv.text('');
                
                // Réactive le bouton de soumission
                inputField.closest('form').find('button[type="submit"]').prop('disabled', false);
            }
        });

        // Il est préférable de cibler l'événement 'submit' du formulaire plutôt que le 'click' du bouton
        $('#registerForm').on('submit', function(e){
            e.preventDefault(); // Empêche le rechargement classique immédiat

            var $form = $(this); // Récupère le formulaire actuel
            var $btnText = $('#btnText');
            var $btnSpinner = $('#btnSpinner');
            var $submitBtn = $('#submitBtn');

            // 1. Nettoyage des anciennes erreurs visuelles avant chaque tentative
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            // 2. Activation de l'animation visuelle de chargement (UX)
            $submitBtn.prop('disabled', true); // Bloque le bouton pour éviter les doubles clics
            $btnText.text('Validation en cours...'); // Change le texte du bouton
            $btnSpinner.removeClass('d-none'); // Affiche le cercle de chargement

            var formData = new FormData($form[0]);

            // 3. Lancement du délai obligatoire de 1.5 secondes (1500 millisecondes)
            setTimeout(function() {
                $.ajax({
                    url: "{{ route('register.client') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if(response.success && response.redirect) {
                            // Redirection fluide et définitive en cas de succès
                            window.location.replace(response.redirect);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Réactivation du bouton en cas d'erreur pour permettre une correction
                        $submitBtn.prop('disabled', false);
                        $btnText.text('Créer mon compte');
                        $btnSpinner.addClass('d-none');

                        // 4. Traitement et affichage des erreurs de validation (Code 422 de Laravel)
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            
                            // On parcourt chaque champ en erreur renvoyé par le Validateur Laravel
                            $.each(errors, function(key, messages) {
                                // Trouve le champ par son attribut 'name' ou 'id' et lui ajoute la bordure rouge
                                $('[name="' + key + '"]').addClass('is-invalid');
                                
                                // Injecte le message d'erreur dans la div 'invalid-feedback' qui suit immédiatement le champ
                                $('.id-error-' + key).text(messages[0]);
                            });
                        } else {
                            console.error("Erreur technique AJAX:", status, error);
                            alert("Une erreur technique est survenue sur le serveur.");
                        }
                    }
                });
            }, 1000); // 1500 ms = 1.5 secondes d'attente visuelle
            
        });
        $('#clientLoginForm').on('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement classique immédiat

            var $form = $(this);
            var $submitBtn = $('#loginButton');
            var $btnText = $('#login_btnText');      // ID unique Connexion
            var $btnSpinner = $('#login_btnSpinner');  // ID unique Connexion

            // 1. Nettoyage des anciennes alertes visuelles sur ce formulaire uniquement
            $form.find('.form-control').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');

            // 2. Activation de l'animation de chargement
            $submitBtn.prop('disabled', true); 
            $btnText.text('Connexion en cours...'); 
            $btnSpinner.removeClass('d-none'); 

            var formData = new FormData($form[0]); 

            // 3. Lancement du délai visuel de 1.5 seconde
            setTimeout(function() {
                $.ajax({
                    url: "{{ route('login.client.submit') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success && response.redirect) {
                            window.location.replace(response.redirect);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Réactivation du bouton de connexion en cas d'échec
                        $submitBtn.prop('disabled', false);
                        $btnText.text('Se connecter au portail');
                        $btnSpinner.addClass('d-none');

                        // 4. Traitement des erreurs
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            
                            $.each(errors, function(key, messages) {
                                var $input = $form.find('[name="' + key + '"]');
                                $input.addClass('is-invalid');
                                $input.siblings('.invalid-feedback').text(messages[0]);// 
                            });
                        } else if (xhr.status === 401) { // 
                            var $input = $form.find('[name="email_or_phone"]');
                            $input.addClass('is-invalid');
                            $input.siblings('.invalid-feedback').text(xhr.responseJSON.message);
                        } else {
                            console.error("Erreur technique AJAX:", status, error);
                            alert("Une erreur technique est survenue sur le serveur.");
                        }
                    }
                });
            }, 1000); 
        });



    });
</script>


</body>
</html>
