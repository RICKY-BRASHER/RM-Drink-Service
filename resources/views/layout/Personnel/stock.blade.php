@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE SUPERIEURE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Suivi des Stocks Physiques</h1>
            <small class="text-muted">Quantités disponibles et seuils d'alertes par point de vente</small>
        </div>
        <!-- Bouton d'action pour simuler un approvisionnement -->
        @if(Auth::user()->role_u === 'admin')
            <button class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" id="btnOpenStockModal">
                <i class="bi bi-plus-lg me-2"></i> Réapprovisionner
            </button>
        @endif
    </div>

    <!-- ONGLETS DE SÉLECTION DU DÉPÔT (SÉCURISÉS PAR RÔLE ADMIN / EMPLOYÉ) -->
    <div class="position-relative mb-4 bg-white p-2 border rounded-4 shadow-xs">
        <ul class="nav nav-pills flex-nowrap gap-2 overflow-auto" id="depotStockTabs" role="tablist" style="scrollbar-width: none; -ms-overflow-style: none;">
            <style>#depotStockTabs::-webkit-scrollbar { display: none; }</style>
            
            <!-- 💡 CAS 1 : L'UTILISATEUR EST UN ADMINISTRATEUR (Affichage de TOUS les dépôts) -->
            @if(Auth::user()->role_u === 'admin')
                
                <!-- Onglet Général obligatoire pour l'admin -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold active rounded-3 px-4 filter-depot-btn d-flex align-items-center gap-2" data-depot="all" type="button">
                        <i class="bi bi-globe fs-5"></i> <span>Tous les Dépôts</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill small tab-count" id="count_all">0</span>
                    </button>
                </li>

                <!-- Boucle complète : L'admin voit défiler tous les dépôts de la base de données -->
                @foreach ($ProduitsParDepot as $nomDepot => $listeProduits) 
                    @php $slugDepot = Str::slug($nomDepot); @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold rounded-3 px-4 filter-depot-btn d-flex align-items-center gap-2" data-depot="{{ $slugDepot }}" type="button">
                            <i class="bi bi-building fs-5"></i> <span>{{ $nomDepot }}</span>
                            <span class="badge bg-light text-secondary rounded-pill small tab-count" id="count_{{ $slugDepot }}">
                                {{ count($listeProduits) }}
                            </span>
                        </button>
                    </li>
                @endforeach

            <!-- 💡 CAS 2 : L'UTILISATEUR EST UN EMPLOYÉ (Affichage de son SEUL dépôt d'affectation) -->
            @else
                @foreach ($ProduitsParDepot as $nomDepot => $listeProduits) 
                    {{-- 🔍 Sécurité stricte : On vérifie si l'ID du dépôt de la boucle correspond à l'ID du dépôt de l'employé connecté --}}
                    @if(count($listeProduits) > 0 && $listeProduits->first()->depot_id === Auth::user()->depot_id)
                        @php $slugDepot = Str::slug($nomDepot); @endphp
                        <li class="nav-item" role="presentation">
                            {{-- L'onglet est "active" par défaut pour l'employé puisqu'il est unique --}}
                            <button class="nav-link fw-bold active rounded-3 px-4 filter-depot-btn d-flex align-items-center gap-2" data-depot="{{ $slugDepot }}" type="button">
                                <i class="bi bi-building fs-5"></i> <span>Dépôt : {{ $nomDepot }}</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill small tab-count" id="count_{{ $slugDepot }}">
                                    {{ count($listeProduits) }}
                                </span>
                            </button>
                        </li>
                    @endif
                @endforeach
            @endif

        </ul>
    </div>



    <!-- BLOC TABLEAU : LISTE DES QUANTITÉS EN STOCK -->
    <div class="erp-panel shadow-sm rounded-4 overflow-hidden border">
        <div class="panel-header bg-white border-bottom p-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
            <span class="fw-bold text-dark"><i class="bi bi-boxes text-muted me-2"></i>État des Écritures de Stocks</span>
            <!-- Recherche rapide -->
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control bg-light border-start-0 small text-sm" id="searchStockInput" placeholder="Filtrer par boisson...">
            </div>
        </div>

        <!-- 💡 CONTENEUR SCROLLABLE : Hauteur bloquée à 8-9 lignes max avec scroll molette -->
        <div class="table-responsive scrollable-table-container" style="max-height: 490px; overflow-y: auto;">
            <table class="table table-hover align-middle m-0" id="stockTable">
                <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.82rem; top: 0; z-index: 10;">
                    <tr class="text-muted small text-uppercase font-monospace border-bottom">
                        <th class="ps-4">Dépôt</th>
                        <th>Nom de la Boisson</th>
                        <th>Fournisseur</th>
                        <th>Entree(J)</th>
                        <th>Ventes(J)</th>
                        <th class="text-center">Quantité Physique</th>
                        <th class="text-center">Seuil d'Alerte</th>
                        <th>Statut Critique</th>
                        @if(Auth::user()->role_u === 'admin')
                            <th class="text-center pe-4">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white border-top-0" style="font-size: 0.95rem;">
    
                    <!-- DOUBLE BOUCLE LARAVEL : Injection des lignes physiques réelles -->
                    @foreach ($ProduitsParDepot as $nomDepot => $listeProduits)
                        
                        {{-- 💡 SECURITÉ ABSOLUE : Si c'est un employé, on saute les dépôts qui ne lui appartiennent pas --}}
                        @if(Auth::user()->role_u === 'admin' || (Auth::user()->role_u === 'employe' && count($listeProduits) > 0 && $listeProduits->first()->depot_id === Auth::user()->depot_id))
                            
                            @php $slugDepot = Str::slug($nomDepot); @endphp 
                            
                            @foreach ($listeProduits as $produit)
                                <!-- 💡 Détermination dynamique de l'état critique du stock -->
                                @php 
                                    $isLowStock = $produit->quantite_p <= ($produit->seuil ?? 15);
                                    // si la moitier du stock est inferieur ou egale au seuil, on considere que le stock est presque vide
                                   $isAlmostEmpty = ($produit->quantite_p / 2) <= ($produit->seuil ?? 15);
                                    
                                @endphp

                                <tr class="stock-row align-middle" data-depot="{{ $slugDepot }}" id="stockRow_{{ $produit->id_produit }}">
                                    <!-- Colonne Dépôt -->
                                    <td class="ps-4">
                                        <strong class="text-secondary"><i class="bi bi-geo-alt-fill me-1"></i>{{ $nomDepot }}</strong>
                                    </td>
                                    
                                    <!-- Colonne Boisson -->
                                    <td>
                                        <span class="fw-bold text-dark text-uppercase">{{ $produit->nom_p }}</span> 
                                        <small class="text-muted">({{ $produit->format_p }})</small>
                                    </td>
                                    
                                    <!-- Colonne Fournisseur -->
                                    <td>
                                        <span class="badge bg-light text-primary border border-primary border-opacity-10 fw-bold px-2 py-1">
                                            {{ $produit->fournisseur?->nom_fournisseur ?? 'AUTRES' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Entrées du Jour -->
                                    <td>
                                        <span class="text-center fw-bold font-monospace text-primary">
                                            {{ $produit->details_approvisionnements->sum('quantite_ajoutee') }}
                                        </span>
                                    </td>
                                    
                                    <!-- Ventes du Jour -->
                                    <td>
                                        <span class="text-center fw-bold font-monospace text-dark">
                                            @php
                                                // On fait la somme des casiers/packs vendus aujourd'hui au statut attente ou payer
                                                $totalVentesJour = $produit->detailVentes->sum('quantite');
                                                
                                                // Formatage intelligent anti-décimales : affiche 21 au lieu de 21.00
                                                $ventesAffichage = ($totalVentesJour == floor($totalVentesJour)) ? (int)$totalVentesJour : number_format($totalVentesJour, 2, ',', ' ');
                                            @endphp
                                            
                                            {{ $ventesAffichage }}
                                        </span>
                                    </td>
                                    
                                    <!-- Quantité Physique -->
                                    <td class="text-center fw-bold font-monospace {{ $isLowStock ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($produit->quantite_p, 0, ',', ' ') }} 
                                    </td>
                                    
                                    <!-- Colonne Seuil -->
                                    <td class="text-center font-monospace text-secondary">
                                        {{ number_format($produit->seuil ?? 15, 0, ',', ' ') }} C / P
                                    </td>
                                    
                                    <!-- Colonne Statut -->
                                    <td>
                                        @if($isLowStock)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Stock Faible
                                            </span>
                                        @elseif($isAlmostEmpty)
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Attention
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                                <i class="bi bi-check-circle-fill me-1"></i> Optimal
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Colonne Actions Administrateur -->
                                    @if(Auth::user()->role_u === 'admin')
                                        <td class="text-center pe-4">
                                            <button type="button" class="btn btn-sm btn-icon btn-light border rounded-3 edit-stock-btn border-info" 
                                                    data-id="{{ $produit->id_produit }}" 
                                                    data-depot="{{ $nomDepot }}" 
                                                    data-name="{{ $produit->nom_p }}" 
                                                    data-qty="{{ $produit->quantite_p }}" 
                                                    data-min="{{ $produit->seuil ?? 15 }}">
                                                <i class="bi bi-pencil-square text-primary"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 delete-product-btn text-danger border-danger" 
                                                data-id="{{ $produit->id_produit }}"
                                                data-bs-toggle="tooltip" title="Supprimer la boisson">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                        @endif {{-- 💡 Fin de la condition de sécurité --}}
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>

<!-- MODAL DE REAPPROVISIONNEMENT UNIQUE (CORRIGÉ & ÉPURÉ) -->
<div class="modal fade" id="stockModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            
            <!-- HEADER DU MODAL -->
            <div class="modal-header bg-light border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3"><i class="bi bi-boxes fs-5"></i></span>
                    <h5 class="modal-title fw-bold text-dark m-0">Bon d'Entrée Stock Unique</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- FORMULAIRE PRINCIPAL -->
            <form id="restockForm" >
                @csrf
                <!-- Champ masqué pour stocker l'ID du dépôt sélectionné -->
                <input type="hidden" name="depot_id" id="modal_active_depot_id">

                <div class="modal-body p-0">
                    <!-- ÉTAPE 1 : SELECTION DU FOURNISSEUR -->
                    <div class="p-4 border-bottom bg-white">
                        <label class="form-label small fw-bold text-secondary uppercase tracking-wider mb-2">1. Sélectionner le Fournisseur Titulaire du Camion</label>
                        <select class="form-select form-select-lg fw-bold text-dark border-2 shadow-xs" name="id_fournisseur" id="select_fournisseur_restock" required>
                            <option value="" disabled selected>-- Choisissez le fournisseur (SABC, GUINNESS, KADJI...) --</option>
                            @foreach ($fournisseurs as $fournisseur)
                                <option value="{{ $fournisseur->id_fournisseur }}">{{ strtoupper($fournisseur->nom_fournisseur) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 💡 ZONE COMPACTE DYNAMIQUE (Gérée par le ID bulk-restock-zone) -->
                    <div id="bulk-restock-zone" class="d-none bg-light">
                        <div class="row g-0">
                            
                            <!-- Colonne Gauche : Barre de choix des dépôts -->
                            <div class="col-md-3 bg-white border-end" style="min-height: 400px;">
                                <div class="p-3 bg-light-subtle border-bottom">
                                    <small class="fw-bold text-muted uppercase tracking-wider">2. Choisir le Dépôt</small>
                                </div>
                                <div class="nav flex-column nav-pills p-2 gap-1.5" id="modalDepotTabs" role="tablist">
                                    <!-- Boutons générés dynamiquement par le script jQuery -->
                                </div>
                            </div>

                            <!-- Colonne Droite : Recherche et Grille des produits -->
                            <div class="col-md-9 d-flex flex-column">
                                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center gap-3">
                                    <small class="fw-bold text-secondary uppercase tracking-wider">3. Saisir les quantités physiques reçues</small>
                                    <div class="input-group input-group-sm" style="max-width: 280px;">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" class="form-control bg-light border-start-0" id="searchModalProduct" placeholder="Filtrer parmi les boissons...">
                                    </div>
                                </div>

                                <!-- Emplacement de la grille des produits rafraîchie -->
                                <div class="p-4 overflow-auto flex-grow-1" style="max-height: 380px;">
                                    <div class="row g-2" id="dynamic-stock-rows">
                                        <!-- Inputs de vos boissons injectés par jQuery -->
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- BOUTONS D'ACTIONS DU PIED DU MODAL -->
                <div class="modal-footer bg-light border-0 py-3 px-4 gap-2">
                    <button type="button" class="btn btn-light border rounded-3 px-4 fw-semibold" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" id="btnSubmitRestock" disabled>Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL BOOTSTRAP UNIQUE : AJUSTEMENT DE STOCK / INVENTAIRE -->
<div class="modal fade" id="stockFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light pb-3">
                <h5 class="fw-bold text-dark m-0" id="stockModalTitle">🏭 Ajustement de Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <form id="stockForm">
                    @csrf
                    <input type="hidden" id="input_stock_id">

                    <!-- Boisson concernée (Lecture seule) -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Boisson concernée</label>
                        <input type="text" class="form-control bg-light text-muted fw-bold" id="input_stock_name" readonly>
                    </div>

                    <!-- Dépôt concerné (Lecture seule) -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Point de vente / Dépôt</label>
                        <input type="text" class="form-control bg-light text-muted" id="input_stock_depot" readonly>
                    </div>

                    <!-- Quantité physique -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Quantité Physique Réelle (Blles)</label>
                        <!-- Ajout de l'attribut name pour assurer la liaison native -->
                        <input type="number" class="form-control fw-bold border-2" id="input_stock_qty" name="quantite_p">
                        
                        <div class="invalid-feedback text-danger small mt-1"></div>
                    </div>

                    <!-- Seuil Alerte -->
                    <div class="mb-4">
                        <label class="form-label form-label-custom mb-1">Seuil minimal d'Alerte (Blles)</label>
                        <input type="number" class="form-control border-2 text-danger fw-semibold" id="input_stock_min" name="seuil_alerte">

                        <div class="invalid-feedback text-danger small mt-1"></div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" >Valider</button>
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
       // Calcul automatique et injection des badges numériques dans vos onglets de page
        function updatePageTabCounts() {
            var total = $('.stock-row').length;
            $('#count_all').text(total);
 
        }
        updatePageTabCounts(); // Appel initial au chargement
        // Écouteur de clic sur vos onglets de dépôts principaux
        $('.filter-depot-btn').on('click', function() {
            $('.filter-depot-btn').removeClass('active');
            $(this).addClass('active');
            
            var targetDepot = $(this).data('depot');
            
            if (targetDepot === 'all') {
                $('.stock-row').fadeIn(200); // Réaffiche tout le monde
            } else {
                $('.stock-row').hide();
                $(`.stock-row[data-depot="${targetDepot}"]`).fadeIn(200); // N'affiche que le dépôt ciblé
            }
        });

        // Filtre de recherche par mot-clé de la page principale
        $('#searchStockInput').on('input', function() {
            var query = $(this).val().toLowerCase().trim();
            var activeDepot = $('.filter-depot-btn.active').data('depot');

            $('.stock-row').each(function() {
                var row = $(this);
                var text = row.find('td:nth-child(2)').text().toLowerCase();
                var matchesSearch = text.includes(query);
                var matchesDepot = (activeDepot === 'all' || row.data('depot') === activeDepot);

                if (matchesSearch && matchesDepot) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        });

        // 3. BOUTON MODIFIER : Chargement et pré-remplissage des fiches de stock
        $(document).on('click', '.edit-stock-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var depot = $(this).data('depot');
            var qty = $(this).data('qty');
            var min = $(this).data('min');

            // Configuration de la Modal d'Inventaire
            $('#input_stock_id').val(id);
            $('#input_stock_name').val(name);
            $('#input_stock_depot').val(depot);
            $('#input_stock_qty').val(qty);
            $('#input_stock_min').val(min);

            $('#stockFormModal').modal('show');
        });

        // 4. ACTION POUR LE BOUTON GÉNÉRAL "RÉAPPROVISIONNER"
        $('#btnOpenStockModal').on('click', function() {
            $('#restockForm')[0].reset();
            $('#modal_active_depot_id').val('');
            $('#bulk-restock-zone').addClass('d-none');
            $('#btnSubmitRestock').prop('disabled', true);
            $('#stockModal').modal('show');
        });

        // 2. Sélection du Fournisseur : On bascule sur l'affichage de la zone
        $('#select_fournisseur_restock').on('change', function() {
            var id_fournisseur = $(this).val();
            var tabMenu = $('#modalDepotTabs');
            
            $('#dynamic-stock-rows').empty();
            // 💡 CORRECTION : Utilise bien l'identifiant bulk-restock-zone présent dans le HTML
            $('#bulk-restock-zone').removeClass('d-none'); 

            tabMenu.html('<div class="text-center w-100 py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary"></div></div>');

            // Récupération des dépôts disponibles pour ce fournisseur
            $.ajax({
                url: '/gestion/fournisseur/' + id_fournisseur + '/depots-disponibles',
                type: 'GET',
                success: function(response) {
                    tabMenu.empty();
                    $.each(response.depots, function(index, depot) {
                        tabMenu.append(`
                            <button class="nav-link w-100 text-start fw-bold rounded-3 py-2.5 px-3 border-0 small modal-depot-btn" data-id="${depot.id}" type="button">
                                <i class="bi bi-building me-1.5"></i>${depot.nom_depot}
                            </button>
                        `);
                    });
                }
            });
        });

        // 3. Clic sur un dépôt : Feuille blanche sécurisée et rechargement
        $(document).on('click', '.modal-depot-btn', function() {
            $('.modal-depot-btn').removeClass('active');
            $(this).addClass('active');

            var idDepot = $(this).data('id');
            $('#modal_active_depot_id').val(idDepot); // Stocke le dépôt d'écriture dans le champ caché

            var rowsContainer = $('#dynamic-stock-rows');
            // Vide l'écran pour garantir l'absence de résidus de saisies d'un autre dépôt
            rowsContainer.html('<div class="text-center w-100 py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary mb-2 d-block mx-auto"></div> Extraction des boissons associées...</div>');

            var idFournisseur = $('#select_fournisseur_restock').val();

            // Récupération des produits filtrés
            $.ajax({
                url: `/gestion/fournisseur/${idFournisseur}/depot/${idDepot}/produits`,
                type: 'GET',
                success: function(response) {
                    rowsContainer.empty();
                    
                    if(response.produits.length === 0) {
                        rowsContainer.html('<div class="alert alert-warning border-0 small m-3 w-100">Aucune boisson n\'est enregistrée pour ce fournisseur dans ce dépôt.</div>');
                        $('#btnSubmitRestock').prop('disabled', true);
                        return;
                    }

                    // Affichage propre en grille
                    $.each(response.produits, function(index, produit) {
                        rowsContainer.append(`
                            <div class="col-md-6 modal-product-item" data-name="${produit.nom_p.toLowerCase()}">
                                <div class="d-flex align-items-center justify-content-between p-2.5 border rounded-3 bg-white h-100 shadow-sm">
                                    <div style="width: 65%;" class="text-truncate">
                                        <span class="fw-bold text-dark text-uppercase small" style="font-size: 0.85rem;">${produit.nom_p}</span><br>
                                        <span class="badge bg-light text-primary border font-monospace text-uppercase" style="font-size: 0.7rem;"><i class="bi bi-box-seam me-1"></i>${produit.format_p}</span>
                                    </div>
                                    <div style="width: 35%;">
                                        <input type="number" name="stocks[${produit.id_produit}]" class="form-control form-control-sm text-end font-monospace fw-bold input-restock-qty" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                    $('#btnSubmitRestock').prop('disabled', false);
                }
            });
        });

         // Détection de saisie (Mise en vert instantanée pour repérer les lignes saisies)
        $(document).on('input', '.input-restock-qty', function() {
            var val = parseInt($(this).val()) || 0;
            if (val > 0) { 
                $(this).removeClass('bg-danger-subtle text-danger border-danger fw-bold');
                $(this).closest('.border').removeClass('border-danger bg-danger bg-opacity-10');
                $(this).addClass('bg-success-subtle text-success border-success fw-bold');
                $(this).closest('.border').addClass('border-success bg-success bg-opacity-10');
            } else if( val < 0){
                $(this).removeClass('bg-success-subtle text-success border-success fw-bold');
                $(this).addClass('bg-danger-subtle text-danger border-danger fw-bold');
                $(this).closest('.border').removeClass('border-success bg-success bg-opacity-10');
                $(this).closest('.border').addClass('border-danger bg-danger bg-opacity-10');
            }else{
                $(this).removeClass('bg-danger-subtle text-danger border-danger fw-bold');
                $(this).removeClass('bg-success-subtle text-success border-success fw-bold');
                $(this).closest('.border').removeClass('border-danger bg-danger bg-opacity-10');
                $(this).closest('.border').removeClass('border-success bg-success bg-opacity-10');
            }
        });

        // Moteur de recherche interne au modal (Filtre instantanément la grille du dépôt actif)
        $(document).on('input', '#searchModalProduct', function() {
            var query = $(this).val().toLowerCase().trim();
            $('.modal-product-item').each(function() {
                var name = $(this).data('name');
                if (name.includes(query)) {
                    $(this).removeClass('d-none');
                } else {
                    $(this).addClass('d-none');
                }
            });
        });


        // 5. SOUMISSION DU FORMULAIRE D'AJUSTEMENT
        $('#stockForm').on('submit', function(e) {
            e.preventDefault();

            var id = $('#input_stock_id').val();
            var qty = $('#input_stock_qty').val();
            var min = $('#input_stock_min').val();

            $.ajax({
                url: '/gestion/stocks/modifier/' + id,
                type: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    quantite_p: qty,
                    seuil_alerte: min
                },
                success: function(response) {
                    if (response.success) {
                        window.location.replace(response.redirect);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;

                        // 1. On nettoie proprement les anciennes erreurs
                        $('#stockForm input').removeClass('is-invalid');
                        $('#stockForm .invalid-feedback').text('');

                        // 2. On boucle pour appliquer les bordures rouges et injecter les messages
                        $.each(errors, function(field, messages) {
                            // Détermine l'ID de l'input cible selon la colonne Laravel
                            var inputId = field === 'quantite_p' ? '#input_stock_qty' : '#input_stock_min';
                            var inputField = $(inputId);
                            
                            if (inputField.length) {
                                inputField.addClass('is-invalid');
                                // 💡 Recherche robuste : cherche la div .invalid-feedback dans le même conteneur parent
                                inputField.closest('.mb-3, .mb-4').find('.invalid-feedback').text(messages[0]);
                            }
                        });
                    } else {
                        alert("Une erreur est survenue lors de la validation des stocks.");
                    }
                }


            });
        });
        
        // 6. SOUMISSION AVEC ÉCRAN DE BILAN ET CONFIRMATION PRO (SweetAlert2)
        $('#restockForm').on('submit', function(e) {
        e.preventDefault(); // Bloque l'envoi immédiat

        var form = $(this);
        var nomFournisseur = $('#select_fournisseur_restock option:selected').text();
        var nomDepot = $('.modal-depot-btn.active').text().trim();
        
        var listeBilanHTML = '<div class="text-start border rounded-3 p-3 bg-light" style="max-height: 200px; overflow-y: auto;">';
        var totalBouteilles = 0;
        var aDesProduits = false;

        // 1. ANALYSE DES ENTRÉES POUR CONSTRUIRE LE BILAN VISUEL
        $('.input-restock-qty').each(function() {
            var qty = parseInt($(this).val()) || 0;
            if (qty > 0) {
                aDesProduits = true;
                totalBouteilles += qty;
                
                // Récupère le nom et le format de la boisson depuis la ligne HTML du modal
                var cardBody = $(this).closest('.d-flex');
                var nomBoisson = cardBody.find('.fw-bold').text().trim();
                var formatBoisson = cardBody.find('.badge').text().trim();

                listeBilanHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom border-secondary border-opacity-10 small">
                        <span class="text-dark fw-medium uppercase">${nomBoisson} <small class="text-muted">(${formatBoisson})</small></span>
                        <span class="font-monospace fw-bold text-success">+ ${qty} cs/u</span>
                    </div>`;
            }
        });
        listeBilanHTML += '</div>';

        // 2. SÉCURITÉ : Bloque instantanément si le gérant n'a rien saisi
        if (!aDesProduits) {
            Swal.fire({
                icon: 'warning',
                title: 'Saisie vide',
                text: 'Veuillez inscrire au moins une quantité supérieure à 0 avant de valider.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // 3. AFFICHAGE DE LA FENÊTRE DE BILAN COMPTABLE
        Swal.fire({
            title: '📑 Vérification du Bon d\'Entrée',
            html: `
                <div class="text-start mb-3 bg-primary bg-opacity-10 p-2.5 rounded-3 border border-primary border-opacity-10" style="font-size: 0.9rem;">
                    <p class="m-0 text-dark"><strong>Livreur :</strong> ${nomFournisseur}</p>
                    <p class="m-0 text-dark"><strong>Dépôt Cible :</strong> ${nomDepot}</p>
                </div>
                ${listeBilanHTML}
                <div class="text-end mt-3">
                    <span class="fs-6 text-dark fw-bold">Volume Total : <span class="text-primary font-monospace">${totalBouteilles} produits</span></span>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Vert Succès
            cancelButtonColor: '#6c757d',   // Gris Neutre
            confirmButtonText: '🚀 Approuver & Enregistrer',
            cancelButtonText: 'Modifier',
            reverseButtons: true
        }).then((result) => {
            // 4. SI L'ADMIN CLIQUE SUR APPROUVER, ON ENVOIE L'AJAX
            if (result.isConfirmed) {
                
                var btnSubmit = $('#btnSubmitRestock');
                btnSubmit.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div> Validation...');

                $.ajax({
                    url: '{{ route('stock.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#stockModal').modal('hide');

                            // Rafraîchissement automatique de la page pour actualiser la table de stock
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = xhr.responseJSON?.message || "Une erreur est survenue lors de l'enregistrement.";
                        Swal.fire({
                            icon: 'error',
                            title: 'Échec',
                            text: errorMsg,
                            confirmButtonColor: '#d33'
                        });
                    },
                    complete: function() {
                        btnSubmit.prop('disabled', false).text("Confirmer");
                    }
                });
            }
        });
    });
        // 7. SUPPRESSION D'UNE BOISSON (ADMIN UNIQUEMENT)
        $(document).on('click', '.delete-product-btn', function() {
            var productId = $(this).data('id');
            var row = $(this).closest('tr');

            Swal.fire({
                title: '⚠️ Confirmer la suppression',
                text: "Cette action est irréversible. Êtes-vous sûr de vouloir supprimer cette boisson ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/Distrib237/admin/produits/supprimer/' + productId,
                        type: 'POST',
                        data: {
                            _token: $('input[name="_token"]').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                    updatePageTabCounts(); // Met à jour le compteur après suppression
                                    var toastEl = document.getElementById('registerSuccessToast');
                                    var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
                                    var toastMessage = toastEl.querySelector('span');
                                    toastMessage.textContent = response.message || "L'agent a été supprimé avec succès.";
                                    toast.show();
                                    
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: xhr.responseJSON?.message || "Une erreur est survenue.",
                                confirmButtonColor: '#d33'
                            });
                        }
                    });
                }
            });
        });

    });

</script>
@endpush
