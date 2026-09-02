@extends('layout.client.client')

@section('client_content')


<div class="container py-3">

    <!-- EN-TÊTE PAGE PROFIL -->
    <div class="mb-4 ps-1">
        <span class="text-secondary-custom small fw-bold text-uppercase tracking-wider" style="font-size: 0.65rem;">Mon espace</span>
        <h5 class="fw-bold m-0 text-white" style="letter-spacing: -0.3px;">Mon Profil & Sécurité</h5>
        <small style="color: #71767b; font-size: 0.75rem;">Gérez vos identifiants d'accès et vérifiez vos données</small>
    </div>

    <!-- 📸 BLOC AVATAR DYNAMIQUE AVEC VISIONNEUSE SMARTPHONE -->
    <div class="profile-header-card mb-4 text-center">
        <div class="d-flex flex-column align-items-center justify-content-center py-2">
            
            <form id="avatarUploadForm" enctype="multipart/form-data" class="d-none">
                <input type="file" id="avatarFileInput" name="avatar" accept="image/*">
            </form>

            <div class="profile-avatar-wrapper mb-3">
                <!-- 💡 CLIC ICI OUVRIRA LA PHOTO EN GRAND (Classe et style ajoutés) -->
                <div class="avatar-display clickable-avatar-trigger" id="avatarContainer" style="overflow: hidden; width: 100%; height: 100%; border-radius: 50%; cursor: pointer;" title="Voir en grand">
                    @if(auth()->user()->avatar_u)
                        <img src="{{ asset('storage/avatars/' . auth()->user()->avatar_u) }}" id="profileAvatarImg" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="bi bi-person-fill" id="profileAvatarIcon"></i>
                    @endif
                </div>
                <!-- Clic ici ouvre toujours l'appareil photo/galerie normalement -->
                <div class="avatar-edit-badge" id="triggerAvatarUpload" title="Modifier la photo">
                    <i class="bi bi-camera-fill"></i>
                </div>
            </div>
            
            <h5 class="fw-bold text-white text-uppercase m-0 font-monospace mb-1" style="letter-spacing: 0.3px;">
                {{ auth()->user()->nom_u ?? 'Ricky' }}
            </h5>
            <span class="badge bg-white bg-opacity-10 text-white-50 rounded-pill px-3 py-1 font-monospace" style="font-size: 0.7rem; font-weight: 600;">
                MATRICULE : CL-{{ auth()->user()->id_u }}
            </span>
        </div>
    </div>

    <!-- 📦 FENÊTRE MODAL LIÈGE POUR L'AFFICHAGE EN GRAND (À METTRE TOUT EN BAS DE LA PAGE) -->
    <div class="modal fade" id="avatarLightboxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm px-2">
            <div class="modal-content avatar-lightbox-content position-relative">
                <!-- Bouton fermer blanc épuré -->
                <button type="button" class="btn-close btn-close-white btn-close-lightbox" data-bs-dismiss="modal" aria-label="Close"></button>
                
                <div class="modal-body p-2 text-center">
                    <!-- Zone d'affichage de la photo en haute résolution -->
                    <img src="" id="lightboxTargetImg" class="avatar-fullscreen-img" alt="Avatar en grand">
                    
                    <!-- Petit libellé d'identité sous l'image -->
                    <small class="text-white-50 d-block mt-3 font-monospace text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                        {{ auth()->user()->nom_u }}
                    </small>
                </div>
            </div>
        </div>
    </div>



    <!-- 📋 BLOC D'INFORMATIONS PERSONNELLES RÉELLES -->
    <div class="mb-2.5 ps-1">
        <h6 style="color: #71767b; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Données du compte</h6>
    </div>

    <div class="premium-card p-3 mb-4" style="background-color: #111111; border: 1px solid #1f2022; border-radius: 16px;">
        <div class="d-flex flex-column gap-3" style="font-size: 0.88rem;">
            
            <!-- Téléphone Réel de la BDD -->
            <div class="d-flex justify-content-between align-items-center">
                <span style="color: #71767b;"><i class="bi bi-telephone-fill text-primary me-2"></i>Téléphone :</span>
                <strong class="font-monospace text-white">{{ auth()->user()->tel_u ?? 'N/A' }}</strong>
            </div>
            <hr class="my-0 border-white border-opacity-5">
            
            <!-- Catégorie de la BDD (Grossiste / Particulier) -->
            <div class="d-flex justify-content-between align-items-center">
                <span style="color: #71767b;"><i class="bi bi-tags-fill text-primary me-2"></i>Catégorie Tarifaire :</span>
                <span class="badge rounded-pill px-2.5 py-1 fw-bold font-monospace text-uppercase" style="background-color: rgba(0, 82, 255, 0.1); color: #00bcff; font-size: 0.7rem;">
                    {{ auth()->user()->type_u ?? 'Particulier' }}
                </span>
            </div>
            <hr class="my-0 border-white border-opacity-5">
            
            <!-- Dépôt Rattaché dynamique via Éloquent -->
            <div class="d-flex justify-content-between align-items-center">
                <span style="color: #71767b;"><i class="bi bi-building-fill-check text-primary me-2"></i>Dépôt Rattaché :</span>
                <strong class="text-white text-uppercase">
                    {{ auth()->user()->depot->nom_depot ?? 'Non assigné' }}
                </strong>
            </div>
            <hr class="my-0 border-white border-opacity-5">
            
            <!-- Date d'inscription formatée au standard francophone -->
            <div class="d-flex justify-content-between align-items-center">
                <span style="color: #71767b;"><i class="bi bi-calendar-event-fill text-primary me-2"></i>Membre depuis :</span>
                <span class="text-white font-monospace">
                    {{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : date('d/m/Y') }}
                </span>
            </div>
        </div>
    </div>

    <!-- 🔒 SÉCURITÉ COMPTE ACCÈS -->
    <div class="mb-2.5 ps-1">
        <h6 style="color: #71767b; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Sécurité de session</h6>
    </div>
    
    <div class="premium-card p-3 mb-4" style="background-color: #111111; border: 1px solid #1f2022; border-radius: 16px; cursor: pointer;" id="triggerPasswordModalBtn">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2.5">
                <span style="color: #0052ff; font-size: 1.1rem; display: flex; align-items: center;"><i class="bi bi-key-fill"></i></span>
                <div class="text-start">
                    <strong class="text-white d-block" style="font-size: 0.85rem; line-height: 1.2;">Changer mon mot de passe</strong>
                    <small style="color: #71767b; font-size: 0.7rem;">Modifier votre code secret d'accès</small>
                </div>
            </div>
            <span style="color: #71767b; font-size: 0.95rem;">➔</span>
        </div>
    </div>

    <!-- 🛑 BOUTON DÉCONNEXION INCORPORÉ (AVEC RECOUVREMENT SWEETALERT2) -->
    <div class="mt-4 px-1">
        <a href="#" class="btn btn-premium-danger-action w-100 py-3 fw-bold rounded-4 shadow-sm" id="btnProfileLogout">
            <i class="bi bi-box-arrow-right me-2"></i> Fermer ma Session (Déconnexion)
        </a>
        <form id="logout-form-profile" action="{{ route('deconnect') }}" method="POST" class="d-none">@csrf</form>
    </div>

