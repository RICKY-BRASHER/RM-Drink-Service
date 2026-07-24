@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE SUPERIEURE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Gestion du Personnel & Ressources</h1>
            <small class="text-muted">Affectations des agents de dépôts, suivi des rôles et audit des performances</small>
        </div>
        <!-- Bouton de recrutement (Modal Bootstrap) -->
        <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm rounded-3 small" id="btnOpenNewEmployeeModal">
            <i class="bi bi-person-plus-fill me-2"></i> Recruter un Agent
        </button>
    </div>

    <!-- BLOC TABLEAU : CLASSEMENT DU PERSONNEL PAR RENDEMENT (Cahier des charges) -->
    <div class="erp-panel shadow-sm">
        <div class="panel-header bg-white border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
            <span class="fw-bold text-dark"><i class="bi bi-person-badge-fill text-muted me-2"></i>Classement et Activités de l'Équipe</span>
            <!-- Recherche instantanée locale -->
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control bg-light border-start-0 small text-sm" id="searchEmployeeInput" placeholder="Rechercher un agent...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" id="employeeTable">
                <thead>
                    <tr class="text-muted small text-uppercase font-monospace border-bottom bg-light">
                        <th style="width: 70px;" class="text-center">Rang</th>
                        <th>Identité / Contact</th>
                        <th>Fonction Système</th>
                        <th>Dépôt Affecté</th>
                        <th class="text-center">Adresse</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($employes as $index => $employe)
                        <tr class="employee-row" id="employeeRow_{{ $employe->id_u }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $employe->nom_u }}</strong><br>
                                <small class="text-muted">{{ $employe->email_u }} | {{ $employe->tel_u }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ strtoupper($employe->type_u) }}</span>
                            </td>
                            <td>
                                <strong class="badge bg-success">{{ strtoupper($employe->depot ? $employe->depot->nom_depot : 'Non affecté') }}</strong>
                                <input type="hidden" name="depot_id" value="{{ $employe->depot_id }}">
                                <input type="hidden" name="role_u" value="{{ $employe->type_u }}">
                                <input type="hidden" id="adresse" value="{{ $employe->localisation_u }}">
                            </td>
                            <td class="text-center">
                                <strong >{{ $employe->localisation_u }}</strong>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary edit-employee-btn" data-bs-toggle="modal" data-bs-target="#employeeFormModal" data-id="{{ $employe->id_u }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteEmployee({{ $employe->id_u }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL UNIQUE CONFIGURATION : RECRUTEMENT / MUTATION AGENT -->
