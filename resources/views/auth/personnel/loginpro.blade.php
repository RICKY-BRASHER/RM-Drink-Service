<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direction & Administration - Ets. Takougang & Cie</title>
    <!-- Liens Bootstrap 5.3.8 fournis -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root {
            --executive-blue: #1e3a8a; /* Bleu Roi Direction */
            --executive-gold: #b45309; /* Accent doré/ambre prestigieux */
            --slate-light: #f1f5f9;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(241, 245, 249, 1) 0%, rgba(226, 232, 240, 1) 90%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        /* Éléments géométriques en arrière-plan pour rendre la page captivante */
        .bg-blur-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(30, 58, 138, 0.08);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            filter: blur(80px);
            z-index: 1;
        }
        .bg-blur-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(180, 83, 9, 0.06);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            filter: blur(60px);
            z-index: 1;
        }
        .admin-box {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.1);
            max-width: 460px;
            width: 100%;
            padding: 3.5rem 2.5rem;
            position: relative;
            z-index: 10;
        }
        @media (max-width: 576px) {
            .admin-box {
                padding: 2.5rem 1.5rem;
                margin: 1rem;
            }
        }
        .panel-indicator {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--executive-blue);
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--executive-blue);
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .form-label {
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
        }
        .form-control-admin {
            border-color: #cbd5e1;
            padding: 12px 14px;
            font-size: 0.95rem;
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
        }
        .form-control-admin:focus {
            border-color: var(--executive-blue);
            box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.1);
        }
        .btn-executive {
            background: linear-gradient(135deg, var(--executive-blue), #1d4ed8);
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 10px;
            border: none;
            transition: all 0.2s ease;
            letter-spacing: 0.5px;
        }
        .btn-executive:hover {
            background: linear-gradient(135deg, #1d4ed8, var(--executive-blue));
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -4px rgba(30, 58, 138, 0.3);
        }
        .security-footer {
            font-size: 0.75rem;
            color: #64748b;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>
<body>

<!-- Effets visuels flous en arrière-plan -->
<div class="bg-blur-1"></div>
<div class="bg-blur-2"></div>

<div class="container d-flex justify-content-center align-items-center">
    <div class="admin-box">
        
        <!-- Indicateur de zone d'administration -->
        <div class="text-center mb-4">
            <div class="panel-indicator mb-3">
                <i class="bi bi-person-workspace text-primary"></i>
                <span>AUTHENTIFICATION ADMINISTRATIVE</span>
            </div>
            <h3 class="fw-bold text-primary mt-2" style="letter-spacing: -0.5px;">Ets. Takougang & Cie</h3>
            <p class="text-muted small">Veuillez valider vos accès pour gérer le réseau de dépôts.</p>
        </div>

        <!-- Formulaire professionnel -->
        <form id="adminLoginForm"  method="POST">
            @csrf
            
            <!-- Email Professionnel -->
            <div class="mb-3">
                <label class="form-label mb-2">Email Professionnel</label>
                <div class="input-group">
                    <span class="input-group-text">👤</span>
                    <input type="email" class="form-control form-control-admin" placeholder="direction@entreprise.com" name="email_or_phone">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Mot de passe clé -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Code Secret / Mot de passe</label>
                    <a href="#" class="small text-decoration-none text-muted" style="font-size: 0.8rem;">Clé oubliée ?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text">🔑</span>
                    <input type="password" class="form-control form-control-admin" placeholder="••••••••" name="password" >
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Option Mémorisation -->
            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="secureSession" name="remember" value="1">
                    <label class="form-check-label small text-secondary fw-medium" for="secureSession">Se souvenir de ce poste administratif</label>
                </div>
            </div>

            <!-- Bouton d'entrée principal avec texte et spinner dédiés -->
            <button type="submit" id="adminLoginButton" class="btn btn-executive w-100 shadow-sm mb-4 d-flex align-items-center justify-content-center gap-2">
                <span id="admin_btnText">Ouvrir la session globale</span>
                <div id="admin_btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></div>
            </button>
        </form>


        <!-- Message de vigilance réglementaire (Renforce le côté lieu administratif officiel) -->
        <div class="security-footer text-center">
            <span class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-square-fill text-warning me-1"></i> Vigilance Sécurité</span>
            Accès réservé. Les restrictions d'horaires d'ouverture par dépôt s'appliquent automatiquement à cette session.
        </div>

    </div>
</div>

<!-- Script Bootstrap 5.3.8 fourni -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
    
        $('#adminLoginForm').on('submit', function(e) {
            e.preventDefault(); // Bloque le rechargement brut de la page

            var $form = $(this);
            var $submitBtn = $('#adminLoginButton');
            var $btnText = $('#admin_btnText');
            var $btnSpinner = $('#admin_btnSpinner');

            // 1. Nettoyage des états d'erreur précédents
            $form.find('.form-control').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');

            // 2. Lancement visuel de l'animation de sécurité (UX)
            $submitBtn.prop('disabled', true);
            $btnText.text('Certifications des clés en cours...');
            $btnSpinner.removeClass('d-none');

            var formData = new FormData($form[0]);

            // 3. Temporisation réglementaire d'analyse de 1.5 seconde
            setTimeout(function() {
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success && response.redirect) {
                            // Redirection immédiate vers l'espace attribué (Admin ou Secrétariat)
                            window.location.replace(response.redirect);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Restauration du bouton de contrôle en cas d'erreur
                        $submitBtn.prop('disabled', false);
                        $btnText.text('Ouvrir la session globale');
                        $btnSpinner.addClass('d-none');

                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            
                            // Injection ciblée des messages d'erreur sous les inputs
                            $.each(errors, function(key, messages) {
                                var $input = $form.find('[name="' + key + '"]');
                                $input.addClass('is-invalid');
                                $input.closest('.mb-3, .mb-4').find('.invalid-feedback').text(messages[0]).show();
                            });
                        } else if (xhr.status === 403) {
                            // Cas spécifique de refus d'autorisation (Ex: un client qui essaie de forcer l'accès)
                            var $input = $form.find('[name="email_or_phone"]');
                            $input.addClass('is-invalid');
                            $input.closest('.mb-3').find('.invalid-feedback').text(xhr.responseJSON.message).show();
                        } else {
                            console.error("Crash système AJAX:", status, error);
                            alert("Une erreur technique interne est survenue.");
                        }
                    }
                });
            }, 1000); // 1500 millisecondes d'attente
        });

    });

</script>

</body>
</html>
