@extends('layout.client.client')

@section('client_content')
<div class="container py-3 notif-page-bg">

    <!-- EN-TÊTE ÉPURÉ STYLE FACEBOOK MOBILE -->
    <div class="d-flex justify-content-between align-items-center mb-4 ps-1">
        <div>
            <h4 class="fw-bold text-white m-0 font-monospace" style="letter-spacing: -0.5px;">Notifications</h4>
            <small style="color: #71767b; font-size: 0.78rem;">Fil d'actualité de votre compte Distrib237</small>
        </div>
        <!-- Bouton tout effacer tactile -->
        <button class="btn btn-sm btn-outline-secondary rounded-pill border-secondary border-opacity-30 font-monospace text-white-50 px-3" style="font-size: 0.72rem; font-weight: 600;" id="clearAllClientNotifs">
            Tout lire
        </button>
    </div>

    <!-- 📲 FIL DE DISCUSSION & ALERTES COMPTABLES -->
    <div class="d-flex flex-column gap-2" id="clientNotificationsContainer">

        <!-- Exemple 1 : Validation de caisse (Non lu) -->
        <div class="notif-mobile-card p-3 d-flex align-items-start gap-3 position-relative" style="border-left: 3px solid #0052ff;">
            <div class="notif-icon-bubble bubble-success">
                <i class="bi bi-shield-check-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-white text-uppercase font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">✅ Commande Validée & Livrée</strong>
                    <!-- Point Bleu Facebook de message non lu -->
                    <div class="notif-unread-indicator"></div>
                </div>
                <span class="text-white-50 d-block mt-1 small" style="line-height: 1.35;">
                    Le règlement de votre bon <b class="text-white font-monospace">FACT-2026-M041</b> a été validé au guichet. Votre reçu PDF officiel est disponible.
                </span>
                <small class="font-monospace mt-1.5 d-block" style="color: #71767b; font-size: 0.68rem;">Il y a 3 minutes</small>
            </div>
        </div>

        <!-- Exemple 2 : Recharge de portefeuille (Non lu) -->
        <div class="notif-mobile-card p-3 d-flex align-items-start gap-3 position-relative" style="border-left: 3px solid #0052ff;">
            <div class="notif-icon-bubble bubble-primary">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-white text-uppercase font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">💰 Compte Épargne Rechargé</strong>
                    <div class="notif-unread-indicator"></div>
                </div>
                <span class="text-white-50 d-block mt-1 small" style="line-height: 1.35;">
                    Votre versement de <b class="text-success font-monospace">150 000 FCFA</b> a été enregistré. Nouveau solde disponible : <b class="text-white font-monospace">345 000 F</b>.
                </span>
                <small class="font-monospace mt-1.5 d-block" style="color: #71767b; font-size: 0.68rem;">Il y a 45 minutes</small>
            </div>
        </div>

        <!-- Exemple 3 : Alerte de modification logistique (Déjà lu) -->
        <div class="notif-mobile-card p-3 d-flex align-items-start gap-3 opacity-60">
            <div class="notif-icon-bubble bubble-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-white text-uppercase font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">⚠️ Bon de commande ajusté</strong>
                </div>
                <span class="text-white-50 d-block mt-1 small" style="line-height: 1.35;">
                    Votre réservation <b class="text-white font-monospace">FACT-2026-X114</b> a été modifiée par le secrétariat en raison des stocks réels de Guinness Brune.
                </span>
                <small class="font-monospace mt-1.5 d-block" style="color: #71767b; font-size: 0.68rem;">Hier à 16:42</small>
            </div>
        </div>

        <!-- Exemple 4 : Sanction de dépassement de dette (Déjà lu) -->
        <div class="notif-mobile-card p-3 d-flex align-items-start gap-3 opacity-60">
            <div class="notif-icon-bubble bubble-danger">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-white text-uppercase font-monospace" style="font-size: 0.8rem; letter-spacing: 0.3px;">🔒 Bloqué : Plafond dépassé</strong>
                </div>
                <span class="text-white-50 d-block mt-1 small" style="line-height: 1.35;">
                    Votre compte a temporairement été suspendu pour les commandes mobiles. Votre ardoise dépasse la tolérance maximale de <b class="text-danger font-monospace">5 000 F</b>.
                </span>
                <small class="font-monospace mt-1.5 d-block" style="color: #71767b; font-size: 0.68rem;">Le 28/08/2026</small>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 💡 Interaction tactile : Un clic sur une notification simule sa lecture (efface le point bleu)
    $(document).on('click', '.notif-mobile-card', function() {
        var card = $(this);
        card.find('.notif-unread-indicator').fadeOut(300, function() {
            $(this).remove();
        });
        card.css('border-left', 'none').addClass('opacity-60');
    });

    // Bouton tout lire
    $('#clearAllClientNotifs').on('click', function() {
        $('.notif-unread-indicator').fadeOut(300);
        $('.notif-mobile-card').css('border-left', 'none').addClass('opacity-60');
    });
});
</script>
@endpush
