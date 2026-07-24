@extends('layout.client.client')

@section('client_content')
<div class="container py-3">

    <!-- EN-TÊTE PAGE SUIVI -->
    <div class="d-flex justify-content-between align-items-end mb-4 ps-1">
        <div>
            <span class="text-secondary-custom small fw-bold text-uppercase tracking-wider" style="font-size: 0.65rem;">Historique d'achats</span>
            <h5 class="fw-bold m-0 text-white" style="letter-spacing: -0.3px;">Suivi de mes Commandes</h5>
            <small style="color: #71767b; font-size: 0.75rem;">Cliquez sur un bon pour déployer son volet d'actions</small>
        </div>
        
        <button type="button" class="btn btn-cyber-sort" id="btnSortOrders" data-order="desc">
            <i class="bi bi-arrow-down-up me-1.5"></i> <span id="sortLabel">Récent</span>
        </button>
    </div>

    <!-- 📦 CONTENEUR MAÎTRE DES CARTES DE COMMANDES -->
    <div class="d-flex flex-column gap-3" id="ordersMasterContainer">
        
        @forelse($achats as $achat)
            @php
                // 🔍 1. CHARGEMENT ANALYTIQUE DES LIGNES EN BDD POUR LE BILAN LOGISTIQUE
                $lignesLues = DB::table('detail_ventes')
                    ->join('produits', 'detail_ventes.produit_id', '=', 'produits.id_produit')
                    ->where('detail_ventes.vente_id', $achat->id)
                    ->select('detail_ventes.quantite', 'produits.format_p')
                    ->get();

                $totalCasiers = 0;
                $totalPacks = 0;
                $totalBidons = 0;

                foreach($lignesLues as $l) {
                    $fmt = strtolower($l->format_p);
                    if (str_contains($fmt, 'casier')) {
                        $totalCasiers += (float)$l->quantite;
                    } elseif (str_contains($fmt, 'bidon')) {
                        $totalBidons += (float)$l->quantite;
                    } else {
                        $totalPacks += (float)$l->quantite;
                    }
                }

                // Formatage propre anti-décimales superflues (.00)
                $txtCasiers = ($totalCasiers == floor($totalCasiers)) ? (int)$totalCasiers : number_format($totalCasiers, 2, ',', ' ');
                $txtPacks = ($totalPacks == floor($totalPacks)) ? (int)$totalPacks : number_format($totalPacks, 2, ',', ' ');
                $txtBidons = ($totalBidons == floor($totalBidons)) ? (int)$totalBidons : number_format($totalBidons, 2, ',', ' ');

                // 2. ÉTABLISSEMENT DU STATUT ET DES COULEURS FINTECH
                $statutBrut = $achat->statut;
                if ($statutBrut === 'payer') {
                    $borderClass = 'border-green-success'; $badgeClass = 'badge-cyber-success'; $labelBadge = 'VALIDÉ & LIVRÉ';
                } elseif ($statutBrut === 'annuler') {
                    $borderClass = 'border-red-danger'; $badgeClass = 'badge-cyber-danger'; $labelBadge = 'ANNULÉ';
                } else {
                    $borderClass = 'border-blue-active'; $badgeClass = 'badge-cyber-blue'; 
                    $labelBadge = ($statutBrut === 'envoyer') ? 'ENVOYÉ' : 'EN ATTENTE';
                }

                $timestampSort = $achat->created_at->format('YmdHi');
            @endphp

            <!-- Carte de commande interactive -->
            <div class="order-status-card {{ $borderClass }} trigger-inline-volet" data-timestamp="{{ $timestampSort }}" data-id="{{ $achat->id }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="font-monospace fw-bold text-white d-block" style="font-size: 0.95rem;">{{ $achat->code_facture }}</span>
                        <small style="color: #71767b; font-size: 0.7rem;">Saisie le : {{ $achat->created_at->format('d/m/Y à H:i') }}</small>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1.5 fw-bold font-monospace {{ $badgeClass }}" style="font-size: 0.68rem;">
                        {{ $labelBadge }}
                    </span>
                </div>
                
                <!-- 📦 AFFICHAGE DU RECAP LOGISTIQUE BRUT PAR APPAREIL TACTILE -->
                <div class="my-2 py-1.5 px-2 rounded-3" style="background-color: #08090a; border: 1px solid rgba(255,255,255,0.02); font-size: 0.78rem;">
                    <div class="d-flex flex-wrap gap-2 text-white-50 font-monospace">
                        @if($totalCasiers > 0) <span>📦 <strong>{{ $txtCasiers }}</strong> cs</span> @endif
                        @if($totalPacks > 0) <span>🧃 <strong>{{ $txtPacks }}</strong> pack</span> @endif
                        @if($totalBidons > 0) <span>💧 <strong>{{ $txtBidons }}</strong> bidon</span> @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2" style="border-top: 1px solid rgba(255,255,255,0.05);">
                    <div>
                        <span style="color: #71767b; font-size: 0.72rem; display: block;">Montant global :</span>
                        <strong class="text-white font-monospace" style="font-size: 0.88rem;">{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</strong>
                    </div>
                    
                    @if($statutBrut === 'payer')
                        <small class="text-success fw-bold" style="font-size: 0.75rem;"><i class="bi bi-check2-all"></i> Livré ✔️</small>
                    @elseif($statutBrut === 'annuler')
                        <small class="text-danger fw-bold" style="font-size: 0.75rem;">Refusé ❌</small>
                    @else
                        <small class="text-white-50" style="font-size: 0.75rem;">En attente 📦</small>
                    @endif
                </div>

                <!-- 🚨 LE VOLET INLINE SOUS CHAQUE COMMANDE MODIFIÉ POUR TOUS LES STATUTS -->
                <div class="inline-volet-container">
                    <div class="row g-2 text-center pt-3 mt-1" style="border-top: 1px dashed rgba(255, 255, 255, 0.05);">
                        <!-- 💡 Bouton Voir Le Bon disponible sur TOUS les statuts -->
                        <div class="col-6">
                            <button type="button" class="btn btn-inline-action w-100 py-2.5 btn-action-detail" data-id="{{ $achat->id }}" data-code="{{ $achat->code_facture }}" data-total="{{ number_format($achat->montant_total, 0, ',', ' ') }}">
                                <i class="bi bi-eye-fill me-1.5"></i> Voir le bon
                            </button>
                        </div>
                        <div class="col-6">
                            @if($statutBrut === 'payer')
                                <a href="/gestion/caisse/imprimer-pdf/{{ $achat->id }}?format=ticket" target="_blank" class="btn btn-inline-action w-100 py-2.5 text-decoration-none">
                                    <i class="bi bi-printer-fill me-1.5"></i> Reçu PDF
                                </a>
                            @else
                                <button type="button" class="btn btn-inline-action w-100 py-2.5 opacity-50" disabled style="cursor: not-allowed; border-color: rgba(255,255,255,0.02);">
                                    <i class="bi bi-lock-fill me-1.5"></i> Bloqué
                                </button>
                            @endif
                        </div>
                    </div>
                    @if($statutBrut !== 'payer' && $statutBrut !== 'annuler')
                        <div class="alert-inline-msg" style="font-size: 0.7rem; color: #71767b; text-align: center; margin-top: 6px;">🔒 Le reçu officiel PDF s'activera après validation de la secrétaire.</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted card border-0 rounded-4 bg-white bg-opacity-5">
                <i class="bi bi-file-earmark-x display-4 d-block mb-2 opacity-20"></i>
                <p class="m-0 fw-bold text-white">Aucun historique</p>
            </div>
        @endforelse

    </div>
</div>

<!-- 📦 MODAL UNIQUE POUR VISIONNER LE CONTENU DU TICKET EN DIRECT (MOBILE-FIRST COMPACT) -->
<div class="modal fade" id="viewOrderContentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down px-3">
        <div class="modal-content dark-modal-content" style="border-radius: 20px;">
            <div class="modal-header dark-modal-header py-2.5">
                <h6 class="fw-bold m-0 text-white"><i class="bi bi-receipt text-primary me-2"></i>Contenu du Bon</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="text-center mb-3">
                    <span class="font-monospace fw-bold" id="modalOrderCode" style="color: #00bcff; font-size: 0.95rem;">CMD-XXXXX</span>
                </div>
                <!-- Zone d'injection asynchrone des lignes de la commande -->
                <div class="table-responsive" style="max-height: 280px;">
                    <table class="table table-dark table-borderless table-sm align-middle mb-0">
                        <tbody style="font-size: 0.85rem;" id="modalOrderItemsBody">
                            <!-- Rempli en AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="p-3 rounded-4 mt-3" style="background-color: #161719; border: 1px solid rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: #71767b; font-weight: bold; font-size: 0.75rem; text-transform: uppercase;">Montant Net :</span>
                        <strong class="fs-5 font-monospace text-white" id="modalOrderTotalText">0 F</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. GESTION DU DÉPLOIEMENT TACTILE DU VOLET INLINE SOUS LA CARD (ACCORDÉON)
    $('.trigger-inline-volet').on('click', function(e) {
        // Sécurité : évite de refermer le volet si on clique sur un bouton interne
        if ($(e.target).closest('.btn-inline-action, a').length) return;
        
        var currentCard = $(this);
        var volet = currentCard.find('.inline-volet-container');
        
        $('.inline-volet-container').not(volet).slideUp(250);
        volet.slideToggle(250);
    });

    // ====================================================================
    // 2. ÉCOUTEUR "VOIR LE BON" -> CHARGEMENT ASYNCHRONE DES ARTICLES EN BDD
    // ====================================================================
    $('.btn-action-detail').on('click', function(e) { 
        e.stopPropagation(); // Bloque la remontée du clic à la carte parent
        
        var id = $(this).data('id');
        var code = $(this).data('code');
        var total = $(this).data('total');

        // Pré-remplissage des entêtes de la modal
        $('#modalOrderCode').text(code);
        $('#modalOrderTotalText').text(total + ' FCFA');
        
        var body = $('#modalOrderItemsBody');
        // Animation d'attente (Spinner) pendant le chargement réseau
        body.html('<tr><td colspan="3" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Chargement des bouteilles...</td></tr>');
        
        $('#viewOrderContentModal').modal('show');

        // Requête API AJAX pour récupérer l'état des lignes du detail_ventes
        $.ajax({
            url: '/Distrib237/client/recuperer-details-bon/' + id,
            type: 'GET',
            success: function(response) {
                if (response.success && response.lignes.length > 0) {
                    body.empty();
                    // Construction dynamique des lignes reçues du serveur
                    $.each(response.lignes, function(index, item) {
                        var row = `
                            <tr class="border-bottom border-dark align-middle">
                                <td class="py-2.5"><strong>${item.nom_p.toUpperCase()}</strong><br><small style="color:#71767b;">${parseInt(item.prix_p).toLocaleString('fr-FR')} F</small></td>
                                <td class="text-center"><span class="badge bg-dark border text-white px-2 py-1">${item.quantite_affichee}</span></td>
                                <td class="text-end fw-bold font-monospace text-white">${parseInt(item.total_ligne).toLocaleString('fr-FR')} F</td>
                            </tr>`;
                        body.append(row);
                    });
                } else {
                    body.html('<tr><td colspan="3" class="text-center py-4 text-danger">Aucun article trouvé dans cette commande.</td></tr>');
                }
            },
            error: function() {
                body.html('<tr><td colspan="3" class="text-center py-4 text-danger">Erreur réseau : Impossible de lire detail_ventes.</td></tr>');
            }
        });
    });

    // ====================================================================
    // 3. MOTEUR ALGORITHMIQUE DE TRI PAR TIMESTAMP (RÉCENT / ANCIEN)
    // ====================================================================
    $('#btnSortOrders').on('click', function() {
        var button = $(this);
        var container = $('#ordersMasterContainer');
        var cards = $('.order-status-card').get();
        var currentOrder = button.attr('data-order');

        cards.sort(function(a, b) {
            var valA = parseInt($(a).attr('data-timestamp'));
            var valB = parseInt($(b).attr('data-timestamp'));
            return (currentOrder === 'desc') ? (valA - valB) : (valB - valA);
        });

        $.each(cards, function(index, card) { container.append(card); });

        if (currentOrder === 'desc') {
            button.attr('data-order', 'asc'); 
            $('#sortLabel').text('Ancien');
        } else {
            button.attr('data-order', 'desc'); 
            $('#sortLabel').text('Récent');
        }
    });
});
</script>
@endpush
