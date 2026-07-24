@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE SUPERIEURE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Gestion des Clients</h1>
            <small class="text-muted">Répertoire des partenaires et gestion du Compte Épargne</small>
        </div>
        @if(Auth::user()->role_u === 'employe')
            <!-- Bouton d'ouverture du formulaire (Modal Bootstrap) -->
            <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm rounded-3 small" id="btnOpenNewClientModal">
                <i class="bi bi-person-plus-fill me-2"></i> Nouveau Client
            </button>
        @endif
    </div>

    <!-- BLOC TABLEAU : LISTE COMPLÈTE DES CLIENTS -->
    <div class="erp-panel shadow-sm">
        <div class="panel-header bg-white border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
            <span class="fw-bold text-dark"><i class="bi bi-people-fill text-muted me-2"></i>Registre Clientèle</span>
            <!-- Champ local de recherche/filtrage instantané -->
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control bg-light border-start-0 small text-sm" id="searchClientInput" placeholder="Rechercher un client...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="clientTable">
                <thead>
                    <tr class="text-muted small text-uppercase font-monospace border-bottom bg-light">
                        <th style="width: 60px;"></th>
                        <th>Code Client</th>
                        <th>Nom complet / Établissement</th>
                        <th>Numéro de Téléphone</th>
                        <th>Adresse de Livraison</th>
                        <th class="text-end">Solde Compte Épargne</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                        
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($clients as $index => $client)
                        <tr class="client-row" id="clientRow_{{ $client->id_u }}">
                            
                            <!-- 💡 DEBUT COLONNE AVATAR DYNAMIQUE -->
                            <td class="ps-3">
                                <div class="table-avatar-wrapper shadow-sm view-large-avatar-trigger" 
                                    style="cursor: pointer;" 
                                    data-name="{{ strtoupper($client->nom_u) }}"
                                    data-src="{{ $client->avatar_u ? asset('storage/avatars/' . $client->avatar_u) : 'https://ui-avatars.com' . urlencode($client->nom_u) . '&background=0052ff&color=fff&size=256' }}">
                                    
                                    @if($client->avatar_u)
                                        <!-- Cas A : Le client possède une vraie photo sur le serveur -->
                                        <img src="{{ asset('storage/avatars/' . $client->avatar_u) }}" class="table-avatar-img" alt="Avatar">
                                    @else
                                        <!-- Cas B : Génération d'une bulle de couleur moderne avec ses initiales -->
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary table-avatar-initials font-monospace">
                                            {{ substr($client->nom_u, 0, 1) }}
                                        </div>
                                    @endif

                                </div>
                            </td>
                            <!-- 💡 FIN COLONNE AVATAR DYNAMIQUE -->

                            <td class="font-monospace fw-bold text-secondary"><strong class="text-danger">{{ $client->id_u }}</strong></td>
                            <td>
                                <strong>{{ strtoupper($client->nom_u) }}</strong><br>
                                <small class="text-muted">{{ $client->email_u }}</small>
                            </td> 
                            <td><strong class="text-secondary">{{ $client->tel_u }}</strong></td>
                            <td><strong class="text-primary">{{ $client->localisation_u }}</strong></td>
                            <td class="text-end fw-bold text-success">{{ number_format($client->solde_u, 0, ',', ' ') }} FCFA</td>
                            
                            <td class="text-center">
                                @if(Auth::user()->role_u === 'employe')
                                    <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 open-recharge-modal-btn text-success border-warning" 
                                        data-id="{{ $client->id_u }}" data-name="{{ $client->nom_u }}" data-solde="{{ $client->solde_u }}" data-bs-toggle="tooltip" title="Recharger le compte">
                                        <i class="bi bi-wallet2"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary edit-client-btn" data-id="{{ $client->id_u }}" data-code="{{ $client->id_u }}" data-name="{{ $client->nom_u }}" data-email="{{ $client->email_u }}" data-tel="{{ $client->tel_u }}" data-address="{{ $client->localisation_u }}" data-balance="{{ $client->solde_u }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                @elseif(Auth::user()->role_u === 'admin')
                                    <button class="btn btn-sm btn-outline-danger delete-client-btn" data-id="{{ $client->id_u }}">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>
<!-- MODAL DE RECHARGE DE COMPTE CLIENT -->
    <div class="modal fade" id="rechargeModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                
                <div class="modal-header bg-light border-0 py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 bg-success-subtle text-success rounded-3"><i class="bi bi-cash-coin fs-5"></i></span>
                        <h5 class="modal-title fw-bold text-dark m-0">Créditer un Compte Client</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="rechargeForm" method="POST" >
                    @csrf
                    <!-- Champ masqué pour stocker l'ID du client à recharger -->
                    <input type="hidden" name="client_id" id="input_recharge_client_id">

                    <div class="modal-body px-4 py-3">
                        
                        <!-- Information Client (Lecture seule) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Nom du Client</label>
                            <input type="text" class="form-control bg-light text-dark fw-bold" id="input_recharge_client_name" readonly>
                        </div>

                        <!-- Solde Actuel (Lecture seule pour repérer les écarts) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Solde Actuel Avant Versement</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light text-muted font-monospace fw-bold" id="input_recharge_client_solde" readonly>
                                <span class="input-group-text bg-light border-start-0 font-monospace text-muted fw-bold">FCFA</span>
                            </div>
                        </div>

                        <!-- Choix du Moyen de Paiement -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Moyen d'encaissement</label>
                            <select class="form-select fw-bold text-dark border-2" name="moyen_recharge" id="input_recharge_moyen" required>
                                <option value="especes" selected>💵 Espèces / Cash Physique</option>
                                <option value="momo">📲 MTN Mobile Money</option>
                                <option value="om">🍊 Orange Money</option>
                            </select>
                        </div>

                        <!-- Montant de la recharge -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Montant à recharger (FCFA)</label>
                            <input type="number" class="form-control form-control-lg fw-bold border-2 text-success font-monospace" name="montant_r" id="input_recharge_montant" placeholder="Ex: 25000" min="500" required>
                            <div class="invalid-feedback text-danger small mt-1"></div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light border-0 py-3 px-4 gap-2">
                        <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success rounded-3 px-4 fw-bold shadow-sm" id="btnSubmitRecharge">Valider le Versement</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

<!-- MODAL BOOTSTRAP UNIQUE : CRÉATION / MODIFICATION CLIENT -->
<div class="modal fade" id="clientFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light pb-3">
                <h5 class="fw-bold text-dark m-0" id="clientModalTitle">👥 Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <form id="clientForm" method="POST" action="#">
                    @csrf
                    <!-- Champ masqué pour l'identifiant lors de la modification -->
                    <input type="hidden" name="id_c" id="input_id_c">

                    <!-- Ligne 1 : Code et Nom -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Code Client</label>
                            <input type="text" class="form-control font-monospace bg-light fw-bold text-secondary" id="input_code_display" name="code_c" disabled>
                            <input type="hidden" name="ref_c" id="input_ref_c">
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Nom du Client / Éts</label>
                            <input type="text" class="form-control" name="nom_u" id="input_nom_c" placeholder="Jean Dupont" >
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Ligne 2 : Contact et Adresse -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Numéro Téléphone</label>
                            <input type="number" class="form-control" name="tel_u" id="input_tel_c" placeholder="693952476" >
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Adresse de livraison</label>
                            <input type="text" class="form-control" name="localisation_u" id="input_adresse_c" placeholder="Logpom, Douala" >
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <!-- Ligne 3 : Email et Type de Client -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Adresse Email</label>
                            <input type="email" class="form-control" name="email_u" id="input_email_c" placeholder="jean.dupont@example.com" >
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Type de Client</label>
                            <select class="form-control" name="type_u" id="input_type_c">
                                <option value="particulier">Particulier</option>
                                <option value="Depot">Depot</option>
                                <option value="tricycle">Tricycle</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Boutons de validation -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 📦 MODAL APERÇU GRAND FORMAT POUR LE REGISTRE -->
<div class="modal fade" id="clientAvatarViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm px-3">
        <div class="modal-content border-0 shadow-lg bg-dark text-white rounded-4" style="overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-3 pe-3 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="" id="targetClientLargeImg" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5);">
                <h6 class="fw-bold text-uppercase font-monospace mt-3 mb-1 text-warning" id="targetClientLargeName">CLIENT</h6>
                <small class="text-white-50 small font-monospace">Fiche d'identification Distrib237</small>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
    $(document).ready(function() {

        // 1. FILTRAGE ET RECHERCHE LOCALE DANS LE TABLEAU SANS AJAX
        $('#searchClientInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#clientTable tbody .client-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // 2. BOUTON NOUVEAU CLIENT : Génération du code et ouverture
        $('#btnOpenNewClientModal').on('click', function() {
            var matricule = "CL#DP" + Math.floor(1000 + Math.random() * 9000); // Génère CL#DPXXXX aléatoire
            
            // Configuration de la Modal en mode Création
            $('#clientModalTitle').text('👥 Nouveau Client');
            $('#clientForm')[0].reset();
            $('#input_id_c').val('');
            $('#input_code_display').val(matricule);
            $('#input_ref_c').val(matricule);
            $('#savingsFieldContainer').removeClass('d-none'); // Affiche le champ épargne pour la création

            // Ouvre la modal Bootstrap
            $('#clientFormModal').modal('show');
        });

        // 3. BOUTON MODIFIER : Capture des attributs "data-" et pré-remplissage
        $(document).on('click', '.edit-client-btn', function() {
            var id = $(this).data('id');
            var code = $(this).data('code');
            var name = $(this).data('name');
            var tel = $(this).data('tel');
            var address = $(this).data('address');
            var balance = $(this).data('balance');
            var email = $(this).data('email');

            // Pré-remplit la Modal en mode Édition
            $('#clientModalTitle').text('👥 Modifier le Client');
            $('#input_id_c').val(id);
            $('#input_code_display').val(code);
            $('#input_ref_c').val(code);
            $('#input_nom_c').val(name);
            $('#input_tel_c').val(tel);
            $('#input_adresse_c').val(address);
            $('#input_solde_c').val(balance);
            $('#input_email_c').val(email);
            $('#savingsFieldContainer').addClass('d-none'); // Masque le champ de provision initiale en modification

            $('#clientFormModal').modal('show');
        });

        // 4. ACTION DE SUPPRESSION SIMULÉE (EFFACEMENT DU DOM EN FONDU)
        $(document).on('click', '.delete-client-btn', function() {
            var id = $(this).data('id');
            var $row = $('#clientRow_' + id);

            if (confirm("Êtes-vous sûr de vouloir retirer ce client des registres du dépôt ?")) {
                $row.fadeOut(500, function() {
                    $(this).remove();
                    alert("Client retiré avec succès (Simulation DOM).");
                });
            }
        });

        // 5. SOUMISSION DU FORMULAIRE (SIMULATION ACTION SUBMIT)
        $('#clientForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#input_id_c').val();
            var isEditMode = (id !== ''); // Détermine si c'est une création ou une modification
            $form = $(this);
            $form.find('.invalid-feedback').text(''); // Réinitialise les messages d'erreur
            $form.find('.is-invalid').removeClass('is-invalid'); // Réinitialise les classes d'erreur
            
            //Ajax pour ajouter ou modifier le client
            var formData = $(this).serialize();
            $.ajax({
                url: '{{ route("client.store") }}', // Remplacez par l'URL de votre route de sauvegarde côté serveur
                method: 'POST',
                data: formData,
                success: function(response) {
                    if(response.success) {
                        window.location.replace(response.redirect);
                    }
                },
                error: function(xhr, status, error) {
                    if(xhr.status === 422) { 
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            // CORRECTION ICI : Ciblage par l'attribut 'name' au lieu de l'ID
                            var inputField = $form.find('[name="' + field + '"]');
                            inputField.addClass('is-invalid');
                            inputField.siblings('.invalid-feedback').text(messages[0]);
                        });
                    } else {
                        alert("Une erreur est survenue. Veuillez réessayer.");
                    }
                }
            })
        });

        // 1. Détection du clic sur l'icône Portefeuille de la liste
        $(document).on('click', '.open-recharge-modal-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var solde = parseFloat($(this).data('solde')) || 0;

            // Injection des valeurs dans le formulaire du modal
            $('#input_recharge_client_id').val(id);
            $('#input_recharge_client_name').val(name.toUpperCase());
            $('#input_recharge_client_solde').val(solde.toLocaleString('fr-FR'));
            $('#input_recharge_montant').val(''); // Laisse le champ vide
            
            // Nettoyage des anciennes classes d'erreurs
            $('#rechargeForm input').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            $('#rechargeModal').modal('show');
        });

        // 2. Soumission avec double validation et bilan financier
        $('#rechargeForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var nomClient = $('#input_recharge_client_name').val();
            var ancienSoldeStr = $('#input_recharge_client_solde').val();
            var ancienSolde = parseFloat(ancienSoldeStr.replace(/\s/g, '')) || 0;
            
            var montant = parseFloat($('#input_recharge_montant').val()) || 0;
            var moyenText = $('#input_recharge_moyen option:selected').text();
            
            // Calcul du futur solde théorique pour le bilan de vérification
            var futurSolde = ancienSolde + montant;

            if (montant < 500) return;

            // Affichage de la boîte de contrôle financière
            Swal.fire({
                title: '💰 Contrôle de Caisse Interne',
                width: '550px',
                html: `
                    <div class="text-start border rounded-3 p-3 bg-light" style="font-size: 0.95rem;">
                        <div class="mb-2 pb-2 border-bottom border-secondary border-opacity-10 text-center">
                            <span class="fs-6 text-dark fw-bold uppercase text-primary">${nomClient}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-muted">
                            <span>Solde initial :</span>
                            <span class="font-monospace fw-semibold">${ancienSolde.toLocaleString('fr-FR')} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success fw-bold">
                            <span>Versement (${moyenText}) :</span>
                            <span class="font-monospace">+ ${montant.toLocaleString('fr-FR')} FCFA</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top border-dark border-opacity-10 text-dark fw-bold fs-6">
                            <span>Nouveau Solde disponible :</span>
                            <span class="font-monospace text-primary">${futurSolde.toLocaleString('fr-FR')} FCFA</span>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Vert
                cancelButtonColor: '#6c757d',   // Gris
                confirmButtonText: '✅ Encaisser & Créditer',
                cancelButtonText: 'Corriger la saisie',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    var btnSubmit = $('#btnSubmitRecharge');
                    btnSubmit.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div> Écriture de caisse...');

                    // Appel AJAX vers votre contrôleur
                    $.ajax({
                        url: "{{ route('client.recharger') }}",
                        type: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Compte Crédité !',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                $('#rechargeModal').modal('hide');

                                // Rafraîchit la liste pour voir le nouveau solde mis à jour instantanément
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                if(errors.montant_r) {
                                    $('#input_recharge_montant').addClass('is-invalid');
                                    $('#input_recharge_montant').closest('.mb-3').find('.invalid-feedback').text(errors.montant_r[0]);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erreur',
                                    text: xhr.responseJSON?.message || "Une erreur est survenue lors de la transaction."
                                });
                            }
                        },
                        complete: function() {
                            btnSubmit.prop('disabled', false).text("Valider le Versement");
                        }
                    });
                }
            });
    });


        // ====================================================================
        // 🖼️ APERÇU EN GRAND DE LA PHOTO DU CLIENT DEPUIS LE REGISTRE DE TABLEAU
        // ====================================================================
        $(document).on('click', '.view-large-avatar-trigger', function() {
            var trigger = $(this);
            var sourceUrl = trigger.attr('data-src');
            var clientName = trigger.attr('data-name');

            // Injection instantanée dans les cibles de la modal d'aperçu
            $('#targetClientLargeImg').attr('src', sourceUrl);
            $('#targetClientLargeName').text(clientName);

            // Déploiement graphique
            $('#clientAvatarViewerModal').modal('show');
        });

    });

</script>

@endpush