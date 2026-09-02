@extends('layout.client.client')

@section('client_content')
<div class="container py-3">

    <!-- EN-TÊTE BIENVENUE & INTERFACE LOGISTIQUE -->
    <div class="mb-3 ps-1">
        <span class="text-secondary-custom small fw-bold text-uppercase tracking-wider" style="font-size: 0.65rem;">Catalogue boissons</span>
        <h5 class="fw-bold m-0 text-white" style="letter-spacing: -0.3px;">Passer une Commande</h5>
        <small style="color: #71767b; font-size: 0.75rem;">Dépôt d'approvisionnement : <strong class="text-white">{{ auth()->user()->depot->nom_depot }}</strong></small>
    </div>

    <!-- 🏷️ VOLET DES FOURNISSEURS EN SCROLL HORIZONTAL CORRIGÉ -->
    <div class="supplier-scroll-wrapper mb-4">
        <!-- Onglet racine toujours présent -->
        <div class="supplier-chip active" data-filter="all">
            <i class="bi bi-globe2"></i> Tout voir
        </div>
        
        <!-- Boucle corrigée pour générer dynamiquement les brasseurs de la BDD -->
        @foreach($fournisseurs as $fourn)
            @php
                // Extraction du nom de la colonne de votre table fournisseurs
                $nomF = strtolower($fourn->nom_fournisseur);
                $iconeF = 'bi-bezier'; // Icône par défaut
                
                if (str_contains($nomF, 'guinness')) { 
                    $iconeF = 'bi-award-fill'; 
                } elseif (str_contains($nomF, 'ucb') || str_contains($nomF, 'kadji')) { 
                    $iconeF = 'bi-shield-shaded'; 
                } elseif (str_contains($nomF, 'eau') || str_contains($nomF, 'jus') || str_contains($nomF, 'tangui')) { 
                    $iconeF = 'bi-droplet-fill'; 
                }
            @endphp
            
            <!-- 💡 CORRECTION ICI : Remplacement de ->id par ->id_fournisseur -->
            <div class="supplier-chip" data-filter="fourn_{{ $fourn->id_fournisseur }}">
                <i class="bi {{ $iconeF }}"></i> {{ strtoupper($fourn->nom_fournisseur) }}
            </div>
        @endforeach
    </div>


    <!-- 📦 GRILLE DE PRODUITS PREMIUM PLUGGÉE SUR LA BDD (MOBILE-FIRST : 2 COLONNES) -->
    <div class="row g-2 mb-5" id="productCatalogGrid">
        @forelse($boissons as $boisson)
            @php
                $formatBrut = strtolower($boisson->format_p);
                $quantiteBrute = (float)$boisson->quantite_p;
                
                // 💡 ALGORITHME ANTI-DÉCIMALES INUTILES : 21 au lieu de 21,00
                $stockAffiche = ($quantiteBrute == floor($quantiteBrute)) ? (int)$quantiteBrute : number_format($quantiteBrute, 2, ',', ' ');

                // DÉTECTION LOGISTIQUE DES TYPES ET ICÔNES D'EMBALLAGES
                if (str_contains($formatBrut, 'casier')) {
                    $suffixeAffiche = 'cs';
                    $iconeEmballage = 'bi-box-seam-fill'; 
                    $badgeFormat = str_contains($formatBrut, '24') ? 'Cs-24' : 'Cs-12';
                } 
                elseif (str_contains($formatBrut, 'bidon')) {
                    $suffixeAffiche = 'bidon';
                    $iconeEmballage = 'bi-droplet-half'; 
                    $badgeFormat = 'Bidon';
                } 
                else {
                    $suffixeAffiche = 'pack';
                    $iconeEmballage = 'bi-boxes'; 
                    
                    if (str_contains($formatBrut, '12')) {
                        $badgeFormat = 'Pack-12';
                    } elseif (str_contains($formatBrut, '6')) {
                        $badgeFormat = 'Pack-6';
                    } else {
                        $badgeFormat = 'Pack';
                    }
                }

                // Liaison de la carte au filtre de son fournisseur (fournisseur_id de votre table produits)
                $fkFournisseur = $boisson->fournisseur_id ?? 'all';
            @endphp

            <!-- Carte Produit Dynamique Complètement Épurée -->
            <!-- 💡 CORRECTION DE LA LIGNE D'OUVERTURE DE LA CARTE PRODUIT -->
                <div class="col-6 product-item-box" data-supplier="fourn_{{ $boisson->id_fournisseur }}">

                <div class="product-card-cyber">
                    <span class="format-badge-cyber">{{ $badgeFormat }}</span>
                    <div>
                        <div class="product-icon-cyber">
                            <i class="bi {{ $iconeEmballage }}"></i>
                        </div>
                        <h6 class="product-title-cyber">{{ strtoupper($boisson->nom_p) }}</h6>
                        <small style="color: #71767b; font-size: 0.68rem; display: block;">
                            En Stock : {{ $stockAffiche }} {{ $suffixeAffiche }}
                        </small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2" style="border-top: 1px solid rgba(255,255,255,0.05);">
                        <span class="fw-bold text-white font-monospace" style="font-size: 0.88rem;">
                            {{ number_format($boisson->prix_p, 0, ',', ' ') }} F
                        </span>
                        <button type="button" class="btn-circle-add open-volume-modal" 
                                data-id="{{ $boisson->id_produit }}"
                                data-name="{{ strtoupper($boisson->nom_p) }}" 
                                data-price="{{ $boisson->prix_p }}"
                                data-suffixe="{{ $suffixeAffiche }}">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-box-seam display-4 d-block mb-3 opacity-20"></i>
                <p class="m-0 fw-bold text-white">Dépôt vide</p>
                <small style="color: #71767b;">Aucun produit disponible dans ce secteur.</small>
            </div>
        @endforelse
    </div>


    <!-- 📱 LE BOUTON TACTILE FLOTTANT DU PANIER MOBILE -->
    <button type="button" class="floating-cart-trigger" id="openCartSummaryBtn">
        <i class="bi bi-basket3 position-relative" style="font-size: 1.4rem;">
            <!-- 💡 INITIALISATION EN BLADE POUR LE RECHARGEMENT -->
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-circle bg-danger border border-dark fw-bold" id="cartBadgeCount" style="font-size: 0.62rem; padding: 3px 5px;">
                {{ $lignesPanier ? count($lignesPanier) : 0 }}
            </span>
        </i>
    </button>

