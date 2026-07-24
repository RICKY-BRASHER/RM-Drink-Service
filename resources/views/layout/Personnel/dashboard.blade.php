@extends('layout.personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- LIGNE 1 : ACTIVITÉ DES VENTES & RÉSUMÉ DES STOCKS -->
    <div class="row g-4 mb-4">
        
        <!-- Bloc Activité des Ventes -->
        <div class="col-xl-8 col-lg-7">
            <div class="erp-panel p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark m-0">Activités de Vente (Journalier)</h5>
                    <span class="badge bg-light text-muted border px-2 py-1">En direct</span>
                </div>
                
                <div class="row g-3">
                    <!-- CA Journalier -->
                    <div class="col-sm-6 col-md-3">
                        <div class="kpi-card text-center border-primary border-opacity-20">
                            <div class="kpi-value text-primary fs-3 fw-bold">{{ number_format($caJournalier, 0, ',', ' ') }}</div>
                            <div class="kpi-title" style="color: #2563eb;">FCFA Encaissés</div>
                            <div class="kpi-footer mt-2"><i class="bi bi-check-circle-fill text-success"></i> Ventes payées</div>
                        </div>
                    </div>
                    <!-- Crédits Clients -->
                    <div class="col-sm-6 col-md-3">
                        <div class="kpi-card text-center border-danger border-opacity-20">
                            <div class="kpi-value text-danger fs-3 fw-bold">{{ $nbDettesJour }}</div>
                            <div class="kpi-title" style="color: #dc2626;">Factures Dettes</div>
                            <div class="kpi-footer mt-2"><i class="bi bi-exclamation-circle-fill text-danger"></i> En attente</div>
                        </div>
                    </div>
                    <!-- Commandes Web -->
                    <div class="col-sm-6 col-md-3">
                        <div class="kpi-card text-center border-success border-opacity-20">
                            <div class="kpi-value text-success fs-3 fw-bold">{{ $nbCommandesWeb }}</div>
                            <div class="kpi-title" style="color: #16a34a;">Commandes Web</div>
                            <div class="kpi-footer mt-2"><i class="bi bi-clock-history text-success"></i> À préparer</div>
                        </div>
                    </div>
                    <!-- Total Volume Saisi -->
                    <div class="col-sm-6 col-md-3">
                        <div class="kpi-card text-center border-dark border-opacity-20">
                            <div class="kpi-value text-dark fs-3 fw-bold">
                                {{ ($volumeVenduesJour == floor($volumeVenduesJour)) ? (int)$volumeVenduesJour : number_format($volumeVenduesJour, 1, ',', ' ') }}
                            </div>
                            <div class="kpi-title" style="color: #0f172a;">Boissons Vendues</div>
                            <div class="kpi-footer mt-2"><i class="bi bi-box-seam"></i> Volume total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc Résumé de l'Inventaire -->
        <div class="col-xl-4 col-lg-5">
            <div class="erp-panel p-4 h-100 bg-white">
                <div class="border-bottom pb-2 mb-3">
                    <h5 class="fw-bold text-dark m-0">Résumé des Stocks</h5>
                </div>
                
                <!-- Quantité totale en stock physique -->
                <div class="d-flex justify-content-between align-items-center p-3 mb-3 bg-light rounded-3 border">
                    <span class="text-uppercase small fw-bold text-secondary font-monospace" style="font-size: 0.75rem;">Boissons en stock</span>
                    <strong class="fs-4 text-dark font-monospace" id="summaryQtyInHand">
                        {{ ($totalStockPhysique == floor($totalStockPhysique)) ? (int)$totalStockPhysique : number_format($totalStockPhysique, 1, ',', ' ') }}
                    </strong>
                </div>

                <!-- Quantité en cours de réapprovisionnement -->
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border">
                    <span class="text-uppercase small fw-bold text-secondary font-monospace" style="font-size: 0.75rem;">Boissons attendues</span>
                    <strong class="fs-4 text-primary font-monospace" id="summaryQtyToReceive">
                        {{ ($totalApprovisionneJour == floor($totalApprovisionneJour)) ? (int)$totalApprovisionneJour : number_format($totalApprovisionneJour, 1, ',', ' ') }}
                    </strong>
                </div>
            </div>
        </div>

    </div>

    <!-- LIGNE 2 : DÉTAILS DU CATALOGUE & COMPTEURS CRITIQUES -->
    <div class="row g-4 mb-4">
        
        <!-- Bloc Détails des Produits et Alertes -->
        <div class="col-md-6 col-xl-4">
            <div class="erp-panel p-4 h-100">
                <div class="panel-header px-0 pt-0 mb-4 bg-transparent border-bottom border-light">
                    <span class="text-uppercase text-muted fw-bold font-monospace" style="font-size: 0.75rem;">Détails du Catalogue</span>
                </div>
                
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small fw-medium"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Boissons en rupture</span>
                        <strong class="text-danger" id="kpiLowStock">{{ $nbRuptures }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small fw-medium"><i class="bi bi-tags-fill text-muted me-2"></i>Catégories actifs</span>
                        <strong class="text-dark" id="kpiItemGroups">4</strong> <!-- SABC, Guinness, UCB, Autres -->
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <span class="text-secondary small fw-medium"><i class="bi bi-grid-fill text-muted me-2"></i>Total références</span>
                        <strong class="text-dark" id="kpiAllItems">{{ $totalReferences }}</strong>
                    </div>
                    
                    <!-- Graphique de rotation calculé dynamiquement -->
                    <div class="text-center pt-2">
                        @php
                            $totalDispo = $totalReferences - $nbRuptures;
                            $tauxDispo = $totalReferences > 0 ? round(($totalDispo / $totalReferences) * 100) : 100;
                        @endphp
                        <div class="d-inline-flex align-items-center justify-content-center position-relative" style="width: 80px; height: 80px;">
                            <div class="spinner-border text-success position-absolute" style="width: 80px; height: 80px; border-width: 6px; transform: rotate(45deg);" role="status"></div>
                            <strong class="text-dark fs-5">{{ $tauxDispo }}%</strong>
                        </div>
                        <small class="text-muted d-block mt-2">Disponibilité du stock par rapport aux références</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloc Produits Vedettes / Les plus vendus -->
        <div class="col-md-6 col-xl-4">
            <div class="erp-panel p-4 h-100">
                <div class="panel-header px-0 pt-0 mb-4 bg-transparent border-bottom border-light">
                    <span class="text-uppercase text-muted fw-bold font-monospace" style="font-size: 0.75rem;">Top 3 Ventes Brasseries</span>
                </div>
                
                <div class="d-flex flex-column gap-3">
                    @forelse($topVentes as $top)
                        @php $venduFmt = ($top->total_vendu == floor($top->total_vendu)) ? (int)$top->total_vendu : number_format($top->total_vendu, 1, ',', ' '); @endphp
                        <div class="p-2.5 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-dark small d-block text-uppercase" style="font-size:0.8rem;">{{ $top->nom_p }}</strong>
                                <small class="text-muted">Format: {{ $top->format_p }}</small>
                            </div>
                            <span class="badge bg-dark rounded-3 px-2.5 py-1.5 font-monospace fw-bold">{{ $venduFmt }} u</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">Aucune boisson sortie ce mois-ci.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bloc Chiffre Mensuel -->
        <div class="col-md-12 col-xl-4">
            <div class="erp-panel p-4 h-100 bg-white">
                <div class="panel-header px-0 pt-0 mb-4 bg-transparent border-bottom border-light">
                    <span class="text-uppercase text-muted fw-bold font-monospace" style="font-size: 0.75rem;">Performances Commerciales</span>
                </div>
                <div class="py-3 text-center">
                    <small class="text-uppercase tracking-wider text-muted font-monospace d-block mb-1">Chiffre d'Affaires Mensuel</small>
                    <h1 class="fw-black text-primary tracking-tight display-6 m-0 fw-bold font-monospace" id="statMonthlySales">{{ number_format($caMensuel, 0, ',', ' ') }} <small class="fs-5 fw-bold">F</small></h1>
                    <div class="mt-4 p-3 bg-primary-subtle rounded-3 border border-primary border-opacity-10" style="background-color: rgba(0,82,255,0.03);">
                        <small class="text-primary d-block fw-bold"><i class="bi bi-graph-up-arrow me-1"></i> Objectif de la Direction</small>
                        <small class="text-muted small">Suivi d'épargne et volume prépayé centralisé</small>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- LIGNE 3 : LISTE COMPLÈTE DE LA CLIENTÈLE FIDÈLE -->
    <div class="row g-4">
        <div class="col-12">
            <div class="erp-panel mb-5">
                <div class="panel-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-heart-fill text-danger me-2"></i>Classement Clientèle Fidèle (Volume d'Achat Global)</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-bold" id="btnRefreshFidelity">Actualiser les fiches</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0" id="fidelityTable">
                        <thead>
                            <tr class="text-muted small text-uppercase font-monospace border-bottom bg-white">
                                <th style="width: 80px;" class="text-center">Rang</th>
                                <th>Nom complet / Établissement</th>
                                <th>Localisation commerciale</th>
                                <th class="text-center">Volume d'achats</th>
                                <th class="text-end pe-4">Montant Total Investi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                           @forelse($clientsFideles as $index => $cf)
                                <tr>
                                    <td class="text-center fw-bold {{ $index === 0 ? 'text-primary' : 'text-secondary' }}">N° {{ $index + 1 }}</td>
                                    
                                    <!-- 💡 Remplacement de telephone_u par tel_u -->
                                    <td><strong class="text-uppercase text-dark">{{ $cf->nom_u }}</strong><br><small class="text-muted font-monospace">Tél: +237 {{ $cf->tel_u }}</small></td>
                                    
                                    <!-- 💡 Remplacement de adresse_u par localisation_u -->
                                    <td><span><i class="bi bi-geo-alt me-1 text-danger"></i>{{ $cf->localisation_u ?? 'Non localisé' }}</span></td>
                                    
                                    <td class="text-center"><span class="badge bg-light text-dark border px-3 py-1.5 fw-bold font-monospace">{{ $cf->total_achats }} Factures</span></td>
                                    <td class="text-end fw-bold font-monospace text-dark pe-4">{{ number_format($cf->capital_investi, 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">Aucun historique client disponible.</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> <!-- Fin de la Ligne 3 -->

</div> <!-- Fin du container-fluid global -->
@endsection

@push('scripts')
<script>
    $(document).ready(function() { 
        // Gestion asynchrone du bouton d'actualisation des KPI
        $('#btnRefreshFidelity').on('click', function() {
            var $btn = $(this);
            
            $btn.prop('disabled', true).text('Calculs en cours...');
            
            setTimeout(function() {
                $btn.prop('disabled', false).text('Actualiser les fiches');
                location.reload(); // Force le rechargement pour mettre à jour les statistiques de la BDD
            }, 1000);
        });
    });
</script>
@endpush
