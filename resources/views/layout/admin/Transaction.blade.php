@extends('layout.personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE COMPTABLE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Audit des Flux Financiers</h1>
            <small class="text-muted">Grand livre des recharges de comptes et écritures de caisse</small>
        </div>
        <div class="text-sm-end">
            <span class="badge bg-light text-secondary border rounded-3 px-3 py-2 fw-semibold">
                <i class="bi bi-shield-check text-success me-1"></i> Mode Superviseur Actif
            </span>
        </div>
    </div>

    <!-- CARDS DE STATISTIQUES RAPIDES (KPI COMPTABLES) -->
    <div class="row g-3 mb-4">
        <!-- Volume Général -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold tracking-wider small d-block mb-1">Volume Global Encaissé</small>
                        <h3 class="fw-bold text-dark m-0 font-monospace">{{ number_format($volumeGeneral, 0, ',', ' ') }} <span class="fs-6 text-primary">F</span></h3>
                    </div>
                    <div class="p-3 bg-primary-subtle text-primary rounded-4"><i class="bi bi-currency-exchange fs-4"></i></div>
                </div>
            </div>
        </div>
        <!-- Total Cash -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold tracking-wider small d-block mb-1">Total Espèces (Caisse)</small>
                        <h3 class="fw-bold text-success m-0 font-monospace">{{ number_format($totalEspeces, 0, ',', ' ') }} <span class="fs-6 text-success">F</span></h3>
                    </div>
                    <div class="p-3 bg-success-subtle text-success rounded-4"><i class="bi bi-cash-stack fs-4"></i></div>
                </div>
            </div>
        </div>
        <!-- Total Mobile Money -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold tracking-wider small d-block mb-1">Total Mobile Money (Momo/OM)</small>
                        <h3 class="fw-bold text-info m-0 font-monospace">{{ number_format($totalMobile, 0, ',', ' ') }} <span class="fs-6 text-info">F</span></h3>
                    </div>
                    <div class="p-3 bg-info-subtle text-info rounded-4"><i class="bi bi-phone-vibrate fs-4"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ZONE DE FILTRAGE INTELLIGENTE -->
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
        <div class="row g-2">
            <!-- Recherche textuelle -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-light border-start-0 text-sm" id="searchTxInput" placeholder="Rechercher un client ou caissier...">
                </div>
            </div>
            <!-- Filtre Dépôt -->
            <div class="col-md-4">
                <select class="form-select bg-light fw-semibold text-secondary" id="filterTxDepot">
                    <option value="all">🏢 Tous les Dépôts</option>
                    @foreach($depots as $depot)
                        <option value="{{ Str::slug($depot->nom_depot) }}">{{ $depot->nom_depot }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Filtre Moyen -->
            <div class="col-md-4">
                <select class="form-select bg-light fw-semibold text-secondary" id="filterTxMoyen">
                    <option value="all">💳 Tous les moyens de paiement</option>
                    <option value="especes">💵 Espèces / Cash</option>
                    <option value="momo">📲 MTN Mobile Money</option>
                    <option value="om">🍊 Orange Money</option>
                </select>
            </div>
        </div>
    </div>

    <!-- GRAND TABLEAU DU JOURNAL DE CAISSE CONSOLE -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <!-- En-tête fixe et scrollable à la molette (8-9 lignes max) -->
        <div class="table-responsive scrollable-table-container" style="max-height: 490px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="transactionTable">
                <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.82rem; top: 0; z-index: 10;">
                    <tr>
                        <th class="ps-4 py-3" style="width: 14%;">Horodatage / Date</th>
                        <th style="width: 12%;">Point de Vente</th>
                        <th style="width: 16%;">Client Crédité</th>
                        <th style="width: 12%;">Type / Nature</th>
                        <th class="text-end" style="width: 11%;">Ancien Solde</th>
                        <th class="text-end" style="width: 11%;">Total Facture</th>
                        <th class="text-end" style="width: 11%;">Cash Reçu (Guichet)</th>
                        <th class="text-end" style="width: 11%;">Nouveau Solde</th>
                        <th class="text-end pe-4" style="width: 11%;">Auteur</th>
                    </tr>
                </thead>
                <tbody class="bg-white border-top-0" style="font-size: 0.92rem;">
                    
                    @forelse($recharges as $recharge)
                        @php 
                            $slugDepot = Str::slug($recharge->client?->depot?->nom_depot ?? 'central'); 
                            $isAchatOuDette = in_array($recharge->type_mouvement, ['achat_direct', 'creation_dette']);
                        @endphp
                        <tr class="tx-row align-middle" data-depot="{{ $slugDepot }}" data-moyen="{{ $recharge->moyen_recharge }}">
                            
                            <!-- 1. Horodatage & IP -->
                            <td class="ps-4">
                                <span class="font-monospace text-dark d-block" style="font-size: 0.85rem;">
                                    {{ $recharge->created_at->format('d/m/Y H:i:s') }}
                                </span>
                                <small class="text-muted font-monospace" style="font-size: 0.72rem;">
                                    <i class="bi bi-laptop me-1"></i>IP: {{ $recharge->ip_adresse ?? '127.0.0.1' }}
                                </small>
                            </td>

                            <!-- 2. Référence et Dépôt -->
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1 d-block mb-1 text-center" style="font-size: 0.75rem; width: fit-content;">
                                    {{ $recharge->reference_tx }}
                                </span>
                                <small class="fw-bold text-uppercase text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $recharge->client?->depot?->nom_depot ?? 'Dépôt Central' }}
                                </small>
                            </td>

                            <!-- 3. Client -->
                            <td>
                                <div class="fw-bold text-dark text-uppercase" style="font-size: 0.9rem;">{{ $recharge->client?->nom_u }}</div>
                                
                            </td>

                            <!-- 4. Type / Nature -->
                            <td>
                                @if($recharge->type_mouvement === 'recharge')
                                    <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-10 px-2 py-1 rounded-3">
                                        <i class="bi bi-plus-circle me-1"></i> RECHARGE COMPTE
                                    </span>
                                @elseif($recharge->type_mouvement === 'achat_direct')
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-10 px-2 py-1 rounded-3">
                                        <i class="bi bi-cart-check me-1"></i> ACHAT DIRECT
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-10 px-2 py-1 rounded-3">
                                        <i class="bi bi-exclamation-octagon me-1"></i> ACHAT EN DETTE
                                    </span>
                                @endif
                            </td>

                            <!-- 5. ➕ NOUVELLE COLONNE : Ancien Solde Épargne -->
                            <td class="text-end font-monospace text-secondary fw-semibold">
                                {{ number_format($recharge->solde_avant_r, 0, ',', ' ') }} <small style="font-size:0.75rem;">F</small>
                            </td>

                            <!-- 6. ➕ NOUVELLE COLONNE : Total de la Facture (S'affiche uniquement si Achat ou Dette) -->
                            <td class="text-end font-monospace">
                                @if($isAchatOuDette && $recharge->vente)
                                    <strong class="text-dark">{{ number_format($recharge->vente->montant_total, 0, ',', ' ') }}</strong> <small class="text-muted" style="font-size:0.75rem;">F</small>
                                @else
                                    <span class="text-muted-custom font-monospace opacity-40">—</span>
                                @endif
                            </td>

                            <!-- 7. Cash Physique Reçu au Guichet -->
                            <td class="text-end font-monospace">
                                <span class="fw-bold text-success" style="font-size: 0.95rem;">
                                    + {{ number_format($recharge->montant_r, 0, ',', ' ') }} <small style="font-size:0.75rem;">F</small>
                                </span>
                            </td>

                            <!-- 8. Nouveau Solde Épargne -->
                            <td class="text-end font-monospace fw-bold text-primary" style="font-size: 1rem;">
                                {{ number_format($recharge->solde_apres_r, 0, ',', ' ') }} <small class="text-muted" style="font-size: 0.72rem;">F</small>
                            </td>

                            <!-- 9. Caissier -->
                            <td class="text-end pe-4 font-monospace text-secondary fw-medium">
                                {{ $recharge->employe?->nom_u }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <!-- Colspan adapté de 7 à 9 pour couvrir les nouvelles colonnes de caisse -->
                            <td colspan="9" class="text-center py-5 text-muted small">
                                <i class="bi bi-cash-coin fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                Aucun mouvement monétaire n'a été enregistré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    
    // Écouteur global sur les 3 éléments de filtrage
    $('#searchTxInput, #filterTxDepot, #filterTxMoyen').on('input change', function() {
        var query = $('#searchTxInput').val().toLowerCase().trim();
        var selectedDepot = $('#filterTxDepot').val();
        var selectedMoyen = $('#filterTxMoyen').val();

        $('.tx-row').each(function() {
            var row = $(this);
            
            // 1. Extraction des textes de la ligne pour la recherche textuelle
            var textContent = row.find('td:nth-child(3)').text().toLowerCase() + ' ' + row.find('td:nth-child(7)').text().toLowerCase();
            
            // 2. Évaluation des conditions de tri
            var matchesSearch = textContent.includes(query);
            var matchesDepot  = (selectedDepot === 'all' || row.data('depot') === selectedDepot);
            var matchesMoyen  = (selectedMoyen === 'all' || row.data('moyen') === selectedMoyen);

            // 3. Affichage ou masquage masqué de la ligne
            if (matchesSearch && matchesDepot && matchesMoyen) {
                row.show();
            } else {
                row.hide();
            }
        });
    });

});

</script>
@endpush