</div>

<!-- 📦 MODAL A : AJUSTEUR DE VOLUME CASIER DYNAMIQUE -->
<div class="modal fade" id="volumeConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm px-3">
        <div class="modal-content dark-modal-content">
            <div class="modal-header dark-modal-header pb-2">
                <h6 class="fw-bold m-0 text-white"><i class="bi bi-sliders text-primary me-2"></i>Ajuster volume</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <h6 class="fw-bold text-center font-monospace text-uppercase tracking-wide mb-3" id="targetDrinkName" style="color: #00bcff;">BOISSON</h6>
                
                <form id="localCartSubmitForm">
                    <!-- Les champs cachés indispensables pour stocker les infos du produit dans le panier JS -->
                    <input type="hidden" id="modalDrinkId">
                    <input type="hidden" id="modalDrinkPrice">
                    
                    <div class="mb-3">
                        <!-- 💡 LE LIBELLÉ DYNAMIQUE ICI (GÉRÉ PAR JAVASCRIPT) -->
                        <label class="form-label small fw-bold" style="color: #71767b;" id="modalQtyLabel">Nombre d'unités :</label>
                        <input type="number" class="form-control form-control-lg text-center fw-bold font-monospace bg-dark text-white border-secondary" id="targetDrinkQty" step="0.25" min="0.25" value="1">
                        <small class="text-center d-block mt-2" style="font-size: 0.72rem; color: #71767b;">Fractions autorisées : 0.25, 0.50, 0.75 ou entiers.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold" style="border-radius: 12px;">Ajouter au Panier</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- 🛒 MODAL B : RECAPITULATIF DU PANIER DE COMMANDE -->
