@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE COMPACTE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Gestion du Réseau des Dépôts</h1>
            <small class="text-muted">Configuration géographique des infrastructures et des verrous horaires opérationnels</small>
        </div>
        <!-- Bouton d'action de création de succursale -->
        <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm rounded-3 small" id="btnOpenNewBranchModal">
            <i class="bi bi-building-add me-2"></i> Créer une Section / Dépôt
        </button>
    </div>

    <!-- GRILLE DYNAMIQUE DES DÉPÔTS EN ACTIVITÉ (CHARGÉE DEPUIS LA BD) -->
    <div class="row g-4 mb-5" id="branchesGrid">
        
        @forelse($depots as $dep)
            <!-- Dépôt Dynamique : {{ $dep->nom_depot }} -->
            <div class="col-md-6 col-xl-4 branch-card-item" id="branchCard_{{ $dep->id }}">
                <div class="erp-panel p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            
                            <!-- Gestion Dynamique du Badge d'État selon la BD -->
                            @if($dep->etat_depot === 'operationnel')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                    <i class="bi bi-check-circle-fill me-1"></i> Opérationnel
                                </span>
                            @elseif($dep->etat_depot === 'inactif')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                    <i class="bi bi-x-circle-fill me-1"></i> Inactif
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small" style="color: #b45309 !important;">
                                    <i class="bi bi-gear-wide-connected me-1"></i> En Maintenance
                                </span>
                            @endif

                            <!-- Menu d'actions (Modifier / Supprimer) -->
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 small">
                                    <!-- Les attributs data- contiennent les vraies valeurs de la BD pour pré-remplir la modal -->
                                    <li>
                                        <button type="button" class="dropdown-item rounded-2 edit-branch-trigger" 
                                                data-id="{{ $dep->id }}" 
                                                data-name="{{ $dep->nom_depot }}" 
                                                data-zone="{{ $dep->adresse_depot }}" 
                                                data-open="{{ \Carbon\Carbon::parse($dep->open)->format('H:i') }}" 
                                                data-close="{{ \Carbon\Carbon::parse($dep->close)->format('H:i') }}"
                                                data-status="{{ $dep->etat_depot }}">
                                            <i class="bi bi-pencil-square text-primary me-2"></i>Modifier
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger rounded-2 delete-branch-trigger" data-id="{{ $dep->id }}">
                                            <i class="bi bi-trash3-fill me-2"></i>Supprimer
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Informations Textuelles depuis la BD -->
                        <h5 class="fw-bold text-dark mb-1 branch-title-text">{{ $dep->nom_depot }}</h5>
                        <small class="text-muted d-block mb-3">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $dep->adresse_depot }}
                        </small>
                    </div>
                    
                    <div class="border-top pt-3 mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-secondary fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i> Sécurité Horaire :</span>
                            <!-- Formatage propre des heures H:i -->
                            <span class="badge bg-light text-dark border font-monospace fw-bold">
                                {{ \Carbon\Carbon::parse($dep->open)->format('H:i') }} - {{ \Carbon\Carbon::parse($dep->close)->format('H:i') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-secondary fw-semibold"><i class="bi bi-people-fill me-1 text-primary"></i> Personnel affecté :</span>
                            <span class="small fw-bold text-dark">
                                @if($dep->employes_count > 1)
                                    {{ $dep->employes_count }} Agents
                                @elseif($dep->employes_count === 1)
                                    1 Agent
                                @else
                                    0 Agent
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Message de secours si la base de données ne contient aucun dépôt -->
            <div class="col-12 text-center py-5">
                <i class="bi bi-building-x display-4 text-muted d-block mb-3"></i>
                <p class="text-secondary fw-medium">Aucun dépôt n'est actuellement configuré dans le système.</p>
                <small class="text-muted">Cliquez sur le bouton "Créer une Section / Dépôt" pour commencer.</small>
            </div>
        @endforelse

    </div>


</div>

<!-- MODAL UNIQUE CONFIGURATION : CRÉATION / MODIFICATION SUCURSALLE -->
<div class="modal fade" id="branchFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light pb-3">
                <h5 class="fw-bold text-dark m-0" id="branchModalTitle">🏢 Nouvelle Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <form id="branchConfigForm">
                    @csrf
                    <!-- Champ masqué pour l'ID avec son attribut name -->
                    <input type="hidden" id="input_branch_id" name="id_d">

                    <!-- Désignation -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Nom de la Succursale / Dépôt</label>
                        <input type="text" name="nom_depot" class="form-control" id="input_branch_name" placeholder="Ex: Dépôt Kribi" >
                        <div class="invalid-feedback"></div> <!-- Emplacement de l'erreur -->
                    </div>

                    <!-- Secteur Géographique -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Secteur Géographique / Zone</label>
                        <input type="text" name="secteur_geographique" class="form-control" id="input_branch_zone" placeholder="Ex: Quartier Lycée" >
                        <div class="invalid-feedback"></div> <!-- Emplacement de l'erreur -->
                    </div>

                    <!-- Etat -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">État du Dépôt</label>
                        <select class="form-control" name="etat_depot" id="input_branch_status" >
                            <option value="operationnel">Opérationnel</option>
                            <option value="inactif">Inactif</option>
                            <option value="en_maintenance">En Maintenance</option>
                        </select>
                        <div class="invalid-feedback"></div> <!-- Emplacement de l'erreur -->
                    </div>

                    <!-- Heure d'Ouverture -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Heure d'Ouverture Légitime</label>
                        <input type="time" name="heure_ouverture" class="form-control font-monospace border-2" id="input_branch_open" value="08:00" >
                        <div class="invalid-feedback"></div> <!-- Emplacement de l'erreur -->
                    </div>

                    <!-- Heure de Verrouillage Nocturne -->
                    <div class="mb-4">
                        <label class="form-label form-label-custom mb-1">Heure de Verrouillage Nocturne</label>
                        <input type="time" name="heure_verrouillage" class="form-control font-monospace border-2 text-danger fw-bold" id="input_branch_close" value="22:00" >
                        <div class="invalid-feedback"></div> <!-- Emplacement de l'erreur -->
                    </div>

                    <!-- Boutons de validation de la modal -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Valider</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div> <!-- Fin de la Modal -->

</div> <!-- Fin du container-fluid global -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        // 1. BOUTON CRÉER : Configuration de la Modal en mode Création
        $('#btnOpenNewBranchModal').on('click', function() {
            $('#branchModalTitle').html('🏢 Nouvelle Section');
            $('#branchConfigForm')[0].reset();
            $('#input_branch_id').val('');

            $('#branchFormModal').modal('show');
        });

        // 2. BOUTON MODIFIER : Lecture des attributs "data-" et chargement dans les champs
        $(document).on('click', '.edit-branch-trigger', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var zone = $(this).data('zone');
            var open = $(this).data('open');
            var close = $(this).data('close');

            // Configuration de la Modal en mode Édition
            $('#branchModalTitle').html('🏢 Configurer le Dépôt');
            $('#input_branch_id').val(id);
            $('#input_branch_name').val(name);
            $('#input_branch_zone').val(zone);
            $('#input_branch_open').val(open);
            $('#input_branch_close').val(close);

            $('#branchFormModal').modal('show');
        });

        // 3. ACTION DE SUPPRESSION SIMULÉE (EFFACEMENT DE LA CARTE EN FONDU)
        $(document).on('click', '.delete-branch-trigger', function() {
            var id = $(this).data('id');
            var $card = $('#branchCard_' + id);
            var branchName = $card.find('.branch-title-text').text();

            // Ton confirm() d'origine conservé intact
            if (confirm("Êtes-vous sûr de vouloir supprimer définitivement le [ " + branchName + " ] du réseau commercial ?")) {
                
                // 💡 INTEGRATION DE LA REQUÊTE COMPTABLE AJAX
                $.ajax({
                    url: '/Distrib237/admin/depots/supprimer/' + id,
                    type: 'POST',
                    data: {
                        // Récupération automatique du jeton CSRF de sécurité Laravel
                        _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            // Ton animation de disparition d'origine s'exécute si la BDD a validé
                            $card.fadeOut(500, function() { 
                                $(this).remove();
                                var toastEl = document.getElementById('registerSuccessToast');
                                var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
                                const toastMessage = toast.querySelector('span');
                                toastMessage.textContent = response.message || "Le dépôt a été supprimé avec succès.";
                                toast.show();
                            });
                        }
                    },
                    error: function(xhr) {
                        // Alerte en cas de problème (ex: dépôt introuvable ou erreur serveur)
                        alert("Échec : " + (xhr.responseJSON?.message || "Erreur de communication avec le serveur."));
                    }
                });

            }
        });


        // 4. Gestion de la somisssion pour creation et  modification d'un depot
        $('#branchConfigForm').on('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement brutal de la page
            
            var $form = $(this);
            var formData = $form.serialize(); // Capture automatiquement tous les champs du formulaire
            var id_d = $('#input_branch_id').val();
            var isEdit = (id_d !== ""); // Si l'ID n'est pas vide, on est en mode modification

            // 1. Nettoyage complet des anciennes erreurs visuelles avant d'envoyer la requête
            $form.find('.form-control').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');

            $.ajax({
                url: "{{ route('depot.store') }}", // Route vers ton contrôleur Laravel
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        window.location.replace(response.redirect);
                        
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // 2. RECUPERATION DES ERREURS DE VALIDATION DE LARAVEL
                        var errors = xhr.responseJSON.errors;
                        
                        // On parcourt chaque erreur (ex: key = "nom_depot", messages = ["Le nom est requis"])
                        $.each(errors, function(key, messages) {
                            // On trouve l'input par son attribut name et on ajoute le contour rouge Bootstrap
                            var $input = $form.find('[name="' + key + '"]');
                            $input.addClass('is-invalid');
                            
                            // On injecte le texte de l'erreur dans la div "invalid-feedback" juste à côté de cet input
                            $input.siblings('.invalid-feedback').text(messages[0]);
                        });
                    } else if (xhr.status === 403) {
                        alert("Accès interdit : Droits d'administration requis.");
                    } else {
                        alert("Une erreur technique interne est survenue.");
                    }
                }
            });
        });

    }); 

</script>
@endpush