<div class="modal fade" id="employeeFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light pb-3">
                <h5 class="fw-bold text-dark m-0" id="employeeModalTitle">👤 Recruter un Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <form id="employeeForm">
                    @csrf
                    <input type="hidden" name="id_u" id="input_emp_id">

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Nom Complet</label>
                            <input type="text" class="form-control" name="nom_u" id="input_emp_name" placeholder="Ex: Jean Dupont" >
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Adresse E-mail Pro</label>
                            <input type="email" class="form-control" name="email_u" id="input_emp_email" placeholder="agent@entreprise.com" >
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Numéro Téléphone</label>
                            <input type="number" class="form-control" name="tel_u" id="input_emp_tel" placeholder="2376xxxxxxxx" >
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Adresse/Residence</label>
                            <input type="text" class="form-control" name="adresse_u" id="input_emp_adresse" placeholder="Ex: Logbessou Baoba" >
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Affectation Dépôt</label>
                            <select class="form-select fw-semibold text-secondary" name="depot_id" id="input_emp_depot" >
                                <option value="" disabled selected>-- Choisir un dépôt --</option>
                                @foreach($depots as $depot)
                                    <option value="{{ $depot->id }}">{{ $depot->nom_depot }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Fonctions & Droits Système</label>
                            <select class="form-select fw-semibold text-secondary" name="role_u" id="input_emp_role" >
                                <option value="" disabled selected>-- Assigner un poste métier --</option>
                                <option value="magasinier">Magasinier (Supervision interne local)</option>
                                <option value="transporteur">Transporteur (Livraison)</option>
                                <option value="chauffeur">Chauffeur (Transport)</option>
                                <option value="manutentionnaire">Manutentionnaire (Stockage)</option>
                                <option value="personnel">Secrétaire / Caissier comptoir (Facturation courante)</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Enregistrer l'accès</button>
                    </div>
                </form>
 
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

<script>
    $(document).ready(function() {

        // 1. RECHERCHE TEXTUELLE LOCALE SANS RECHARGER LA PAGE
        $('#searchEmployeeInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#employeeTable tbody .employee-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // 2. BOUTON RECRUTER : Configuration de la Modal en mode Création
        $('#btnOpenNewEmployeeModal').on('click', function() {
            $('#employeeModalTitle').text('👤 Recruter un Agent');
            $('#employeeForm')[0].reset();
            $('#input_emp_id').val('');

            $('#employeeFormModal').modal('show');
        });

        // 3. BOUTON MODIFIER / MUTER : Chargement des paramètres de l'agent
        $(document).on('click', '.edit-employee-btn', function() {
            var id = $(this).data('id');
            var $row = $('#employeeRow_' + id);
            var name = $row.find('strong').first().text(); // Récupère le nom complet de l'employé
            var email = $row.find('small').first().text().split('|')[0].trim();
            var tel = $row.find('small').first().text().split('|')[1].trim();
            var depot = $row.find('input[name="depot_id"]').val(); // Récupère l'ID du dépôt affecté
            var role = $row.find('input[name="role_u"]').val(); // Récupère le rôle de l'employé
            var adresse = $row.find('#adresse').val(); // Récupère l'adresse de l'employé


            // Mutation / Changement de poste de l'employé
            $('#employeeModalTitle').text('👤 Muter / Configurer l\'Agent');
            $('#input_emp_id').val(id);
            $('#input_emp_name').val(name);
            $('#input_emp_email').val(email);
            $('#input_emp_tel').val(tel);
            $('#input_emp_depot').val(depot);
            $('#input_emp_role').val(role);
            $('#input_emp_adresse').val(adresse);

            $('#employeeFormModal').modal('show');
        });

        // 4. ACTION DE VÉROUILLAGE DES ACCÈS (SÉCURITÉ INFRASTRUCTURE FRAUDE)
        $(document).on('click', '.block-employee-btn', function() {
            var id = $(this).data('id');
            var $row = $('#employeeRow_' + id);
            var employeeName = $row.find('strong').text();

            if (confirm("⚠️ Sécurité : Souhaitez-vous suspendre temporairement les droits d'accès de [ " + employeeName + " ] ? Ses verrous de facturation seront coupés immédiatement.")) {
                alert("Accès suspendus. L'agent ne peut plus initialiser de session comptoir.");
            }
        });

        // 5. SOUMISSION DU FORMULAIRE DE RECRUTEMENT
        $('#employeeForm').on('submit', function(e) {
            e.preventDefault(); // Bloque le rechargement brut de la page
            
            var $form = $(this);
            var formData = $form.serialize();
            var id_u = $('#input_emp_id').val();
            var isEdit = (id_u !== ""); // Détermine si on est en mode modification ou ajout

            // Nettoyage complet des erreurs précédentes
            $form.find('.form-control, .form-select').removeClass('is-invalid');
            $form.find('.invalid-feedback').text('');

            $.ajax({
                url: "{{ route('personnel.store') }}",
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
                        var errors = xhr.responseJSON.errors;
                        // Parcourt et affiche chaque message d'erreur sous l'input concerné
                        $.each(errors, function(key, messages) {
                            var $input = $form.find('[name="' + key + '"]');
                            $input.addClass('is-invalid');
                            $input.siblings('.invalid-feedback').text(messages[0]);
                        });
                    } else if (xhr.status === 403) {
                        alert("Accès refusé : Droits d'administration requis.");
                    } else {
                        alert("Erreur système : Impossible d'enregistrer l'employé.");
                    }
                }
            });
        });

    });

</script>
@endpush