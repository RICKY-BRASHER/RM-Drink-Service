@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- EN-TÊTE DU SECRÉTARIAT CENTRALISÉ -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Gestion Logistique des Commandes</h1>
            <small class="text-muted">Traitement et encaissement des réservations de casiers et bons d'achats du dépôt</small>
        </div>
        <div>
            <!-- Compte global cumulé de vos deux variables transmises par le contrôleur -->
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-clock-history me-1"></i> {{ count($commandesRecues) + count($commandesEnAttente) }} En cours de traitement
            </span>
        </div>
    </div>

    <!-- 📱 SYSTÈME DE ONGLETS PREMIUM POUR SÉPARER LES COMPTES DE COMPTOIR ET LES FLUX WEB -->
    <ul class="nav nav-tabs border-bottom mb-4 gap-2" id="fluxLogistiqueTabs" role="tablist" style="border-width: 2px !important;">
        <li class="nav-item" role="presentation">
            <button class="nav-top-tab nav-link active fw-bold px-3 py-2.5 rounded-3 position-relative" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-flux" type="button" role="tab" aria-controls="online-flux" aria-selected="true" style="transition: all 0.2s;">
                📱 Commandes Reçues en Ligne 
                <span class="badge bg-primary ms-1 font-monospace">{{ count($commandesRecues) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-top-tab nav-link fw-bold px-3 py-2.5 rounded-3 position-relative" id="counter-tab" data-bs-toggle="tab" data-bs-target="#counter-flux" type="button" role="tab" aria-controls="counter-flux" aria-selected="false" style="transition: all 0.2s;">
                ⏳ Mises en Attente au Comptoir
                <span class="badge bg-secondary ms-1 font-monospace">{{ count($commandesEnAttente) }}</span>
            </button>
        </li>
    </ul>

    <!-- PANNEAUX DE RENDU DES GRANDS TABLEAUX LOGISTIQUES -->
    <div class="tab-content" id="fluxLogistiqueTabsContent">

        {{-- ==================================================================== --}}
        {{-- BLOC 1 : PANNEAU DES COMMANDES REÇUES EN LIGNE (STATUT 'ENVOYER')     --}}
        {{-- ==================================================================== --}}
        <div class="tab-pane fade show active" id="online-flux" role="tabpanel" aria-labelledby="online-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive scrollable-table-container" style="max-height: 490px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.82rem; top: 0; z-index: 10;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 15%;">Code Facture</th>
                                <th style="width: 15%;">Date Envoi</th>
                                <th style="width: 25%;">Client Distant</th>
                                <th style="width: 20%;">Contenu demandé</th>
                                <th class="text-end" style="width: 13%;">Estimation Net</th>
                                <th class="text-center pe-4" style="width: 12%;">Actions Caisse</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white border-top-0" style="font-size: 0.92rem;">
                            @forelse($commandesRecues as $commande)
                                <tr class="pending-order-row align-middle" id="orderRow_{{ $commande->id }}">
                                    <td class="ps-4 font-monospace fw-bold text-primary">{{ $commande->code_facture }}</td>
                                    <td class="text-secondary font-monospace">{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="fw-bold text-dark text-uppercase">{{ $commande->client?->nom_u ?? 'Client Sans Profil' }}</div>
                                        <small class="text-muted">Épargne actuelle : <span class="font-monospace fw-semibold text-success">{{ number_format($commande->client?->solde_u ?? 0, 0, ',', ' ') }} F</span></small>
                                    </td>
                                    <td>
                                        <div class="text-truncate text-secondary uppercase" style="max-width: 220px;" data-bs-toggle="tooltip" title="@foreach($commande->detailVentes as $d){{ $d->produit?->nom_p }} (x{{ $d->quantite }}), @endforeach">
                                            @foreach($commande->detailVentes as $detail)
                                                {{ $detail->produit?->nom_p }}<small class="text-muted font-monospace">(x{{ (float)$detail->quantite }} cs)</small>{{ !$loop->last ? ' + ' : '' }}
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark fs-6">{{ number_format($commande->montant_total, 0, ',', ' ') }} <small class="text-muted" style="font-size:0.75rem;">F</small></td>
                                    <!-- Boutons actions simplifiés pour les commandes EN LIGNE -->
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1.5">
                                            
                                            <!-- 💡 LE NOUVEAU BOUTON MODIFIER CRAYON À INSÉRER ICI -->
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 btn-ouvrir-modification text-warning border-warning" 
                                                    data-id="{{ $commande->id }}" 
                                                    data-code="{{ $commande->code_facture }}" 
                                                    data-clientname="{{ $commande->client ? strtoupper($commande->client->nom_u) : 'CLIENT' }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <!-- Ton bouton vert d'encaissement d'origine -->
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 valid-order-btn text-success border-success" 
                                                    data-id="{{ $commande->id }}"
                                                    data-code="{{ $commande->code_facture }}"
                                                    data-total="{{ $commande->montant_total }}"
                                                    data-avance="{{ $commande->montant_verse }}" 
                                                    data-clientname="{{ $commande->client ? strtoupper($commande->client->nom_u) : 'CLIENT' }}"
                                                    data-balance="{{ $commande->client ? $commande->client->solde_u : 0 }}">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>

                                            <!-- Ton bouton rouge d'annulation d'origine -->
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 del-order-btn text-danger border-danger" data-id="{{ $commande->id }}" data-code="{{ $commande->code_facture }}">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small border-0">
                                        <i class="bi bi-phone-vibrate fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Aucun panier client n'est en attente de réception en ligne dans ce dépôt.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ==================================================================== --}}
        {{-- BLOC 2 : PANNEAU DES COMMANDES EN ATTENTE COMPTOIR (STATUT 'ATTENTE')  --}}
        {{-- ==================================================================== --}}
        <div class="tab-pane fade" id="counter-flux" role="tabpanel" aria-labelledby="counter-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive scrollable-table-container" style="max-height: 490px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" id="ordersPendingTable">
                        <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.82rem; top: 0; z-index: 10;">
                            <tr>
                                <th class="ps-4 py-3" style="width: 15%;">Code Facture</th>
                                <th style="width: 15%;">Date Saisie</th>
                                <th style="width: 25%;">Client Concerné</th>
                                <th style="width: 20%;">Contenu de la commande</th>
                                <th class="text-end" style="width: 13%;">Net à Payer</th>
                                <th class="text-center pe-4" style="width: 12%;">Actions Caisse</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white border-top-0" style="font-size: 0.92rem;">
                            @forelse($commandesEnAttente as $commande)
                                <tr class="pending-order-row align-middle" id="orderRow_{{ $commande->id }}">
                                    <td class="ps-4 font-monospace fw-bold text-dark">{{ $commande->code_facture }}</td>
                                    <td class="text-secondary font-monospace">{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($commande->client)
                                            <div class="fw-bold text-dark text-uppercase">{{ $commande->client->nom_u }}</div>
                                            <small class="text-muted">Épargne actuelle : <span class="font-monospace fw-semibold text-success">{{ number_format($commande->client->solde_u, 0, ',', ' ') }} F</span></small>
                                        @else
                                            <div class="text-muted fw-bold"><i class="bi bi-person-dash me-1"></i> Client de passage</div>
                                            <small class="text-secondary-emphasis small">Vente Comptoir Directe</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate text-secondary uppercase" style="max-width: 220px;" data-bs-toggle="tooltip" title="@foreach($commande->detailVentes as $d){{ $d->produit?->nom_p }} (x{{ $d->quantite }}), @endforeach">
                                            @foreach($commande->detailVentes as $detail)
                                                {{ $detail->produit?->nom_p }}<small class="text-muted font-monospace">(x{{ (float)$detail->quantite }})</small>{{ !$loop->last ? ' + ' : '' }}
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-dark fs-6">{{ number_format($commande->montant_total, 0, ',', ' ') }} <small class="text-muted" style="font-size:0.75rem;">F</small></td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1.5">
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 process-order-btn text-success border-success" 
                                                    data-id="{{ $commande->id }}"
                                                    data-code="{{ $commande->code_facture }}"
                                                    data-total="{{ $commande->montant_total }}"
                                                    data-avance="{{ $commande->montant_verse }}"
                                                    data-clientname="{{ $commande->client ? strtoupper($commande->client->nom_u) : 'CLIENT DE PASSAGE' }}"
                                                    data-balance="{{ $commande->client ? $commande->client->solde_u : 0 }}">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 reject-order-btn text-danger border-danger" data-id="{{ $commande->id }}" data-code="{{ $commande->code_facture }}">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small border-0">
                                        <i class="bi bi-clock-history fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        Absolument aucun bon d'achat n'est mis de côté au comptoir aujourd'hui.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- 🛠️ MODAL DE MODIFICATION ET RÉAJUSTEMENT DE COMMANDE CLIENT -->
<div class="modal fade" id="editClientOrderModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-pencil-square text-warning me-2"></i>Réajustement Logistique du Bon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- En-tête de la facture ciblée -->
                <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 mb-3 border">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block" style="font-size:0.68rem;">Référence Facture :</span>
                        <strong class="text-dark font-monospace" id="editModalOrderCode">CMD-XXXXX</strong>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small text-uppercase fw-bold d-block" style="font-size:0.68rem;">Titulaire du compte :</span>
                        <strong class="text-primary text-uppercase" id="editModalClientName">RICKY</strong>
                    </div>
                </div>

                <!-- ➕ BLOC AJOUT RAPIDE D'UN NOUVEAU PRODUIT MANQUANT -->
                <div class="bg-light p-3 rounded-3 border mb-4">
                    <label class="form-label small fw-bold text-secondary mb-1">Ajouter une boisson manquante au bon :</label>
                    <div class="row g-2">
                        <div class="col-8">
                            <select class="form-select fw-semibold" id="selectAddExtraProduct">
                                <option value="" disabled selected>-- Sélectionner la boisson à rajouter --</option>
                                {{-- Rempli dynamiquement en AJAX par la liste des boissons du dépôt --}}
                            </select>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary w-100 fw-bold" id="btnSubmitExtraProduct">
                                <i class="bi bi-plus-circle-fill"></i> Insérer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TABLEAU DE MODIFICATION DES QUANTITÉS ET SUPPRESSIONS -->
                <div class="table-responsive border rounded-3 mb-3">
                    <table class="table edit-order-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2.5">Désignation Produit</th>
                                <th class="text-center" style="width: 20%;">Quantité</th>
                                <th class="text-end" style="width: 20%;">Prix Unitaire</th>
                                <th class="text-end" style="width: 20%;">Total Ligne</th>
                                <th class="text-center" style="width: 10%;">Retirer</th>
                            </tr>
                        </thead>
                        <tbody id="editModalOrderItemsBody">
                            {{-- Injecté dynamiquement en AJAX selon la commande cliquée --}}
                        </tbody>
                    </table>
                </div>

                <!-- Bilan Financier réajusté en direct -->
                <div class="d-flex justify-content-end align-items-center p-3 bg-dark text-white rounded-3">
                    <span class="text-white-50 small text-uppercase fw-bold me-3" style="font-size:0.75rem;">Nouveau Montant Net :</span>
                    <h4 class="fw-bold m-0 font-monospace text-warning" id="editModalTotalSum">0 FCFA</h4>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 border-top gap-2">
                <button type="button" class="btn btn-outline-secondary font-monospace fw-bold px-4 rounded-3" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success fw-bold px-4 rounded-3 shadow" id="btnSaveOrderModifications">
                    <i class="bi bi-shield-check-fill me-1"></i> Valider et Encaisser
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
$(document).ready(function() {
    
        // ====================================================================
    // ✅ CORRECTION CHIRURGICALE DU CALCUL DE LA DETTE DANS LE SWAL FINANCIER
    // ====================================================================
    $(document).on('click', '.process-order-btn', function() {
        var id = $(this).data('id');
        var code = $(this).data('code');
        var totalFacture = parseFloat($(this).data('total')) || 0; // Ex: 10 000 F
        var avanceAnterieure = parseFloat($(this).data('avance')) || 0; // Toujours 0 pour une commande en ligne
        var clientName = $(this).data('clientname');
        var soldeEpargne = parseFloat($(this).data('balance')) || 0; // Ex: -3 000 F (Si endetté)

        // 💡 ALGORITHME CORRECTEUR DE CAISSE :
        var texteSoldeLigne = "";
        var resteADonner = 0;

        if (soldeEpargne < 0) {
            // 🚨 CAS DU CLIENT ENDETTÉ : La dette (valeur absolue) s'ajoute au prix de la facture !
            var detteActive = Math.abs(soldeEpargne); // Transforme -3000 en 3000
            resteADonner = totalFacture + detteActive - avanceAnterieure;
            
            texteSoldeLigne = `<div class="d-flex justify-content-between mb-1 text-danger"><span>⚠️ Dette antérieure à recouvrer :</span><b class="font-monospace">+ ${detteActive.toLocaleString('fr-FR')} F</b></div>`;
        } else {
            // CAS DU CLIENT AVEC ÉPARGNE POSITIVE OU NEUTRE (Ton code d'origine inchangé)
            var totalDejaMobilise = avanceAnterieure + (soldeEpargne > 0 ? soldeEpargne : 0);
            resteADonner = totalFacture - totalDejaMobilise;
            if (resteADonner < 0) resteADonner = 0;

            texteSoldeLigne = `<div class="d-flex justify-content-between mb-1 text-primary"><span>Montant Solde Épargne utilisé :</span><b class="font-monospace">- ${soldeEpargne.toLocaleString('fr-FR')} F</b></div>`;
        }

        Swal.fire({
            title: '📑 Clôture Financière : ' + code,
            width: '580px',
            html: `
                <div class="text-start border p-3 rounded bg-light mb-3" style="font-size: 0.9rem;">
                    <div class="mb-2 pb-1 border-bottom text-center fw-bold text-primary uppercase">${clientName}</div>
                    <div class="d-flex justify-content-between mb-1"><span>Prix des boissons demandées :</span><b class="font-monospace">${totalFacture.toLocaleString('fr-FR')} F</b></div>
                    
                    <!-- 💡 Injection dynamique du bloc dette ou épargne calculé au-dessus -->
                    ${texteSoldeLigne}
                    
                    <div class="d-flex justify-content-between mb-1 text-success"><span>Ancien acompte versé au dépôt :</span><b class="font-monospace">+ ${avanceAnterieure.toLocaleString('fr-FR')} F</b></div>
                    <hr class="my-1 text-muted">
                    <div class="d-flex justify-content-between fw-bold text-dark fs-6">
                        <span>Complément Cash total à réclamer :</span>
                        <span class="font-monospace text-danger fs-5">${resteADonner.toLocaleString('fr-FR')} FCFA</span>
                    </div>
                </div>
                <div class="text-start">
                    <label class="form-label small fw-bold text-secondary mb-1">Saisir l'argent physique reçu au comptoir :</label>
                    <input type="number" id="swal_complement_cash" class="form-control form-control-lg fw-bold font-monospace text-end text-success" value="0" min="0">
                    <small class="text-muted small mt-1 d-block">Le montant versé doit couvrir l'ardoise pour solder le compte du client.</small>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '✅ Encaisser & Clôturer',
            cancelButtonText: 'Retour',
            preConfirm: () => {
                var val = parseFloat(document.getElementById('swal_complement_cash').value);
                if (isNaN(val) || val < 0) {
                    Swal.showValidationMessage('Veuillez entrer un montant valide.');
                }
                return val;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var complementCash = result.value;

                $.ajax({
                    url: '/gestion/commandes-attente/traiter/' + id,
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        action: 'valider',
                        montant_verse: complementCash 
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Succès !', response.message, 'success');
                            $('#orderRow_' + id).fadeOut(300);
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Échec', xhr.responseJSON?.message || 'Erreur de caisse', 'error');
                    }
                });
            }
        });
    });


    function futurSoldeCalculeText(solde, avance, total) {
        return "calculé en direct par la caisse";
    }
    $(document).on('click', '.valid-order-btn', function(){
        var id = $(this).data('id');
        $.ajax({
            url: '/gestion/commandes-recu/traiter/' + id,
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                action: 'valider',
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Succès !', response.message, 'success');
                    $('#orderRow_' + id).fadeOut(300);
                    setTimeout(function() { location.reload(); }, 1000);
                }
            },
            error: function(xhr) {
                Swal.fire('Échec', xhr.responseJSON?.message || 'Erreur de caisse', 'error');
            }
        });
    });

    $(document).on('click', '.del-order-btn', function() {

        var id = $(this).data('id');
        var code = $(this).data('code');

        Swal.fire({
            title: '🗑️ Supprimer la commande ' + code + ' ?',
            text: "Une Notification sera envoye au client pour l'informer de l'annulation de son bon.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, annuler sa commande',
            cancelButtonText: 'Ignorer',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/gestion/commandes-recu/traiter/' + id,
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        action: 'refuser'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Annulée !', response.message, 'success');
                            $('#orderRow_' + id).fadeOut(300, function() { $(this).remove(); });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    }
                });
            }
        });
    });
    // ==========================================
    // ACTION B : CLIC SUR LE BOUTON REFUSER / ANNULER
    // ==========================================
    $(document).on('click', '.reject-order-btn', function() {

        var id = $(this).data('id');
        var code = $(this).data('code');

        Swal.fire({
            title: '🗑️ Supprimer la commande ' + code + ' ?',
            text: "Les boissons réservées seront instantanément remises dans le stock physique des machines.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, annuler le bon',
            cancelButtonText: 'Laisser en attente',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/gestion/commandes-attente/traiter/' + id,
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        action: 'refuser'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Annulée !', response.message, 'success');
                            $('#orderRow_' + id).fadeOut(300, function() { $(this).remove(); });
                            setTimeout(function() { location.reload(); }, 1000);
                        }
                    }
                });
            }
        });
    });


    // Variable globale de mémorisation du bon en cours d'édition
    var activeEditOrderId = null;

    // ====================================================================
    // 1. OUVERTURE DE LA MODAL ET LECTURE ANALYTIQUE DES LIGNES (AJAX)
    // ====================================================================
    $(document).on('click', '.btn-ouvrir-modification', function() {
        var button = $(this);
        activeEditOrderId = button.data('id');
        
        $('#editModalOrderCode').text(button.data('code'));
        $('#editModalClientName').text(button.data('clientname'));
        
        loadOrderItemsToEditModal(activeEditOrderId);
        loadDepotProductsToSelectInput(); 
        
        $('#editClientOrderModal').modal('show');
    });

    // Fonction maîtresse pour dessiner le contenu modifiable lié à id_produit
    function loadOrderItemsToEditModal(orderId) {
        var body = $('#editModalOrderItemsBody');
        body.html('<tr><td colspan="5" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Analyse de la facture...</td></tr>');

        $.ajax({
            url: '/Distrib237/secretariat/commande/recuperer-edition/' + orderId,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    body.empty();
                    $.each(response.lignes, function(index, item) {
                        var row = `
                            <tr class="align-middle" id="editRow_${item.id_produit}">
                                <td class="ps-3">
                                    <strong class="text-dark d-block text-uppercase" style="font-size:0.85rem;">${item.nom_p}</strong>
                                    <small class="text-muted font-monospace">${item.format_p} • Stock dispos : ${item.stock_actuel}</small>
                                </td>
                                <td>
                                    <!-- Injection rigoureuse de data-pid liée à id_produit -->
                                    <input type="number" class="form-control form-control-sm input-qty-edit change-qty-trigger" 
                                           data-pid="${item.id_produit}" 
                                           data-price="${item.prix_p}" 
                                           data-max="${item.stock_actuel}" 
                                           step="0.25" min="0.25" value="${item.quantite}">
                                </td>
                                <td class="text-end font-monospace fw-bold text-secondary">${parseInt(item.prix_p).toLocaleString('fr-FR')} F</td>
                                <td class="text-end font-monospace fw-bold text-dark json-row-total" id="lineTotal_${item.id_produit}">${parseInt(item.total_ligne).toLocaleString('fr-FR')} F</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-link text-danger p-0 delete-edit-line-btn" data-pid="${item.id_produit}">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </td>
                            </tr>`;
                        body.append(row);
                    });
                    $('#editModalTotalSum').text(parseInt(response.total_global).toLocaleString('fr-FR') + ' FCFA');
                }
            }
        });
    }

    // ====================================================================
    // 2. RECALCUL EN DIRECT ET SECURITE BRUTE DES STOCKS
    // ====================================================================
    $(document).on('change input', '.change-qty-trigger', function() {
        var input = $(this);
        var pId = input.attr('data-pid');
        var price = parseFloat(input.attr('data-price')) || 0;
        var maxStock = parseFloat(input.attr('data-max')) || 0;
        var newQty = parseFloat(input.val()) || 0;

        if (newQty > maxStock) {
            Swal.fire({ title: 'Alerte Stock ! ⚠️', text: "Stock insuffisant au dépôt (" + maxStock + " max).", icon: 'warning', width: '310px' });
            newQty = maxStock;
            input.val(maxStock);
        }

        var totalLigne = newQty * price;
        $('#lineTotal_' + pId).text(totalLigne.toLocaleString('fr-FR') + ' F');

        recalculerNetGlobalModal();
    });

    function recalculerNetGlobalModal() {
        var globalSum = 0;
        $('.change-qty-trigger').each(function() {
            var q = parseFloat($(this).val()) || 0;
            var p = parseFloat($(this).attr('data-price')) || 0;
            globalSum += (q * p);
        });
        $('#editModalTotalSum').text(globalSum.toLocaleString('fr-FR') + ' FCFA');
    }

    // ====================================================================
    // 3. RETIRER UNE LIGNE (SUPPRESSION GRAPHIQUE AVANT ENVOI)
    // ====================================================================
    $(document).on('click', '.delete-edit-line-btn', function() {
        var pId = $(this).attr('data-pid');
        $('#editRow_' + pId).fadeOut(200, function() {
            $(this).remove();
            recalculerNetGlobalModal();
        });
    });

    // ====================================================================
    // 4. ALIMENTATION DU MENU DEROULANT DU RAJOUT D'ARTICLES VIA ID_PRODUIT
    // ====================================================================
    function loadDepotProductsToSelectInput() {
        var select = $('#selectAddExtraProduct');
        $.ajax({
            url: '/Distrib237/secretariat/commande/liste-produits-depot',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    select.empty().append('<option value="" disabled selected>-- Sélectionner la boisson à rajouter --</option>');
                    $.each(response.produits, function(index, prod) {
                        select.append(`<option value="${prod.id_produit}" data-name="${prod.nom_p.toUpperCase()}" data-price="${prod.prix_p}" data-stock="${prod.quantite_p}">${prod.nom_p.toUpperCase()} (${parseInt(prod.prix_p)} F)</option>`);
                    });
                }
            }
        });
    }

    // Insertion instantanée de la boisson ajoutée manuellement
    $('#btnSubmitExtraProduct').on('click', function() {
        var select = $('#selectAddExtraProduct');
        var option = select.find('option:selected');
        var pId = select.val();

        if (!pId) return;

        if ($('#editRow_' + pId).length > 0) {
            Swal.fire({ title: 'Doublon ⚠️', text: "Cette boisson est déjà présente. Modifiez sa quantité.", icon: 'info', width: '310px' });
            return;
        }

        var row = `
            <tr class="align-middle" id="editRow_${pId}">
                <td class="ps-3"><strong class="text-dark d-block text-uppercase" style="font-size:0.85rem;">${option.data('name')}</strong><small class="text-muted font-monospace">Ajout Manuel • Stock : ${option.data('stock')}</small></td>
                <td><input type="number" class="form-control form-control-sm input-qty-edit change-qty-trigger" data-pid="${pId}" data-price="${option.data('price')}" data-max="${option.data('stock')}" step="0.25" min="0.25" value="1.00"></td>
                <td class="text-end font-monospace fw-bold text-secondary">${parseInt(option.data('price')).toLocaleString('fr-FR')} F</td>
                <td class="text-end font-monospace fw-bold text-dark json-row-total" id="lineTotal_${pId}">${parseInt(option.data('price')).toLocaleString('fr-FR')} F</td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0 delete-edit-line-btn" data-pid="${pId}"><i class="bi bi-trash3-fill"></i></button></td>
            </tr>`;
        
        $('#editModalOrderItemsBody').append(row);
        select.val('').trigger('change');
        recalculerNetGlobalModal();
    });

    // ====================================================================
    // 5. VALIDATION FINALE ET PASSE DE RELAIS AU POP-UP D'ENCAISSEMENT
    // ====================================================================
    $('#btnSaveOrderModifications').on('click', function() {
        var finalPanier = [];
        var errorDetected = false;
        
        $('.change-qty-trigger').each(function() {
            var input = $(this);
            var extractedPid = input.attr('data-pid'); // Lecture brute de id_produit
            var currentQty = parseFloat(input.val()) || 0;

            if (!extractedPid || extractedPid === "undefined") {
                errorDetected = true;
                return false; 
            }

            finalPanier.push({
                produit_id: parseInt(extractedPid), 
                quantite: currentQty
            });
        });

        if (errorDetected) {
            Swal.fire({ title: 'Erreur ❌', text: "Un article possède un identifiant corrompu.", icon: 'error', width: '310px' });
            return;
        }

        if (finalPanier.length === 0) {
            Swal.fire({ title: 'Panier Vide ❌', text: "Impossible de valider un bon sans aucun article.", icon: 'error', width: '310px' });
            return;
        }

        // Transmission des réajustements
        $.ajax({
            url: '/Distrib237/secretariat/commande/sauvegarder-ajustements/' + activeEditOrderId,
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                lignes: finalPanier
            },
            success: function(response) {
                if (response.success) {
                    $('#editClientOrderModal').modal('hide');
                }
            },
            error: function(xhr) {
                Swal.fire({ 
                    title: 'Erreur ❌', 
                    text: xhr.responseJSON?.message || "Échec d'ajustement.", 
                    icon: 'error', 
                    width: '310px' 
                });
            }
        });
    });
});
</script>
@endpush


