@extends('layout.personnel.depot')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 100vh;">
    
    <!-- 📄 EN-TÊTE DU MODULE -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="bi bi-truck text-primary me-2"></i>Espace Livraisons & Flux Emballages</h4>
            <small class="text-muted font-monospace">Validation des sorties de quai et traçabilité des casiers consignés par lignes brasseries</small>
        </div>
    </div>

    <!-- 🚚 BLOC 1 : FACTURES EN ATTENTE DE SORTIE PHYSIQUE (STATUT 'PAYER') -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark m-0"><i class="bi bi-clock-history text-warning me-2"></i>Commandes prêtes à l'embarquement</h6>
            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-1.5 rounded-pill font-monospace fw-bold">
                {{ count($commandesAPreparer) }} en attente
            </span>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="bg-light font-monospace small text-muted">
                            <th class="ps-3">Code Facture</th>
                            <th>Acheteur / Client</th>
                            <th>Date Règlement</th>
                            <th class="text-center">Mode Paiement</th>
                            <th class="text-center">Action Logistique</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commandesAPreparer as $v)
                            <tr id="rowShipping_{{ $v->id }}">
                                <td class="ps-3 font-monospace fw-bold text-primary">{{ $v->code_facture }}</td>
                                <td>
                                    <strong class="text-uppercase text-dark">{{ $v->client?->nom_u ?? 'Client Comptoir' }}</strong>
                                    <small class="text-muted d-block font-monospace"><i class="bi bi-telephone me-1"></i>{{ $v->client?->tel_u ?? 'N/A' }}</small>
                                </td>
                                <td class="small font-monospace">{{ \Carbon\Carbon::parse($v->updated_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill small font-monospace">
                                        {{ $v->mode_paiement }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <!-- Déclencheur tactile de la modal -->
                                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3 rounded-3 trigger-modal-delivery" 
                                            data-id="{{ $v->id }}" 
                                            data-code="{{ $v->code_facture }}"
                                            data-client="{{ strtoupper($v->client?->nom_u ?? 'Client') }}">
                                        <i class="bi bi-box-seam me-1"></i> Valider la Sortie
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted small">
                                    <i class="bi bi-shield-check display-5 text-success d-block mb-2 opacity-50"></i>
                                    Zéro chargement en attente. Toutes les factures payées ont quitté le quai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 📜 BLOC 2 : REGISTRE CONSOLIDÉ PAR FACTURE -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h6 class="fw-bold text-secondary m-0"><i class="bi bi-journal-check text-success me-2"></i>Registre d'Audit Centralisé des Livraisons</h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size: 0.88rem;">
                    <thead>
                        <tr class="bg-light font-monospace small text-muted">
                            <th class="ps-3">Date</th>
                            <th>Code Facture</th>
                            <th>Acheteur</th>
                            <th>Détails des Restitutions</th>
                            <th class="text-center">Total Rendu</th>
                            <th>Dettes / Surplus Casiers</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historiqueRetours as $h)
                            @php
                                // Calcul mathématique du reste total à rendre sur cette commande
                                $totalResteArendre = max(0, (int)$h->total_achete - (int)$h->total_rendu);// On s'assure que le reliquat ne soit jamais négatif
                            @endphp
                            <tr>
                                <!-- Date de livraison -->
                                <td class="ps-3 small font-monospace text-muted">
                                    {{ \Carbon\Carbon::parse($h->date_livraison)->format('d/m/Y H:i') }}
                                </td>
                                
                                <!-- Numéro de Facture Unique -->
                                <td class="font-monospace text-primary fw-bold">{{ $h->code_facture }}</td>
                                
                                <!-- Nom du Client -->
                                <td class="text-uppercase small fw-bold text-dark">{{ $h->nom_client }}</td>
                                
                                <!-- 🍾 CELLULE DE DÉTAIL DES RENDUS PAR MARQUE -->
                                <td>
                                    <span class="text-secondary font-monospace" style="font-size: 0.82rem;">
                                        <i class="bi bi-arrow-return-left text-success me-1"></i> {{ $h->detail_rendus_text }}
                                    </span>
                                </td>
                                
                                <!-- Volume Global Rendu -->
                                <td class="text-center fw-bold font-monospace text-primary" style="font-size: 0.9rem;">
                                    {{ $h->total_rendu }} cs
                                </td>
                                
                                <!-- 🚨 CELLULE TRICOLORE INDÉPENDANTE : S'ADAPTE CHIRURGICALEMENT AU MIXTE SANS CONFLIT -->
                                <td>
                                    <div class="p-2 bg-light border rounded-3 d-inline-block font-monospace" style="font-size: 0.8rem;">
                                        {{-- 💡 L'écriture {!! !!} permet à Laravel d'interpréter les balises span de couleurs générées par MySQL --}}
                                        {!! $h->detail_restant_text !!}
                                    </div>
                                </td>



                                
                                <!-- État de livraison finale -->
                                <td class="text-center">
                                    <span class="badge bg-success text-white px-2.5 py-1 rounded-pill font-monospace small" style="font-size: 0.72rem;">
                                        LIVRÉ
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted small">
                                    <i class="bi bi-inbox text-muted display-6 d-block mb-1 opacity-40"></i>
                                    Aucune livraison enregistrée dans le registre d'audit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- 📦 MODAL UNIQUE DE COMPTAGE DYNAMIQUE DES CONSIGNES -->
<div class="modal fade" id="modalProcessDeliveryAndVides" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white rounded-top-4 py-3">
                <h6 class="fw-bold m-0"><i class="bi bi-arrow-left-right text-info me-2"></i>Contrôle de Quai : <span id="labelModalInvoiceCode" class="font-monospace text-warning"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Client : <b class="text-dark" id="labelModalClientName"></b>. Renseignez les casiers vides ramenés pour chaque marque.</p>
                
                <form id="formSubmitDeliveryLogistics">
                    <input type="hidden" id="targetDeliveryInvoiceId" name="invoice_id">

                    <!-- 🔄 GÉNÉRATION DYNAMIQUE DES INPUTS SELON LES BRASSERIES CONFIGURÉES -->
                    @foreach($fournisseurs as $fourn)
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase font-monospace mb-1" style="font-size:0.7rem;">
                                CASIERS VIDES {{ strtoupper($fourn->nom_fournisseur) }} :
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold font-monospace text-muted">cs</span>
                                <!-- Le name sous forme de tableau name="rendus[id_fournisseur]" pour peupler directement le contrôleur -->
                                <input type="number" name="rendus[{{ $fourn->id_fournisseur }}]" class="form-control text-center font-monospace fw-bold fs-5 py-1.5" value="0" min="0" required>
                            </div>
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold rounded-3 mt-3 shadow" id="btnSubmitDeliveryProcess">
                        <i class="bi bi-check2-all me-1"></i> Enregistrer la Sortie
                    </button>
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
    // 🚚 OUVERTURE DE LA MODAL ET INITIALISATION DU BON
    // ====================================================================
    $(document).on('click', '.trigger-modal-delivery', function() {
        var btn = $(this);
        var id = btn.data('id');
        var code = btn.data('code');
        var client = btn.data('client');

        $('#targetDeliveryInvoiceId').val(id);
        $('#labelModalInvoiceCode').text(code);
        $('#labelModalClientName').text(client);

        // Remet tous les compteurs à 0 de façon propre
        $('#formSubmitDeliveryLogistics').find('input[type="number"]').val(0);

        $('#modalProcessDeliveryAndVides').modal('show');
    });

    // ====================================================================
    // ⚡ ENVOI DE LA SÉLECTION COMPTABLE VIA AJAX
    // ====================================================================
    $('#formSubmitDeliveryLogistics').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var idVente = $('#targetDeliveryInvoiceId').val();
        var btn = $('#btnSubmitDeliveryProcess');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...');

        $.ajax({
            url: '/Distrib237/caissier/livraisons/valider/' + idVente,
            type: 'POST',
            data: form.serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(response) {
                if (response.success) {
                    $('#modalProcessDeliveryAndVides').modal('hide');
                    
                    $('#rowShipping_' + idVente).fadeOut(400, function() {
                        $(this).remove();
                        alert("Succès : " + response.message);
                        location.reload(); // Rechargement pour actualiser instantanément l'historique par lignes du bas
                    });
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Enregistrer la Sortie');
                alert("Échec : " + (xhr.responseJSON?.message || "Erreur lors de la boucle logistique."));
            }
        });
    });

});
</script>
@endpush