</div>

<!-- 📦 MODAL DE CHANGEMENT DE MOT DE PASSE AVEC ŒIL INTERACTIF -->
<div class="modal fade" id="passwordChangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm px-3">
        <div class="modal-content dark-modal-content" style="background-color: #111111; border: 1px solid #1f2022; color: white; border-radius: 20px;">
            <div class="modal-header dark-modal-header pb-2" style="border-bottom: 1px solid #1f2022;">
                <h6 class="fw-bold m-0 text-white"><i class="bi bi-lock-fill text-primary me-2"></i>Modifier mes accès</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <form id="profilePasswordSubmitForm">
                    
                    <!-- Champ 1 : Ancien Password -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold" style="color: #71767b;">Ancien mot de passe :</label>
                        <div class="input-group">
                            <input type="password" id="old_password" name="old_password" class="form-control text-center fw-bold bg-dark text-white border-end-0" placeholder="••••••••" style="border-color: rgba(255,255,255,0.1) !important; border-top-left-radius: 10px; border-bottom-left-radius: 10px;" required>
                            <span class="input-group-text bg-dark toggle-password-visibility" data-target="#old_password" style="border-color: rgba(255,255,255,0.1) !important; color: #71767b; cursor: pointer; border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Champ 2 : Nouveau Password -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold" style="color: #71767b;">Nouveau mot de passe :</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control text-center fw-bold bg-dark text-white border-end-0" placeholder="••••••••" style="border-color: rgba(255,255,255,0.1) !important; border-top-left-radius: 10px; border-bottom-left-radius: 10px;" required>
                            <span class="input-group-text bg-dark toggle-password-visibility" data-target="#password" style="border-color: rgba(255,255,255,0.1) !important; color: #71767b; cursor: pointer; border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Champ 3 : Confirmation -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold" style="color: #71767b;">Confirmer le nouveau mot de passe :</label>
                        <div class="input-group">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control text-center fw-bold bg-dark text-white border-end-0" placeholder="••••••••" style="border-color: rgba(255,255,255,0.1) !important; border-top-left-radius: 10px; border-bottom-left-radius: 10px;" required>
                            <span class="input-group-text bg-dark toggle-password-visibility" data-target="#password_confirmation" style="border-color: rgba(255,255,255,0.1) !important; color: #71767b; cursor: pointer; border-top-right-radius: 10px; border-bottom-right-radius: 10px;">
                                <i class="bi bi-eye"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold mt-2" style="border-radius: 12px;">Mettre à jour mes accès</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // ====================================================================
    // 🖼️ INTERACTION : CLIC SUR L'AVATAR -> OUVERTURE DE LA PHOTO EN GRAND
    // ====================================================================
    $(document).on('click', '.clickable-avatar-trigger', function() {
        // 1. On cherche s'il y a une balise <img> dans le conteneur
        var imgElement = $(this).find('img');
        var sourceUrl = "";

        if (imgElement.length > 0) {
            // Si le client a une photo personnalisée, on extrait son URL réelle
            sourceUrl = imgElement.attr('src');
        } else {
            // S'il n'a pas encore de photo, on peut utiliser un avatar par défaut neutre
            // ou une icône d'illustration pour simuler le plein écran
            sourceUrl = "https://ui-avatars.com{{ auth()->user()->nom_u }}&background=0052ff&color=fff&size=512";
        }

        // 2. On injecte l'URL de la photo dans la cible de la visionneuse
        $('#lightboxTargetImg').attr('src', sourceUrl);

        // 3. On déploie la fenêtre de cinéma Bootstrap
        $('#avatarLightboxModal').modal('show');
    });

    // Déclenchement de la modal
    $('#triggerPasswordModalBtn').on('click', function() {
        $('#passwordChangeModal').modal('show');
    });

        // ====================================================================
    // 🔐 SÉCURITÉ : CHANGEMENT DE MOT DE PASSE EN AJAX (FINTECH DARK)
    // ====================================================================
    $('#profilePasswordSubmitForm').on('submit', function(e) {
        e.preventDefault(); // Évite le rechargement de la page mobile

        var form = $(this);
        var formData = form.serialize(); // Prépare les données saisies

        // Animation d'attente SweetAlert2
        Swal.fire({
            title: 'Changement de clé...',
            text: 'Cryptage de vos nouveaux accès en cours.',
            width: '310px',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: '/Distrib237/client/modifier-mot-de-passe',
            type: 'POST',
            data: formData + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(response) {
                if (response.success) {
                    Swal.close();
                    
                    // On vide les champs du formulaire pour la sécurité
                    form[0].reset();
                    
                    // On ferme la modal Bootstrap
                    $('#passwordChangeModal').modal('hide');

                    // Alerte de confirmation de succès
                    Swal.fire({
                        title: 'Sécurisé ! 🔐',
                        text: response.message,
                        icon: 'success',
                        width: '310px',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function(xhr) {
                // Interception des erreurs de validation (ex: trop court, identique à l'ancien, etc.)
                var message = "Erreur de mise à jour.";
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    // Récupère le premier message d'erreur de la liste
                    message = Object.values(errors)[0][0];
                } else {
                    message = xhr.responseJSON?.message || "Impossible de modifier vos accès.";
                }

                Swal.fire({
                    title: 'Échec ❌',
                    text: message,
                    icon: 'error',
                    width: '310px'
                });
            }
        });
    });

        // ====================================================================
    // 👀 INTERACTION TACTILE : VOIR / MASQUER LE MOT DE PASSE SUR MOBILE
    // ====================================================================
    $(document).on('click', '.toggle-password-visibility', function() {
        var iconBtn = $(this);
        var icon = iconBtn.find('i');
        
        // On récupère l'identifiant du champ ciblé (ex: #password)
        var targetSelector = iconBtn.data('target');
        var inputField = $(targetSelector);

        // Permutation de l'attribut type du champ HTML
        if (inputField.attr('type') === 'password') {
            inputField.attr('type', 'text'); // Affiche le texte clair
            icon.removeClass('bi-eye').addClass('bi-eye-slash'); // Barre l'œil
            iconBtn.css('color', '#0052ff'); // Allume l'œil en Bleu Électrique
        } else {
            inputField.attr('type', 'password'); // Masque à nouveau avec des points
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
            iconBtn.css('color', '#71767b'); // Éteint l'œil en gris neutre
        }
    });

    // 🚪 ÉCOUTEUR DE DÉCONNEXION INTERACTIF (SWEETALERT2 CHIRURGICAL MOBILE)
    $('#btnProfileLogout').on('click', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Déconnexion ?',
            text: "Fermer votre session de caisse unique ?",
            icon: 'warning',
            width: '310px',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#71767b',
            confirmButtonText: 'Déconnexion',
            cancelButtonText: 'Annuler',
            reverseButtons: true // Bouton de confirmation placé à droite sous le pouce
        }).then((result) => {
            if (result.isConfirmed) {
                // Déclenche la soumission du formulaire POST masqué de Laravel
                $('#logout-form-profile').submit();
            }
        });
    });

    // 1. Déclenche le clic sur le champ de fichier caché au clic sur la caméra
    $('#triggerAvatarUpload').on('click', function() {
        $('#avatarFileInput').click();
    });

    // 2. Intercepte le choix du fichier (galerie ou photo prise en direct)
    $('#avatarFileInput').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        // Validation de sécurité : max 2 Mo pour économiser la bande passante du serveur
        if (file.size > 5* 1024 * 1024) {
            Swal.fire({ title: 'Fichier trop lourd ⚠️', text: "La photo ne doit pas dépasser 5 Mo pour l'affichage mobile.", icon: 'warning', width: '310px' });
            return;
        }

        // Préparation du colis binaire (FormData) pour l'upload AJAX
        var formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Animation d'attente SweetAlert2
        Swal.fire({
            title: 'Mise à jour...',
            text: 'Téléversement de votre photo de profil en cours.',
            width: '200px',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Envoi asynchrone vers Laravel
        $.ajax({
            url: '/Distrib237/client/modifier-avatar',
            type: 'POST',
            data: formData,
            processData: false, // Indispensable pour l'envoi de fichiers binaires
            contentType: false, // Indispensable pour l'envoi de fichiers binaires
            success: function(response) {
                if (response.success) {
                    Swal.close();

                    // Mise à jour graphique instantanée de la bulle d'avatar sans recharger la page !
                    var container = $('#avatarContainer');
                    var newImgUrl = '/storage/avatars/' + response.avatar_name;

                    container.html('<img src="' + newImgUrl + '" style="width:100%; height:100%; object-fit:cover;">');

                }
            },
            error: function(xhr) {
                Swal.fire({ title: 'Échec ❌', text: xhr.responseJSON?.message || "Impossible de charger la photo.", icon: 'error', width: '310px' });
            }
        });
    });
});
</script>
@endpush
