@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">
    
    <!-- EN-TÊTE CHRONOLOGIQUE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Historique & Archives</h1>
            <small class="text-muted">Traçabilité complète des entrées d'approvisionnements et des factures de ventes du dépôt</small>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-3 small fw-bold font-monospace">
            <i class="bi bi-calendar3 me-2 text-primary"></i>Journal d'Audit Central
        </span>
    </div>

    <!-- 🌐 SATELLITE TACTILE : FILTRAGE DES DÉPÔTS (VISIBLE EXCLUSIVEMENT PAR L'ADMINISTRATEUR) -->
    @if(Auth::user()->role_u === 'admin')
        <div class="d-flex gap-2 overflow-x-auto pb-3 mb-3" style="white-space: nowrap; -webkit-overflow-scrolling: touch;">
            <button type="button" class="btn btn-sm btn-dark px-3 rounded-pill fw-bold filter-depot-btn active" data-depot="all">
                🌍 Tous les Dépôts
            </button>
            @foreach($depots as $d)
                <button type="button" class="btn btn-sm btn-light border px-3 rounded-pill fw-bold filter-depot-btn" data-depot="{{ Str::slug($d->nom_depot) }}">
                    📍 {{ strtoupper($d->nom_depot) }}
                </button>
            @endforeach
        </div>
    @endif

    <!--  Pill d'onglets de navigation d'origine modifiée pour les flux de stocks -->
    <ul class="nav nav-pills mb-4 bg-white p-2 border rounded-3 gap-1" id="historyTabs" role="tablist">
        <li class="nav-item flex-fill text-center" role="presentation">
            <button class="nav-link w-100 fw-bold active filter-history-btn" data-type="all" type="button">
                <i class="bi bi-collection-fill me-2"></i>Toutes les archives
            </button>
        </li>
        <li class="nav-item flex-fill text-center" role="presentation">
            <button class="nav-link w-100 fw-bold filter-history-btn" data-type="facture" type="button">
                <i class="bi bi-receipt me-2"></i>Factures de Ventes
            </button>
        </li>
        <!-- 💡 SÉCURITÉ ACCRUE : Masquage de l'onglet approv pour l'employé -->
        @if(Auth::user()->role_u === 'admin')
            <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 fw-bold filter-history-btn" data-type="approv" type="button">
                    <i class="bi bi-box-seam-fill me-2"></i>Approvisionnements (Entrées)
                </button>
            </li>
        @endif
    </ul>

    <!-- BLOC TABLEAU GÉNERAL D'AUDIT CENTRAL -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
            <span class="fw-bold text-dark"><i class="bi bi-journal-text text-muted me-2"></i>Journal d'Audit Central</span>
            <div class="input-group" style="max-width: 260px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control bg-light border-start-0 small text-sm" id="searchHistoryInput" placeholder="Rechercher par client/réf...">
            </div>
        </div>

        <div class="table-responsive" style="max-height: 490px; overflow-y: auto;">
            <table class="table table-hover align-middle m-0" id="historyTable">
                <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.82rem; top: 0; z-index: 10;">
                    <tr>
                        <th class="ps-4 py-3" style="width: 140px;">Horodatage</th>
                        <th style="width: 160px;">Code Facture / Réf</th>
                        <th style="width: 120px;">Dépôt</th>
                        <th style="width: 150px;">Type d'opération</th>
                        <th>Bénéficiaire / Acteur</th>
                        <th>Détails</th>
                        <th class="text-end" style="width: 140px;">Montant/Qte</th>
                        <th class="text-center" style="width: 100px;">Visualiser</th>
                    </tr>
                </thead>
                <tbody class="bg-white" id="historyTableBody">

                    {{-- 🛒 1. LES FACHURES DE VENTES / SORTIES (COMMUNES ADMIN + EMPLOYE) --}}
                    @foreach($ventes as $v)
                        @php
                            $slugD = Str::slug($v->client?->depot?->nom_depot ?? 'central');
                            
                            // Construction de la chaîne descriptive pour la colonne et pour la modal
                            $txtDetails = "";
                            foreach($v->detailVentes as $d) {
                                $txtDetails .= $d->produit?->nom_p . " (x" . (float)$d->quantite . "), ";
                            }
                            $txtDetails = rtrim($txtDetails, ', ');

                            // Version avec des retours à la ligne HTML <br> propre pour la modal d'aperçu
                            $modalDetails = "";
                            foreach($v->detailVentes as $d) {
                                $modalDetails .= "• " . strtoupper($d->produit?->nom_p) . " : <b>" . (float)$d->quantite . "</b> cs/pack<br>";
                            }
                        @endphp
                        <tr class="history-row align-middle" data-type="facture" data-depot="{{ $slugD }}">
                            <td class="ps-4 font-monospace small text-muted">{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            
                            <!-- 💡 NOUVELLE COLONNE AFFICHANT LE CODE DE LA FACTURE -->
                            <td class="font-monospace fw-bold text-dark">
                                {{ $v->code_facture }}
                            </td>

                            <td><strong class="text-secondary small uppercase"><i class="bi bi-geo-alt-fill me-1"></i>{{ $v->client?->depot?->nom_depot ?? 'Comptoir' }}</strong></td>
                            <td>
                                @if($v->statut === 'payer')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 py-1 px-2.5 rounded-pill fw-bold small">PAYER NON LIVRER</span>
                                @elseif($v->statut === 'annuler' )
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 py-1 px-2.5 rounded-pill fw-bold small">❌ VENTE ANNULER</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 py-1 px-2.5 rounded-pill fw-bold small">LIVRER</span>
                                @endif
                            </td>
                            <td><strong class="text-dark text-uppercase">{{ $v->client?->nom_u ?? 'Client de passage' }}</strong></td>
                            <td class="small text-secondary text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="{{ $txtDetails }}">{{ $txtDetails }}</td>
                            <td class="text-end fw-bold font-monospace text-dark">{{ number_format($v->montant_total, 0, ',', ' ') }} F</td>
                            <td class="text-center">
                                <!-- 💡 Le code de transfert de données a été sécurisé contre les conflits de syntaxe -->
                                <button type="button" class="btn btn-sm btn-light border view-history-btn" 
                                        data-type="Facture de Vente" 
                                        data-title="{{ $v->code_facture }}" 
                                        data-details="Client : <b>{{ $v->client?->nom_u ?? 'Comptoir' }}</b><br>Encaissé par : <b>{{ $v->employe?->nom_u ?? 'Système' }}</b><hr>{{ $modalDetails }}"
                                        {{-- 💡 TRANSMISSION DE L'URL DU PDF (Vaut null si le fichier n'existe pas) --}}
                                        data-pdf="{{ $v->url_pdf }}">
                                    <i class="bi bi-eye-fill text-secondary"></i>
                                </button>

                            </td>
                        </tr> 
                    @endforeach

                    {{-- 📦 2. LES APPROVISIONNEMENTS GENERAUX / ENTRÉES (RÉSERVÉ EXCLUSIVEMENT A L'ADMIN) --}}
                    @if(Auth::user()->role_u === 'admin')
                        @foreach($approvisionnements as $app)
                            @php 
                                $slugD = Str::slug($app->nom_depot); 
                                $modalDetailsHtml = str_replace('|', '<br>', $app->liste_produits_html);
                            @endphp
                            <tr class="history-row align-middle" data-type="approv" data-depot="{{ $slugD }}">
                                <td class="ps-4 font-monospace small text-muted">
                                    {{ \Carbon\Carbon::parse($app->created_at)->format('d/m/Y H:i') }}
                                </td>
                                
                                <td class="font-monospace text-dark fw-bold">
                                    BON-APP-{{ $app->id_app }}
                                </td>

                                <td>
                                    <strong class="text-secondary small text-uppercase">
                                        <i class="bi bi-geo-alt-fill me-1"></i>{{ $app->nom_depot }}
                                    </strong>
                                </td>
                                
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border-danger border-opacity-10 py-1.5 px-3 rounded-pill fw-bold small">
                                        <i class="bi bi-box-seam me-1"></i> ENTRÉE STOCK
                                    </span>
                                </td>
                                
                                <!-- 💡 NOUVEL AFFICHAGE : BRASSERIE / FOURNISSEUR SOURCE -->
                                <td>
                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-10 py-1.5 px-3 rounded-3 fw-bold small text-uppercase">
                                        🏢 {{ $app->nom_fournisseur }}
                                    </span>
                                </td>
                                
                                <td class="small text-muted font-monospace">
                                    Livraison {{ $app->nom_fournisseur }} consolidée globale
                                </td>
                                
                                <td class="text-end fw-bold font-monospace text fs-6">
                                    {{ number_format($app->total_boissons, 0, ',', ' ') }} Produit(s)
                                </td>
                                
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-light border view-history-btn" 
                                            data-type="BON GLOBAL D'ENTRÉE MAGASIN" 
                                            data-title="BON N°{{ $app->id_app }}" 
                                            data-details="Fournisseur : <b>{{ strtoupper($app->nom_fournisseur) }}</b><br>Dépôt de livraison : <b>{{ $app->nom_depot }}</b><br>Volume total reçu : <b>{{ $app->total_boissons }} produits</b><hr><div class='text-start'>{{ $modalDetailsHtml }}</div>" 
                                            data-pdf="{{ $app->url_pdf }}">
                                        <i class="bi bi-eye-fill text-secondary"></i>
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    @endif



                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- MODAL UNIQUE D'APERÇU RAPIDE D'ORIGINE -->
<div class="modal fade" id="historyViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom pb-3">
                <div>
                    <span class="badge bg-dark rounded-pill px-2 py-1 mb-1 font-monospace text-uppercase" style="font-size: 0.65rem;" id="modalOpType">N/A</span>
                    <h5 class="fw-bold text-dark m-0" id="modalDocTitle">Détails de la pièce</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 bg-light bg-opacity-50">
                <div class="p-3 bg-white border rounded-3 shadow-inner">
                    <p class="text-secondary small font-monospace m-0" id="modalContentTarget">
                        <!-- Injection JQuery -->
                    </p>
                </div>
            </div>
            <div class="modal-footer border-top pt-3 bg-light bg-opacity-20">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 px-3 fw-bold" onclick="window.print();"><i class="bi bi-printer me-1"></i> Imprimer</button>
                <button type="button" class="btn btn-secondary btn-sm rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // ====================================================================
    // 🧮 MOTEUR DE DOUBLE-FILTRAGE INTERACTIF COMBINÉ
    // ====================================================================
    function applyCombinedHistoryFilters(text, type, depot) {
        if (typeof type === 'undefined') type = $('.filter-history-btn.active').data('type') || 'all';
        if (typeof depot === 'undefined') depot = $('.filter-depot-btn.active').data('depot') || 'all';
        if (typeof text === 'undefined') text = $('#searchHistoryInput').val().toLowerCase();

        $('#historyTable tbody .history-row').each(function() {
            var row = $(this);
            var rowText = row.text().toLowerCase();
            var rowType = row.data('type');
            var rowDepot = row.data('depot');

            var matchText = rowText.indexOf(text) > -1;
            var matchType = (type === 'all' || rowType === type);
            var matchDepot = (depot === 'all' || rowDepot === depot);

            if (matchText && matchType && matchDepot) {
                row.fadeIn(100);
            } else {
                row.fadeOut(100);
            }
        });
    }

    // 1. RECHERCHE TEXTUELLE SUR L'ENSEMBLE DES COLONNES
    $('#searchHistoryInput').on('keyup', function() {
        var textValue = $(this).val().toLowerCase();
        applyCombinedHistoryFilters(textValue);
    });

    // 2. FILTRAGE PAR ONGLETS (TOUT / FACTURES / APPROV)
    $('.filter-history-btn').on('click', function() {
        $('.filter-history-btn').removeClass('active');
        $(this).addClass('active');
        applyCombinedHistoryFilters();
    });

    // 3. EXCLUSIF ADMIN : FILTRAGE MULTI-DEPOTS DU BANDEAU HORIZONTAL
    $('.filter-depot-btn').on('click', function() {
        $('.filter-depot-btn').removeClass('btn-dark active').addClass('btn-light border');
        $(this).removeClass('btn-light border').addClass('btn-dark active');
        applyCombinedHistoryFilters();
    });

    // 4. APERÇU NET DE LA PIÈCE COMPTABLE DANS LA FENÊTRE MODAL CORRIGÉ
    $(document).on('click', '.view-history-btn', function() {
        var btn = $(this);
        var urlPdf = btn.data('pdf'); // Récupération du lien PDF

        if (urlPdf && urlPdf !== "") {
            // Ouvrir le PDF dans un nouvel onglet
            window.open(urlPdf, '_blank');
        } else {
            // Sinon, afficher la modal de détails
            var type = btn.data('type');
            var title = btn.data('title');
            var details = btn.data('details');

            $('#modalOpType').text(type);
            $('#modalDocTitle').text(title);
            $('#modalContentTarget').html(details);
            $('#historyViewModal').modal('show');
        }
    });



});
</script>
@endpush


