@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE SUPERIEURE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Gestion Facturation comptoir</h1>
            <small class="text-muted">Édition des reçus de ventes directes et débits sur comptes d'épargne client</small>
        </div>
        <span class="badge bg-dark-subtle text-dark border p-2 fw-mono fs-6 font-monospace" id="invoiceCodeHeader">FACTURE #438</span>
    </div>

    <!-- ZONE D'ÉCOSYSTÈME POS COMPTOIR (GRID MIXTE) -->
    <div class="row g-4">
        <!-- BLOC GAUCHE : FORMULAIRE DE SAISIE DE L'ARTICLE -->
        <form id="venteform" class="col-xl-5 col-lg-6 m-0">
            @csrf
            <div class="erp-panel p-4 shadow-sm bg-white rounded-4 border">
                <!-- Choix Client -->
                <div class="mb-4 bg-white p-3 rounded-3 border">
                    <label class="form-label mb-1 text-uppercase tracking-wider text-primary small fw-bold">👥 Sélection Client</label>
                    <select class="form-select border-2 fw-semibold" id="choixclient" name="client_id" required {{ $venteEnCours ? 'disabled' : '' }}>
                        <option value="" disabled {{ !$venteEnCours ? 'selected' : '' }}>-- Choisir le Client --</option>
                        <option value="walkin" data-name="Client de passage" data-balance="0" data-bloque="0" data-statut="">🛒 Client de passage (Espèces/Mobile)</option>
                        
                        @foreach($clients as $client) 
                            <!-- 💡 INJECTION DU DATA-STATUT DU PANIER DANS L'OPTION HTML -->
                            <option value="{{ $client->id_u }}" 
                                    data-name="{{ strtoupper($client->nom_u) }}" 
                                    data-balance="{{ $client->solde_u }}"
                                    data-bloque="{{ $client->est_bloque_caisse }}"
                                    data-statut="{{ $client->statut_mobile }}"
                                    {{ $venteEnCours && $venteEnCours->client_id == $client->id_u ? 'selected' : '' }}>
                                👤 {{ strtoupper($client->nom_u) }} 
                                @if($client->statut_mobile === 'brouillon')
                                    (🔒 EN COURS DE SAISIE SUR MOBILE)
                                @elseif($client->statut_mobile === 'envoyer')
                                    (🛰️ COMMANDE TRANSMISE EN LIGNE)
                                @else
                                    (Épargne : {{ number_format($client->solde_u, 0, ',', ' ') }} F)
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <!-- 💡 ALERTE DYNAMIQUE INTELLIGENTE ANTI-CONFLIT -->
                    <div class="mt-3 text-start d-none" id="customerBlockAlert">
                        <!-- CAS N°1 : Le client a un panier au statut BROUILLON (Forçage autorisé) -->
                        <div class="alert alert-warning d-flex flex-column gap-2 m-0 p-3 rounded-3 d-none" id="boxAlertBrouillon" style="font-size: 0.85rem; border-color: rgba(255,102,0,0.2); background-color: rgba(255,102,0,0.02); color: #fff;">
                            <div class="text-dark fw-semibold">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> 
                                <strong>Panier en cours :</strong> Le client remplit actuellement sa facture sur son téléphone (Erreur humaine possible).
                            </div>
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-dark btn-sm fw-bold rounded-2 px-3" id="btnBypassBrouillon">
                                    🔓 Forcer et Réinitialiser la session mobile
                                </button>
                            </div>
                        </div>

                        <!-- CAS N°2 : Le client a déjà ENVOYÉ sa commande (Forçage INTERDIT) -->
                        <div class="alert alert-danger d-flex flex-column gap-2 m-0 p-3 rounded-3 d-none" id="boxAlertEnvoyer" style="font-size: 0.85rem;">
                            <div>
                                <i class="bi bi-shield-lock-fill me-1"></i> 
                                <strong>Commande transmise :</strong> Ce client a déjà officiellement envoyé son bon d'achat au dépôt. Saisie simultanée interdite [search].
                            </div>
                            <small class="text-muted d-block" style="color: #ea868f !important;">⚠️ Veuillez traiter, modifier ou valider sa facture directement depuis l'onglet des Commandes avant de pouvoir ouvrir un nouveau ticket pour lui.</small>
                        </div>
                    </div>
                </div>



                <!-- Choix Boisson Classé par Fournisseur -->
                <div class="mb-3">
                    <label class="form-label form-label-custom mb-1 text-uppercase tracking-wider text-secondary small fw-bold">🍾 Sélection de la Boisson</label>
                    <select class="form-select border-2 fw-semibold" id="choixproduit" name="produit_id" disabled>
                        <option value="" disabled selected>-- Choisir la référence --</option>
                        
                        @php
                            // 💡 REGROUPEMENT INSTANTANÉ : On classe la collection de boissons par le nom du fournisseur
                            $produitsGroupesParFournisseur = $produitsDuDepot->groupBy(function($prod) {
                                return $prod->fournisseur?->nom_fournisseur ?? 'AUTRES BRASSERIES';
                            });
                        @endphp

                        <!-- DOUBLE BOUCLE BLADE : Génération des groupes de brasseries -->
                        @foreach($produitsGroupesParFournisseur as $nomFournisseur => $boissons)
                            <optgroup label="🏢 GAMME {{ strtoupper($nomFournisseur) }}" style="color: #0052ff; font-weight: bold; background-color: #f8fafc;">
                                
                                @foreach($boissons as $produit)
                                    @php 
                                        // Extraction et formatage propre pour les attributs data
                                        $nomP = strtoupper($produit->nom_p);
                                        $formatP = strtoupper($produit->format_p);
                                        $prixP = (int)$produit->prix_p;
                                        $stockP = (int)$produit->quantite_p;
                                        $supplierP = $produit->fournisseur?->nom_fournisseur ?? 'AUTRES';
                                    @endphp
                                    
                                    <option value="{{ $produit->id_produit }}" 
                                            data-name="{{ $nomP }}" 
                                            data-format="{{ $formatP }}" 
                                            data-price="{{ $prixP }}" 
                                            data-supplier="{{ $supplierP }}" 
                                            data-stock="{{ $stockP }}"
                                            style="color: #000000; font-weight: 500; background-color: #ffffff;">
                                        🍾 {{ $nomP }} ({{ $formatP }}) 
                                    </option>
                                @endforeach

                            </optgroup>
                        @endforeach
                    </select>
                </div>


                <!-- Métadonnées Boisson -->
                <div class="row g-2 mb-3 d-none" id="posProductMetaBox">
                    <div class="col-6"><div class="p-2 bg-light rounded text-center border"><small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Fournisseur</small><strong class="text-dark small" id="posMetaSupplier">N/A</strong></div></div>
                    <div class="col-6"><div class="p-2 bg-light rounded text-center border" id="posMetaStockBadge"><small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Stock Physique</small><strong class="text-dark small" id="posMetaStockCount">0 blles</strong></div></div>
                </div>

                <!-- Prix et Quantité -->
                <div class="row g-3 mb-4">
                    <div class="col-6"><label class="form-label small text-muted">Prix Unitaire (FCFA)</label><input type="text" class="form-control fw-bold bg-light font-monospace text-end" id="Prix_u" name="prix_u" readonly></div>
                    <div class="col-6"><label class="form-label small text-muted">Quantité à vendre</label><input type="number" class="form-control border-2 fw-bold text-center font-monospace" id="qte_v" name="qte_v" value="1" min="1" disabled></div>
                </div>

                <!-- Règlement Comptable -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="form-label small text-muted">Statut Facture</label>
                        <select class="form-select border-2 fw-semibold" id="status" name="status">
                            <option value="" >--Choix de l'etat--</option>
                            <option value="payer" >Payée</option>
                            <option value="attente">En attente</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">Mode de Paiement</label>
                        <select class="form-select border-2 fw-semibold" id="payement" name="payement">
                            <option value="Espece" selected>💰 Espece au comptoir</option>
                            <option value="OM-MOMO">📲 Mobile (OM / Momo)</option>
                            <option value="Epargne" disabled id="optPosWalletPay">💳 Compte Épargne</option>
                        </select>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="row g-2 border-top pt-3">
                    <div class="col-4"><button type="button" class="btn btn-outline-secondary w-100 py-2.5 fw-bold rounded-3" id="vider_f">Vider</button></div>
                    <div class="col-8"><button type="button" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm" id="ajou_f" disabled><i class="bi bi-plus-circle me-1"></i> Ajouter au Panier</button></div>
                </div>
            </div>
            <!-- Champs cachés de persistance pour l'AJAX -->
            <input type="hidden" name="active_client_id" id="hidden_client_id" value="{{ $venteEnCours ? ($venteEnCours->client_id ?? 'walkin') : '' }}">
            <input type="hidden" id="vente_id_brouillon" value="{{ $venteEnCours ? $venteEnCours->id : '' }}">
        </form>


        <!-- BLOC DROIT : PANIER DE COMPOSITION & TOTAL COMPTABLE EN DIRECT -->
        <div class="col-xl-7 col-lg-6">
            <div class="erp-panel p-4 bg-white h-100 d-flex flex-column justify-content-between shadow-sm rounded-4 border">
                <div>
                    <h5 class="fw-bold text-dark border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-check text-primary me-2"></i>Détails du Panier Saisi</span>
                        <span class="badge bg-light text-secondary border rounded-pill font-monospace" id="panierItemCount" style="font-size: 0.8rem;">0 article</span>
                    </h5>
                    
                    <!-- Table du Panier d'Achat Dynamique -->
                    <div class="table-responsive scrollable-table-container" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="cartTable">
                            <thead class="table-light text-secondary small text-uppercase font-monospace sticky-top" style="top:0; z-index:5;">
                                <tr>
                                    <th class="ps-3 py-2">Désignation Boisson</th>
                                    <th class="text-center py-2" style="width: 100px;">Quantité</th>
                                    <th class="text-end py-2" style="width: 120px;">Prix P.U</th>
                                    <th class="text-end py-2" style="width: 130px;">Total Ligne</th>
                                    <th class="text-center py-2" style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody" class="border-top-0">
                                @if($venteEnCours && $venteEnCours->detailVentes->count() > 0)
                                    @foreach($venteEnCours->detailVentes as $detail)
                                        <tr id="cartRow_{{ $detail->id }}" class="cart-item-row" data-total="{{ $detail->total_ligne }}" data-produit="{{ $detail->produit_id }}">
                                            <td class="ps-3"><strong class="text-dark">{{ strtoupper($detail->produit->nom_p) }}</strong></td>
                                            <td class="text-center font-monospace fw-bold">{{ $detail->quantite }}</td>
                                            <td class="text-end font-monospace">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F</td>
                                            <td class="text-end font-monospace fw-bold text-primary">{{ number_format($detail->total_ligne, 0, ',', ' ') }} F</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-link border-danger remove-cart-item-btn" data-id="{{ $detail->id }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyCartRow"><td colspan="5" class="text-center py-5 text-muted small"><i class="bi bi-cart-x fs-2 d-block mb-2 opacity-50"></i> Aucun article inséré.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BLOC TOTAL COMPTABLE FINANCIER REVISITÉ -->
                <div class="border-top pt-3 mt-4 bg-light p-3 rounded-3 border">
                    
                    <!-- Zone de Saisie de l'Argent Reçu -->
                    <div class="mb-3 bg-white p-2.5 rounded-3 border">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">💰 Somme encaissée (Espèces / Mobile)</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg fw-bold font-monospace text-end text-success" id="posCashReceived" value="0" min="0">
                            <span class="input-group-text font-monospace fw-bold text-muted bg-light">FCFA</span>
                        </div>
                        <small class="text-muted d-block mt-1" id="posCalculationHelp">Veuillez inscrire l'argent réel donné par le client.</small>
                    </div>
                    <!-- 💡 LE SWITCH DE CHOIX DE RENDU DE MONNAIE -->
                    <div class="mb-3 bg-white p-2.5 rounded-3 border d-none" id="posRefundToggleBox">
                        <div class="form-check form-switch d-flex justify-content-between align-items-center p-0">
                            <label class="form-check-label fw-bold text-secondary text-uppercase small m-0" for="posRefundToggle">
                                💵 Rendre le reliquat en espèces ?
                            </label>
                            <input class="form-check-input border-2 ms-0" type="checkbox" role="switch" id="posRefundToggle" name="rendre_monnaie" value="1">
                        </div>
                        
                        <!-- Zone d'affichage du montant exact à restituer au comptoir -->
                        <div class="mt-2 pt-2 border-top d-none text-end" id="posChangeDueBox">
                            <span class="text-secondary small fw-bold">MONNAIE PHYSIQUE À RENDRE : </span>
                            <strong class="text-danger fs-5 font-monospace" id="posChangeDueAmount">0 F</strong>
                        </div>
                    </div>


                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-secondary text-uppercase tracking-wider small">Net à payer :</span>
                        <span class="fs-3 fw-bold font-monospace text-dark" id="posInvoiceTotal" data-rawtotal="0">0 <small class="fs-6 fw-semibold text-primary">FCFA</small></span>
                    </div>
                    
                    <button type="button" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow fs-5" id="btnFinalizeSale" disabled>
                        <i class="bi bi-receipt-cutoff me-2"></i> Éditer & Encaisser le Reçu
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
    // 🔍 1. PERSISTANCE DU BROUILLON AU RECHARGEMENT (DÉBLOCAGE SÉQUENTIEL)
    // ====================================================================
    if ($('#vente_id_brouillon').val() !== "") {
        $('#choixproduit').prop('disabled', false); 
        $('#customerWalletDisplay').removeClass('d-none');
        recalculerTotalPanier();
    }   

    // ====================================================================
    // 💡 2. FONCTION MAÎTRESSE : CALCUL ET VERROUILLAGE RÉACTIF DE L'INTERFACE
    // ====================================================================
    function recalculerTotalPanier() {
        var totalInvoice = 0;
        var rowCount = 0;

        // Force la lecture par .attr() pour détruire le cache jQuery résiduel
        $('.cart-item-row').each(function() {
            totalInvoice += parseFloat($(this).attr('data-total')) || 0;
            rowCount++;
        });

        // Mise à jour des affichages du panier de droite
        $('#posInvoiceTotal').data('rawtotal', totalInvoice);
        $('#posInvoiceTotal').html(totalInvoice.toLocaleString('fr-FR') + ' <small class="fs-6 fw-semibold text-primary">FCFA</small>');
        $('#panierItemCount').text(rowCount + (rowCount > 1 ? ' articles' : ' article'));
        
        // Extraction des métadonnées financières du client actif élu
        var clientSelect = $('#choixclient option:selected');
        var clientId = $('#choixclient').val() || $('#hidden_client_id').val();
        var isWalkin = clientId === 'walkin' || clientId === '';
        var soldeDisponible = isWalkin ? 0 : parseFloat(clientSelect.data('balance')) || 0;
        
        var selectStatus = $('#status');
        var selectPayement = $('#payement');
        var optWalletPay = $('#optPosWalletPay');
        var inputCash = $('#posCashReceived');

        // Gestion des boutons et des accès selon le volume d'articles
        if (rowCount > 0) {
            $('#btnFinalizeSale').prop('disabled', false);
            $('#choixclient').prop('disabled', true); 
        } else {
            $('#btnFinalizeSale').prop('disabled', true);
            $('#choixclient').prop('disabled', false); 
            selectStatus.find('option').prop('disabled', false); 
            inputCash.prop('disabled', false).val(0);
            $('#posCalculationHelp').html('<span class="text-muted"><i class="bi bi-cart"></i> Panier vide. Prêt pour une nouvelle saisie.</span>');
            return; 
        }

        // ANALYSES CHIRURGICALES DES COMPTES (COMPTE MIXTE ÉPARGNE / DETTE)
        var argentVerse = parseFloat(inputCash.val()) || 0;
        var epargneDisponible = soldeDisponible > 0 ? soldeDisponible : 0;
        var detteActive = soldeDisponible < 0 ? Math.abs(soldeDisponible) : 0;
        var resteNetAPayer = totalInvoice + detteActive - epargneDisponible;

        if (clientId === '' || clientId === null) {
            $('#posRefundToggleBox').addClass('d-none').find('input').prop('checked', false);
            $('#posChangeDueBox').addClass('d-none');
            $('#posCalculationHelp').html('<span class="text-muted"><i class="bi bi-person-dash"></i> Veuillez sélectionner un client pour démarrer la facturation.</span>');
            return;
        }

        // CAS A : LE COMPTE ÉPARGNE DU CLIENT COUVRE L'INTÉGRALITÉ DE LA FACTURE (SANS CASH REQUIS)
        if (!isWalkin && soldeDisponible >= totalInvoice && argentVerse === 0) {
            optWalletPay.prop('disabled', false);
            selectPayement.val('Epargne');
            
            selectStatus.val('payer');
            selectStatus.find('option[value="attente"]').prop('disabled', true);
            selectStatus.find('option[value="payer"]').prop('disabled', false);
            
            inputCash.val(0).prop('disabled', true); 
            $('#posRefundToggleBox').addClass('d-none').find('input').prop('checked', false); 
            $('#posChangeDueBox').addClass('d-none');

            var soldeFuturSimule = soldeDisponible - totalInvoice;
            $('#posCalculationHelp').html(`<span class="text-success fw-bold"><i class="bi bi-shield-check"></i> Épargne supérieure : Option "En attente" bloquée. Déduction directe. Futur solde : +${soldeFuturSimule.toLocaleString('fr-FR')} F.</span>`);
        } 
        // CAS B : LE CASH PHYSIQUE VERSÉ COMPLÈTE OU DÉPASSE LE RESTE NET À PAYER
        else if (!isWalkin && argentVerse >= resteNetAPayer) {
            selectStatus.val('payer');
            selectStatus.find('option[value="attente"]').prop('disabled', true);
            selectStatus.find('option[value="payer"]').prop('disabled', false);
            inputCash.prop('disabled', false);

            if (argentVerse > resteNetAPayer) {
                $('#posRefundToggleBox').removeClass('d-none');
                var surplusMonnaie = argentVerse - resteNetAPayer;

                if ($('#posRefundToggle').is(':checked')) {
                    $('#posChangeDueAmount').text(surplusMonnaie.toLocaleString('fr-FR') + ' F');
                    $('#posChangeDueBox').removeClass('d-none');
                    $('#posCalculationHelp').html(`<span class="text-success fw-bold"><i class="bi bi-cash-coin"></i> Monnaie comptoir : Vous devez remettre ${surplusMonnaie.toLocaleString('fr-FR')} F en espèces au client. Son compte épargne reste intact.</span>`);
                } else {
                    $('#posChangeDueBox').addClass('d-none');
                    $('#posCalculationHelp').html(`<span class="text-success fw-bold"><i class="bi bi-piggy-bank-fill"></i> Option Épargne : Le surplus de +${surplusMonnaie.toLocaleString('fr-FR')} F sera versé sur son compte de réserve.</span>`);
                }
            } else {
                $('#posRefundToggleBox').addClass('d-none').find('input').prop('checked', false);
                $('#posChangeDueBox').addClass('d-none');
                $('#posCalculationHelp').html('<span class="text-success fw-bold"><i class="bi bi-lightning-charge-fill"></i> Rapprochement exact : Facture et passif entièrement soldés à 0 F.</span>');
            }
        } 
        // CAS C : COMPLÉMENT INSUFFISANT OU TOTALEMENT À VIDE (RISQUE DE DETTE EN COULISSES)
        else {
            inputCash.prop('disabled', false);
            selectStatus.find('option[value="attente"]').prop('disabled', false);
            $('#posRefundToggleBox').addClass('d-none').find('input').prop('checked', false);
            $('#posChangeDueBox').addClass('d-none');
            
            if (!isWalkin) {
                optWalletPay.prop('disabled', true);
                if (selectPayement.val() === 'Epargne') selectPayement.val('Espece');
                
                var detteRésiduelle = resteNetAPayer - argentVerse;
                $('#posCalculationHelp').html(`<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Épargne consommée / Insuffisante : Le compte client passera à -${detteRésiduelle.toLocaleString('fr-FR')} F de dette.</span>`);
            } else {
                $('#posCalculationHelp').html('<span class="text-muted"><i class="bi bi-cart"></i> Client comptoir. Saisissez le montant perçu.</span>');
            }

            if (argentVerse === 0) {
                selectStatus.val('attente');
                selectStatus.find('option[value="payer"]').prop('disabled', true);
            } else {
                selectStatus.find('option[value="payer"]').prop('disabled', false);
                selectStatus.val('payer');
            }
        }
    }

    // 👥 GESTION DU CHOIX CLIENT REFACTOREE CONTRE LES EFFETS FANTOMES
    // ====================================================================
    $(document).on('change', '#choixclient', function() {
        var val = $(this).val();
        var selected = $(this).find('option:selected');
        var walletDisplay = $('#customerWalletDisplay');
        var walletAmount = $('#customerWalletAmount');
        var walletLabel = $('#customerWalletLabel');
        
        var blockAlert = $('#customerBlockAlert');
        var boxBrouillon = $('#boxAlertBrouillon');
        var boxEnvoyer = $('#boxAlertEnvoyer');

        var isBloque = parseInt(selected.data('bloque')) || 0; 
        var statutMobile = selected.data('statut'); // Récupère 'brouillon' ou 'envoyer'

        // Nettoyage complet initial
        blockAlert.addClass('d-none');
        boxBrouillon.addClass('d-none');
        boxEnvoyer.addClass('d-none');
        walletDisplay.addClass('d-none');

        if (!val || val === "") {
            $('#choixproduit').prop('disabled', true).val('').trigger('change');
            return;
        }

        // 🚨 VERROU CHIRURGICAL ANTI-CONFLIT MOBILE
        if (isBloque === 1) {
            // Verrouille le sélecteur de boissons de caisse comptoir
            $('#choixproduit').prop('disabled', true).val('').trigger('change');
            blockAlert.removeClass('d-none');

            if (statutMobile === 'envoyer') {
                // 🔒 CAS A : Commande officielle transmise -> Blocage pur et dur sans bouton de forçage !
                boxEnvoyer.removeClass('d-none');
                
                Swal.fire({
                    title: 'Commande Transmise 🛰️',
                    text: "Ce client a déjà envoyé son bon d'achat officiel. Traitez-le depuis l'onglet des Commandes.",
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    width: '310px'
                });
            } else if (statutMobile === 'brouillon') {
                // 🔓 CAS B : Simple brouillon en cours -> Alerte ambrée avec bouton Forcer
                boxBrouillon.removeClass('d-none');
                
                Swal.fire({
                    title: 'Session Mobile Active 🔒',
                    text: "Ce client a un panier temporaire ouvert sur son téléphone. Vous pouvez forcer sa libération s'il s'agit d'un oubli.",
                    icon: 'info',
                    confirmButtonColor: '#0052ff',
                    width: '310px'
                });
            }
            return;
        }

        // Si aucune restriction mobile, comportement de caisse d'origine...
        $('#choixproduit').prop('disabled', false);

        if (val === 'walkin') {
            walletDisplay.addClass('d-none');
        } else {
            var balance = parseFloat(selected.data('balance')) || 0;
            walletAmount.removeClass('text-success text-danger text-dark');

            if (balance < 0) {
                walletLabel.text('⚠️ Dette active a recouvrer : ');
                walletAmount.addClass('text-danger').text(balance.toLocaleString('fr-FR') + ' F');
            } else if (balance > 0) {
                walletLabel.text('💳 Tresorerie Epargne disponible : ');
                walletAmount.addClass('text-success').text('+' + balance.toLocaleString('fr-FR') + ' F');
            } else {
                walletLabel.text('⚖️ Compte Epargne : ');
                walletAmount.addClass('text-dark').text('0 F');
            }

            walletDisplay.removeClass('d-none');
        }
        
        recalculerTotalPanier();
    });

    // ====================================================================
    // 🔓 ACTION : CLIC SUR LE BOUTON DE BYPASS ANTI-PANIER FANTÔME (AJAX)
    // ====================================================================
    $(document).on('click', '#btnBypassBrouillon', function() {
        var clientId = $('#choixclient').val();
        var clientName = $('#choixclient').find('option:selected').data('name');

        Swal.fire({
            title: 'Forcer la session ?',
            text: "Le panier temporaire de " + clientName + " sur son téléphone sera effacé pour libérer sa ligne. Confirmer ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff6600', // Alerte ambrée
            cancelButtonColor: '#71767b',
            confirmButtonText: 'Oui, libérer',
            cancelButtonText: 'Annuler',
            reverseButtons: true,
            width: '310px'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/Distrib237/secretariat/forcer-liberation',
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(), // CSRF du formulaire de caisse
                        client_id: clientId
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Débloqué !',
                                text: response.message,
                                icon: 'success',
                                width: '310px'
                            });
                            
                            // On bascule l'attribut data-bloque à 0 sur l'option sélectionnée pour lever l'interdiction
                            $('#choixclient').find('option:selected').attr('data-bloque', '0');
                            
                            // On simule un changement pour effacer le bloc rouge et activer l'affichage des soldes
                            $('#choixclient').trigger('change');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Action refusée',
                            text: xhr.responseJSON?.message || "Erreur de traitement.",
                            icon: 'error',
                            width: '310px'
                        });
                    }
                });
            }
        });
    });



    $(document).on('input change', '#posCashReceived, #posRefundToggle', function() {
        recalculerTotalPanier();
    });

    $(document).on('change', '#choixproduit', function() {
        var selected = $(this).find('option:selected');
        var val = $(this).val();
        var metaBox = $('#posProductMetaBox');

        if (!val || val === "") {
            metaBox.addClass('d-none');
            $('#qte_v, #ajou_f').prop('disabled', true);
            $('#Prix_u').val('');
            return;
        }

        var supplier = selected.data('supplier');
        var stock = parseInt(selected.data('stock')) || 0;
        var format = selected.data('format') || 'blles';
        var price = parseInt(selected.data('price')) || 0;

        $('#posMetaSupplier').text(supplier);
        $('#posMetaStockCount').text(stock + ' ' + format);
        $('#Prix_u').val(price.toLocaleString('fr-FR'));

        if (stock <= 0) {
            $('#posMetaStockBadge').addClass('bg-danger-subtle border-danger text-danger').removeClass('bg-light text-dark');
            $('#qte_v, #ajou_f').prop('disabled', true);
            $('#qte_v').val(0);
        } else {
            // 💡 REPRISE EXACTE ICI : Stock disponible, on repasse au neutre et on libère les inputs
            $('#posMetaStockBadge').removeClass('bg-danger-subtle border-danger text-danger').addClass('bg-light text-dark');
            $('#qte_v').prop('disabled', false).val(1).attr('max', stock).removeClass('is-invalid');
            $('#ajou_f').prop('disabled', false);
        }

        metaBox.removeClass('d-none');
    });

    // ====================================================================
    // 🚀 CLIC SUR "AJOUTER AU PANIER" (AVEC SÉCURITÉ GÈLE ANTI-DOUBLE CLIC)
    // ====================================================================
    $('#ajou_f').on('click', function() {
        var clientId = $('#choixclient').val() || $('#hidden_client_id').val();
        var produitId = $('#choixproduit').val();
        var qte = parseFloat($('#qte_v').val()) || 0; // 💡 On passe en parseFloat pour accepter les décimales
        var prixU = $('#Prix_u').val().replace(/\s/g, '');

        if (!produitId || qte <= 0) return;

         // 💡 BLINDAGE DES DECIMALES (0.25, 0.5, 0.75 autorisés uniquement)
        var partieDecimale = (qte % 1).toFixed(2); // Isole les chiffres après la virgule (ex: 0.25)
        
        if (partieDecimale != 0.00 && partieDecimale != 0.25 && partieDecimale != 0.50 && partieDecimale != 0.75) {
            Swal.fire({
                icon: 'error',
                title: 'Quantité invalide',
                text: 'Vous ne pouvez vendre que des casiers entiers (1, 2...), des demi-casiers (0.5), des quarts (0.25) ou des trois-quarts (0.75). Les autres valeurs sont refusées.',
                confirmButtonColor: '#dc3545'
            });
            return; // Bloque l'envoi
        }
        // Test anti-doublon d'article dans le tableau HTML
        var dejaDansLePanier = false;
        $('.cart-item-row').each(function() {
            if ($(this).attr('data-produit') == produitId) {
                dejaDansLePanier = true;
            }
        });

        if (dejaDansLePanier) {
            Swal.fire({
                icon: 'warning',
                title: 'Article déjà présent',
                text: 'Cette boisson est déjà dans votre panier. Si vous souhaitez modifier la quantité, supprimez la ligne actuelle et recréez-la.',
                confirmButtonColor: '#3085d6'
            });
            return; // Bloque la requête AJAX
        }

        // 🔒 Sécurisation : On gèle le bouton d'ajout pour tuer le double-clic parasite
        var btnAjouter = $(this);
        btnAjouter.prop('disabled', true).html('<div class="spinner-border spinner-border-sm"></div>');

        $.ajax({
            url: '/gestion/caisse/ajouter-article',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                vente_id: $('#vente_id_brouillon').val(),
                client_id: clientId,
                produit_id: produitId,
                quantite: qte,
                prix_unitaire: prixU
            },
            success: function(response) {
                if (response.success) {
                    $('#emptyCartRow').remove();
                    $('#vente_id_brouillon').val(response.vente_id);
                    $('#hidden_client_id').val(clientId);
                    $('#choixclient').prop('disabled', true); // Verrouille le client

                    $('#cartRow_' + response.detail_id).remove();
                    var newRow = `
                        <tr id="cartRow_${response.detail_id}" class="cart-item-row" data-total="${response.total_ligne}" data-produit="${produitId}">
                            <td class="ps-3"><strong class="text-dark">${response.nom_produit}</strong></td>
                            <td class="text-center font-monospace fw-bold">${qte}</td>
                            <td class="text-end font-monospace">${parseInt(prixU).toLocaleString('fr-FR')} F</td>
                            <td class="text-end font-monospace fw-bold text-primary">${parseInt(response.total_ligne).toLocaleString('fr-FR')} F</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-link text-danger border-danger remove-cart-item-btn" data-id="${response.detail_id}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>`;
                        
                    $('#cartTableBody').append(newRow);
                    recalculerTotalPanier();
                    $('#choixproduit').val('').trigger('change'); 
                }
            },
            complete: function() {
                // Libère le bouton d'ajout à la fin du cycle réseau
                btnAjouter.prop('disabled', false).html('<i class="bi bi-plus-circle me-1"></i> Ajouter au Panier');
            }
        });
    });

    // ==========================================
    // 🗑️ RETIRER UN ARTICLE DU PANIER (AVEC DÉLAI DE DIGESTION DOM)
    // ==========================================
    $(document).on('click', '.remove-cart-item-btn', function() {
        var detailId = $(this).data('id');
        var row = $(this).closest('tr');

        $.ajax({
            url: '/gestion/caisse/supprimer-article/' + detailId,
            type: 'DELETE',
            data: { _token: $('input[name="_token"]').val() },
            success: function(response) {
                if (response.success) {
                    row.remove(); // Destruction de la ligne
                    
                    if ($('.cart-item-row').length === 0) {
                        $('#vente_id_brouillon').val('');
                        $('#hidden_client_id').val('');
                        $('#choixclient').prop('disabled', false).val('').trigger('change');
                        $('#cartTableBody').html('<tr id="emptyCartRow"><td colspan="5" class="text-center py-5 text-muted small"><i class="bi bi-cart-x fs-2 d-block mb-2 opacity-50"></i> Aucun article inséré.</td></tr>');
                    }
                    
                    // Force le recalcul asynchrone à blanc après 50ms
                    setTimeout(function() {
                        recalculerTotalPanier(); 
                    }, 50);
                }
            }
        });
    });

    // ==========================================
    // 🧼 ACTION : BOUTON VIDER TOTALEMENT LE PANIER (RESET COMPLET)
    // ==========================================
    $('#vider_f').on('click', function() {
        var venteId = $('#vente_id_brouillon').val();

        if (!venteId) {
            $('#choixproduit').val('').trigger('change');
            $('#Prix_u').val('');
            $('#qte_v').val(1).prop('disabled', true);
            return;
        }

        Swal.fire({
            title: '🗑️ Annuler la facture ?',
            text: "Toutes les lignes saisies seront définitivement effacées du panier.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, vider',
            cancelButtonText: 'Continuer la vente',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/gestion/caisse/annuler-brouillon/' + venteId,
                    type: 'DELETE',
                    data: { _token: $('input[name="_token"]').val() },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Panier vidé !',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                location.reload();
                            }, 1200);
                        }
                    },
                    error: function() {
                        Swal.fire('Erreur', "Impossible d'annuler le panier actuel.", 'error');
                    }
                });
            }
        });
    });

    // ==========================================
    // 🧾 CLIC SUR "ÉDITER & ENCAISSER" + APPEL DU REÇU THERMIQUE
    // ==========================================
    $('#btnFinalizeSale').on('click', function() {
        var venteId = $('#vente_id_brouillon').val();
        var modePaiement = $('#payement').val();
        var statutFacture = $('#status').val();
        
        var totalFacture = parseFloat($('#posInvoiceTotal').data('rawtotal')) || 0;
        var argentVerse = parseFloat($('#posCashReceived').val()) || 0;
        
        var clientSelect = $('#choixclient option:selected');
        var isWalkin = $('#choixclient').val() === 'walkin';
        
        var soldeActuel = isWalkin ? 0 : parseFloat(clientSelect.data('balance')) || 0;
        var epargneDisponible = soldeActuel > 0 ? soldeActuel : 0;
        var detteActive = soldeActuel < 0 ? Math.abs(soldeActuel) : 0;
        
        var resteNetAPayer = totalFacture + detteActive - epargneDisponible;

        // 🛑 Sécurité : Blocage si dette active précédente et montant versé insuffisant
        if (!isWalkin && soldeActuel < -5000) { // Seuil de dette active à 5000 FCFA
            var montantRequisPourValider = totalFacture + detteActive;
            
            if (argentVerse < montantRequisPourValider) {
                Swal.fire({
                    icon: 'error',
                    title: '🛑 Compte Bloqué (Dette Active)',
                    html: `
                        <div class="text-start border p-3 rounded bg-danger bg-opacity-10 text-danger small">
                            <p class="mb-1"><strong>Dette précédente :</strong> ${detteActive.toLocaleString('fr-FR')} FCFA</p>
                            <p class="mb-1"><strong>Nouvelle Facture :</strong> ${totalFacture.toLocaleString('fr-FR')} FCFA</p>
                            <hr class="my-1 border-danger">
                            <p class="mb-0 fw-bold">Le client doit verser au moins : ${montantRequisPourValider.toLocaleString('fr-FR')} FCFA pour débloquer sa facturation.</p>
                        </div>`,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Corriger l\'encaissement'
                });
                return;
            }
        }

        // Configuration de l'affichage du SweetAlert2
        var switchRendreCoche = $('#posRefundToggle').is(':checked');
        // 💡 REPRISE EXACTE DU SCRIPT ICI :
        var labelResultatFinancier = '💳 Futur Solde Épargne :';
        var calculValeurFinanciere = soldeActuel + argentVerse - totalFacture;
        var colorClass = 'text-primary';

        if (!isWalkin && argentVerse > resteNetAPayer) {
            if (switchRendreCoche) {
                labelResultatFinancier = '💵 RELIQUAT CASH À RENDRE AU COMPTOIR :';
                calculValeurFinanciere = argentVerse - resteNetAPayer;
                colorClass = 'text-danger fs-5'; 
            } else {
                labelResultatFinancier = '🐷 Futur Solde Épargne (Monnaie conservée) :';
                colorClass = 'text-success';
            }
        } else if (!isWalkin && calculValeurFinanciere < 0) {
            labelResultatFinancier = '⚠️ Future Dette Restante Client :';
            colorClass = 'text-danger';
            calculValeurFinanciere = Math.abs(calculValeurFinanciere);
        }

        var bilanSoldeHTML = '';
        if (!isWalkin) {
            bilanSoldeHTML = `
                <div class="border rounded p-3 bg-light small mt-2 text-start">
                    <div class="d-flex justify-content-between mb-1 text-muted"><span>Solde de départ :</span><b>${soldeActuel.toLocaleString('fr-FR')} F</b></div>
                    <div class="d-flex justify-content-between mb-1 text-success font-monospace"><span>Argent Physique Reçu :</span><b>+ ${argentVerse.toLocaleString('fr-FR')} F</b></div>
                    <div class="d-flex justify-content-between mb-1 text-danger font-monospace"><span>Net de la Facture :</span><b>- ${totalFacture.toLocaleString('fr-FR')} F</b></div>
                    <hr class="my-1 border-secondary border-opacity-20">
                    <div class="d-flex justify-content-between fw-bold ${colorClass}">
                        <span>${labelResultatFinancier}</span>
                        <span class="font-monospace">${calculValeurFinanciere.toLocaleString('fr-FR')} FCFA</span>
                    </div>
                </div>`;
        } else {
            var monnaiePassage = argentVerse - totalFacture;
            bilanSoldeHTML = `
                <div class="border rounded p-3 bg-light small mt-2 text-start">
                    <div class="d-flex justify-content-between mb-1 text-muted"><span>Total Facture :</span><b>${totalFacture.toLocaleString('fr-FR')} F</b></div>
                    <div class="d-flex justify-content-between mb-1 text-success"><span>Argent perçu :</span><b>+ ${argentVerse.toLocaleString('fr-FR')} F</b></div>
                    <hr class="my-1">
                    <div class="d-flex justify-content-between fw-bold ${monnaiePassage > 0 ? 'text-danger fs-5' : 'text-primary'}">
                        <span>${monnaiePassage > 0 ? '💵 Monnaie à rendre au comptoir :' : '✅ Compte Juste :'}</span>
                        <b class="font-monospace">${(monnaiePassage > 0 ? monnaiePassage : 0).toLocaleString('fr-FR')} FCFA</b>
                    </div>
                </div>`;
        }

        Swal.fire({
            title: '🧾 Validation de l\'Encaissement',
            width: '550px',
            html: `
                <div class="alert alert-info text-start p-2.5 mb-2 small rounded-3 border-0">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Mode d'Écriture :</strong> Double comptabilisation (Caisse + Historique Admin)
                </div>
                ${bilanSoldeHTML}
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', 
            cancelButtonColor: '#6c757d',   
            confirmButtonText: '🚀 Confirmer & Valider',
            cancelButtonText: 'Modifier la saisie',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                var btnFinaliser = $('#btnFinalizeSale');
                btnFinaliser.prop('disabled', true).html('<div class="spinner-border spinner-border-sm me-1"></div> Traitement...');

                $.ajax({
                    url: '/gestion/caisse/cloturer-vente',
                    type: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(),
                        vente_id: venteId,
                        mode_paiement: modePaiement,
                        statut: statutFacture,
                        montant_verse: argentVerse,
                        rendre_monnaie: switchRendreCoche ? 1 : 0 
                    },
                    success: function(response) {
                        if (response.success) {
                            
                            // 💡 LE SELECTEUR GRAPHIQUE DE FORMATS DE FACTURE
                            Swal.fire({
                                title: '🖨️ Choix du format d\'impression',
                                text: "La vente est validée. Veuillez sélectionner le format du reçu pour le client :",
                                icon: 'success',
                                showCancelButton: true,
                                cancelButtonColor: '#6c757d',
                                cancelButtonText: '❌ Fermer sans imprimer',
                                showDenyButton: true,
                                confirmButtonColor: '#198754', // Vert
                                denyButtonColor: '#0d6efd',    // Bleu
                                confirmButtonText: '📄 Ticket Standard (80mm)',
                                denyButtonText: '📝 Format A5 (Demi-page)',
                                // Ajout d'un troisième bouton personnalisé pour le format A4
                                footer: `<button type="button" class="btn btn-secondary rounded-3 w-100 fw-bold py-2" id="swal-btn-a4" style="background-color: #6f42c1; border-color: #6f42c1;">🏢 Facture Grand Format A4 (Grossiste)</button>`
                            }).then((result) => {
                                var urlImpression = '/gestion/caisse/imprimer-pdf/' + response.vente_id;

                                if (result.isConfirmed) {
                                    // Option 1 : Ticket Thermique
                                    window.open(urlImpression + '?format=ticket', '_blank');
                                } else if (result.isDenied) {
                                    // Option 2 : Format A5
                                    window.open(urlImpression + '?format=a5', '_blank');
                                }

                                // Redirection de rafraîchissement de la caisse dans tous les cas
                                setTimeout(function() { location.reload(); }, 1000);
                            });

                            // Écouteur pour intercepter le clic sur le bouton A4 personnalisé dans le footer du SweetAlert
                            $(document).on('click', '#swal-btn-a4', function() {
                                window.open('/gestion/caisse/imprimer-pdf/' + response.vente_id + '?format=a4', '_blank');
                                Swal.close();
                                setTimeout(function() { location.reload(); }, 1000);
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erreur comptable', xhr.responseJSON?.message || 'La transaction a été rejetée.', 'error');
                        btnFinaliser.prop('disabled', false).html('<i class="bi bi-receipt-cutoff me-2"></i> Éditer & Encaisser le Reçu');
                    }
                });
            }
        });
    });

    // Écouteurs d'ajustements monétaires
    $(document).on('input change', '#posCashReceived, #posRefundToggle', function() {
        recalculerTotalPanier();
    });




}); // 💡 FIN DU DOCUMENT READY CAISSE
</script>

</script>
@endpush
