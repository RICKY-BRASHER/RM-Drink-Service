@extends('layout.client.client')

@section('content')
<div class="container-fluid p-0">

    <!-- ENTÊTE DE SÉCURITÉ -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Mes Transactions & Épargne</h1>
            <small class="text-muted">Déclarez vos versements financiers et auditez vos mouvements de trésorerie</small>
        </div>
        <span class="badge bg-light text-dark border font-monospace py-2 px-3 fw-bold fs-6">
            Mon Solde : {{ number_format(auth()->user()->solde_u, 0, ',', ' ') }} F
        </span>
    </div>

    <div class="row g-4">
        <!-- BLOC GAUCHE : FORMULAIRE DE DÉCLARATION -->
        <div class="col-xl-4 col-lg-5">
            <div class="erp-panel p-4 shadow-sm bg-white">
                <div class="panel-header bg-transparent border-0 px-0 pt-0 pb-2 mb-2 fw-bold text-dark">
                    <i class="bi bi-send-plus-fill text-primary me-2"></i>Déclarer un rechargement
                </div>
                
                <form id="clientRechargeForm" method="POST" action="#">
                    @csrf
                    <!-- Montant -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Montant versé (FCFA)</label>
                        <input type="number" class="form-control border-2 fw-bold text-success" name="montant_epargne" id="input_amount" placeholder="Minimum : 5 000 F" min="5000" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <!-- Référence Transaction -->
                    <div class="mb-4">
                        <label class="form-label form-label-custom mb-1">Réf / ID Mobile Money (OM/MTN)</label>
                        <input type="text" class="form-control font-monospace border-2" name="ref_momo" id="input_ref" placeholder="Ex: OM_9483012" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm text-uppercase small font-monospace">
                        <i class="bi bi-wallet2 me-1"></i> Soumettre à la direction
                    </button>
                </form>
            </div>
        </div>

        <!-- BLOC DROIT : HISTORIQUE DES TRANSACTIONS FINANCIÈRES -->
        <div class="col-xl-8 col-lg-7">
            <div class="erp-panel shadow-sm">
                <div class="panel-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-clock-history text-muted me-2"></i>Registre de mes Écritures Financières</span>
                    <!-- Recherche textuelle interne -->
                    <input type="text" class="form-control bg-light small" id="searchTxInput" placeholder="🔍 Rechercher une référence..." style="max-width: 200px; height: 32px; font-size: 0.8rem; border-radius: 8px;">
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" id="txTable">
                        <thead>
                            <tr class="text-muted small text-uppercase font-monospace border-bottom bg-light">
                                <th>Date</th>
                                <th>Type d'opération</th>
                                <th>ID Référence</th>
                                <th class="text-end">Impact Solde</th>
                                <th class="text-center">Statut Certification</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <!-- Flux 1 -->
                            <tr class="tx-row">
                                <td class="font-monospace small text-muted">Aujourd'hui, 11:05</td>
                                <td><span class="fw-semibold small text-dark">Dépôt Épargne (Momo)</span></td>
                                <td class="font-monospace text-secondary small">OM_9483012</td>
                                <td class="text-end fw-bold text-success font-monospace">+50 000 F</td>
                                <td class="text-center">
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 py-1.5 px-3 rounded-pill fw-bold small">
                                        ⏳ Attente Direction
                                    </span>
                                </td>
                            </tr>
                            <!-- Flux 2 -->
                            <tr class="tx-row">
                                <td class="font-monospace small text-muted">14 Juillet, 15:20</td>
                                <td><span class="fw-semibold small text-dark">Débit Facture Achat</span></td>
                                <td class="font-monospace text-secondary small">CMD-WEB#3412</td>
                                <td class="text-end fw-bold text-danger font-monospace">-15 600 F</td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                        ✅ Certifié / Figé
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Filtrage en direct
        $('#searchTxInput').on('keyup', function() {
            var val = $(this).val().toLowerCase();
            $('#txTable tbody .tx-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });

        // Simulation de la validation du formulaire
        $('#clientRechargeForm').on('submit', function(e) {
            e.preventDefault();
            alert("📲 Succès : Votre déclaration de versement de " + parseFloat($('#input_amount').val()).toLocaleString() + " F a été transmise au Super-Admin pour contrôle.");
            this.reset();
        });
    });
</script>
@endpush