<div class="modal fade" id="cartSummaryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content dark-modal-content" style="border-radius: 20px 20px 0 0;">
            <div class="modal-header dark-modal-header">
                <h6 class="fw-bold m-0 text-white"><i class="bi bi-cart-check text-primary me-2"></i>Ma Commande Distante</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <table class="table table-dark table-borderless table-sm align-middle mb-0">
                    <tbody style="font-size: 0.85rem;" id="localCartItemsList">
                        @if($lignesPanier && count($lignesPanier) > 0)
                            @foreach($lignesPanier as $ligne)
                                @php
                                    $fmt = strtolower($ligne->format_p);
                                    $sfx = str_contains($fmt, 'casier') ? 'cs' : (str_contains($fmt, 'bidon') ? 'bidon' : 'pack');
                                    $qteFmt = ($ligne->quantite == floor($ligne->quantite)) ? (int)$ligne->quantite : number_format($ligne->quantite, 2, ',', ' ');
                                @endphp
                                <!-- 💡 CORRECTION ID DE LA LIGNE TR -->
                                <tr class="border-bottom border-dark align-middle" id="cartRow_{{ $ligne->produit_id }}">
                                    <td class="py-2"><strong>{{ strtoupper($ligne->nom_p) }}</strong><br><small style="color: #71767b;">{{ number_format($ligne->prix_p, 0, ',', ' ') }} F</small></td>
                                    <td class="text-center"><span class="badge bg-dark border text-white px-2 py-1">{{ $qteFmt }} {{ $sfx }}</span></td>
                                    <td class="text-end fw-bold font-monospace text-white">{{ number_format($ligne->total_ligne, 0, ',', ' ') }} F</td>
                                    <td class="text-center ps-2">
                                        <!-- 💡 CORRECTION CLASSE ET DATA-ID SUR LE BOUTON CORBEILLE -->
                                        <button type="button" class="btn text-danger p-1 remove-local-item-btn" data-id="{{ $ligne->produit_id }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                
                <div class="p-3 rounded-4 mt-3" style="background-color: #161719; border: 1px solid rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span style="color: #71767b; font-weight: bold; font-size: 0.8rem; text-transform: uppercase;">Estimation Net :</span>
                        <strong class="fs-5 font-monospace text-white" id="localCartTotalSum">{{ number_format($totalBrouillon ?? 0, 0, ',', ' ') }} FCFA</strong>
                    </div>
                    <!-- 💡 LE BOUTON DE FINALISATION ENCOUCHÉ SUR SON ID BACKEND -->
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold" id="btnFinalizeClientOrder" style="border-radius: 12px; font-size: 0.95rem;">
                        <i class="bi bi-shield-lock-fill me-1"></i> Envoyer la commande
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // ====================================================================
    // MOTEUR DE FILTRAGE DU VOLET HORIZONTAL DES FOURNISSEURS (TACTILE)
    // ====================================================================
    $('.supplier-chip').on('click', function() {
        // Gestion de la classe active sur l'onglet cliqué
        $('.supplier-chip').removeClass('active');
        $(this).addClass('active');

        var filterValue = $(this).data('filter');

        // Animation d'apparition/disparition des cartes boissons
        if (filterValue === 'all') {
            $('.product-item-box').hide().fadeIn(300);
        } else {
            $('.product-item-box').hide();
            $('.product-item-box[data-supplier="' + filterValue + '"]').fadeIn(300);
        }
    });

        // ====================================================================
    // 🔘 ACTION : CLIC SUR "+" -> ADAPTATION DU LIBELLÉ ET OUVERTURE MODAL
    // ====================================================================
    $(document).on('click', '.btn-circle-add', function() {
        var button = $(this);
        
        // Extraction rigoureuse des attributs data- du bouton cliqué
        var id = button.data('id');
        var name = button.data('name');
        var price = button.data('price');
        var suffixe = button.data('suffixe');

        // Injection immédiate dans les champs cachés du formulaire du modal
        $('#modalDrinkId').val(id);
        $('#modalDrinkPrice').val(price);
        $('#targetDrinkName').text(name).attr('data-suffixe', suffixe);
        
        // On remet la quantité par défaut à 1 casier / pack / bidon
        $('#targetDrinkQty').val('1.00');

        // Adaptation sémantique du libellé selon l'emballage
        var labelText = "Nombre de casier(s) :";
        if (suffixe === 'pack') labelText = "Nombre de pack(s) :";
        else if (suffixe === 'bidon') labelText = "Nombre de bidon(s) :";

        $('#modalQtyLabel').text(labelText);
        
        // Ouverture graphique de la fenêtre
        $('#volumeConfigModal').modal('show');
    });



    // ====================================================================
    // 🛒 CLIC SUR LE PANIER FLOTTANT
    // ====================================================================
    $('#openCartSummaryBtn').on('click', function() {
        var currentCount = parseInt($('#cartBadgeCount').text()) || 0;
        if (currentCount === 0) {
            // 💡 Alerte Panier Vide Mobile-First
            Swal.fire({
                title: 'Panier vide',
                text: "Veuillez d'abord sélectionner des boissons.",
                icon: 'info',
                width: '310px',
                confirmButtonColor: '#0052ff',
                confirmButtonText: 'D’accord'
            });
            return false;
        }
        $('#cartSummaryModal').modal('show');
    });

    // ====================================================================
    // 2. SOUMISSION DU FORMULAIRE -> INSERTION INTERACTIVE EN BDD (AJAX)
    // ====================================================================
    $('#localCartSubmitForm').on('submit', function(e) {
        e.preventDefault();

        var pId = $('#modalDrinkId').val();
        var qty = parseFloat($('#targetDrinkQty').val()) || 0;

        $.ajax({
            url: "{{ route('client.ajout.produit') }}",
            type: 'POST',
            data: {
                produit_id: pId,
                quantite: qty
            },
            success: function(response) {
                if (response.success) {
                    $('#cartBadgeCount').text(response.badge_count);
                    $('#volumeConfigModal').modal('hide');
                    
                    // 💡 Notification Succès Épurée et automatique (sans bouton pour aller vite)
                   
                    setTimeout(function() {
                        location.reload(); 
                    }, 1000);
                }
            },
            error: function(xhr) {
                // 💡 Alerte Erreur Serveur Mobile-First
                Swal.fire({
                    title: 'Action refusée',
                    text: xhr.responseJSON?.message || "Impossible d'ajouter l'article.",
                    icon: 'error',
                    width: '310px',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Corriger'
                });
            }
        });
    });

    // ====================================================================
    // 🗑️ RETIRER UN PRODUIT EN BASE DE DONNÉES EN DIRECT VIA AJAX
    // ====================================================================
    $(document).on('click', '.remove-local-item-btn', function() {
        var button = $(this);
        var pId = button.data('id'); 
        var row = $('#cartRow_' + pId); 

        // 💡 Boîte de confirmation Tactile Épurée (Largeur bridée à 310px)
        
                $.ajax({
                    url: '/Distrib237/client/supprimer-produit/' + pId,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(250, function() {
                                $(this).remove();
                                
                                $('#cartBadgeCount').text(response.badge_count);
                                $('#localCartTotalSum').text(response.nouveau_total.toLocaleString('fr-FR') + ' FCFA');

                                if (response.badge_count === 0) {
                                    $('#cartSummaryModal').modal('hide');
                                    
                                    

                                    setTimeout(function() {
                                        location.reload(); 
                                    }, 500);
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Erreur réseau',
                            text: "Impossible de joindre le serveur de caisse.",
                            icon: 'error',
                            width: '310px',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
    });

    // ====================================================================
    // 🛰️ TRANSMISSION FINALE DE LA COMMANDE AU SECRÉTARIAT (AJAX)
    // ====================================================================
    // ====================================================================
    // 🛰️ TRANSMISSION FINALE AVEC SÉCURITÉ DE SEUIL DE DETTE (AJAX)
    // ====================================================================
    $('#btnFinalizeClientOrder').on('click', function() {
        var btn = $(this);
        
        // 💡 RÉCUPÉRATION DU SOLDE DU CLIENT DEPUIS LA SESSION LARAVEL
        // Si le solde est négatif, sa valeur absolue représente la dette active
        var soldeClient = parseFloat("{{ auth()->user()->solde_u }}") || 0;

        // 🚨 CONDITION DE SÉCURITÉ : Dette supérieure à 5000 F (Solde inférieur à -5000 F)
        if (soldeClient < -5000) {
            var detteActive = Math.abs(soldeClient);
            
            Swal.fire({
                title: 'Envoi bloqué 🔒',
                text: "Votre dette active (" + detteActive.toLocaleString('fr-FR') + " F) dépasse le seuil maximal de 5 000 F autorise. Veuillez apurer votre compte au secrétariat.",
                icon: 'error',
                width: '310px',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Compris'
            });
            return false; // Arrête immédiatement l'exécution et bloque l'envoi AJAX
        }

        // Si la dette est inférieure à 5000 F, le processus d'envoi normal continue...
        Swal.fire({
            title: 'Transmettre ?',
            text: "Envoyer votre réservation au secrétariat de votre dépôt ?",
            icon: 'question',
            width: '310px',
            showCancelButton: true,
            confirmButtonColor: '#0052ff',
            cancelButtonColor: '#71767b',
            confirmButtonText: 'Oui, envoyer',
            cancelButtonText: 'Vérifier',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div> Envoi...');

                $.ajax({
                    url: '/Distrib237/client/finaliser-panier',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#cartSummaryModal').modal('hide');
                            Swal.fire({
                                title: 'Envoyé !',
                                text: response.message,
                                icon: 'success',
                                width: '310px',
                                confirmButtonColor: '#0052ff',
                                confirmButtonText: 'Suivre mon bon'
                            }).then(() => {
                                window.location.href = '/Distrib237/client/suivie';
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="bi bi-shield-lock-fill me-1"></i> Envoyer la commande');
                        Swal.fire({
                            title: 'Échec d\'envoi',
                            text: xhr.responseJSON?.message || "Une erreur est survenue.",
                            icon: 'error',
                            width: '310px',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

});
</script>
@endpush
