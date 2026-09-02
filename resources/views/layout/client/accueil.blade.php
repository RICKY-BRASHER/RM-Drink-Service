@extends('layout.client.client')

@section('client_content')
<div class="container py-3">

    <!-- MESSAGE DE BIENVENUE DYNAMIQUE -->
    <div class="mb-3 ps-1">
        <h5 class="fw-bold m-0 text-white" style="letter-spacing: -0.3px;">Bienvenue, {{ ucfirst(strtolower($client->nom_u)) }}</h5>
    </div>

    <!-- 💳 CARTE FINTECH PRESTIGE BLUE ALIMENTÉE PAR LA BDD -->
    <div class="fintech-card mb-4 text-white">
        <!-- LIGNE SUPÉRIEURE : SOLDE ÉPARGNE INTERACTIF -->
        <div class="d-flex justify-content-between align-items-start position-relative" style="z-index: 3;">
            <div>
                <span class="card-label-small">Solde Épargne</span>
                @php
                    $soldeBrut = (float)$client->solde_u;
                    $soldeFormate = number_format($soldeBrut, 0, ',', ' ') . ' F';
                @endphp
                <!-- Si le solde est négatif, il s'affiche en rouge sur la carte de prestige -->
                <h2 class="fw-bold m-0 font-monospace mt-1 {{ $soldeBrut < 0 ? 'text-black' : 'text-white' }}" 
                    id="displayWalletAmount" 
                    data-real="{{ $soldeFormate }}" 
                    style="font-size: 1.9rem; letter-spacing: -0.5px;">
                    {{ $soldeFormate }}
                </h2>
            </div>
            <!-- L'œil tactile -->
            <div class="eye-toggle-btn" id="toggleWalletEye" data-status="visible" style="cursor: pointer;">
                <i class="bi bi-eye"></i>
            </div>
        </div>

        <!-- LIGNE INFÉRIEURE : NOM ET NUMÉRO DE TÉLÉPHONE MASQUÉ -->
        <div class="d-flex justify-content-between align-items-end position-relative" style="z-index: 3; margin-top: 35px;">
            <div>
                <span class="card-label-small d-block">Titulaire</span>
                <strong class="text-white text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.3px;">{{ $client->nom_u }}</strong>
            </div>
            <div class="text-end">
                <span class="card-label-small d-block">Identifiant Unique</span>
                <!-- Extraction sécurisée des 4 derniers chiffres du numéro de téléphone -->
                <span class="font-monospace text-white fw-bold" style="font-size: 0.85rem;">•••• {{ substr($client->id_u, -4) }}</span>
            </div>
        </div>
    </div>

    <!-- LE LIEN DES TRANSACTIONS FINANCIÈRES RACCORDÉ AUX ROUTES -->
    <div class="mb-4 ps-1">
        <a href="{{ route('client.transactions') }}" style="color: #0052ff; text-decoration: none; font-weight: bold; font-size: 0.82rem; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-arrow-down-left-circle"></i> Consulter mes transactions de caisse ➔
        </a>
    </div>

    <!-- 💳 BLOC CASHBACK ENTIÈREMENT SÉCURISÉ ET RENDU INTERACTIF -->
    <div style="background-color: #111111; border-radius: 16px; padding: 16px 20px; margin-bottom: 24px; border: 1px solid #1f2022;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2.5">
                <span style="color: #9ea3a9; font-size: 0.85rem; font-weight: 500;">Mon Cashback (1%)</span>
            </div>
            <div class="d-flex align-items-center gap-2 font-monospace">
                <div id="toggleCashbackEye" data-status="visible" style="font-size: 1rem; cursor: pointer; opacity: 0.7; display: flex; align-items: center; color: #71767b;">
                    <i class="bi bi-eye"></i>
                </div>
                <strong class="text-white" id="displayCashbackAmount" data-real="{{ number_format($cashbackVirtuel, 0, ',', ' ') }} F" style="font-size: 0.95rem;">
                    {{ number_format($cashbackVirtuel, 0, ',', ' ') }} F
                </strong>
                <span style="color: #71767b; font-size: 0.85rem; margin-left: 2px;">➔</span>
            </div>
        </div>
    </div>

    <!-- 📊 LES COMPTEURS CARRÉS COMPACTS SÉCURISÉS -->
    <div class="mb-2.5 ps-1">
        <h6 style="color: #71767b; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Statistiques Mensuelles</h6>
    </div>
    
    <div class="row g-2 mb-4">
        <!-- Compteur 1 : Commandes réelles du mois -->
        <div class="col-6">
            <div style="background-color: #111111; border: 1px solid rgba(0, 82, 255, 0.2); border-radius: 16px; padding: 18px; aspect-ratio: 1 / 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span style="font-size: 1.4rem; display: flex; align-items: center; color: #0052ff; margin-bottom: 6px;"><i class="bi bi-calendar3"></i></span>
                    <small style="color: #71767b; font-size: 0.72rem; font-weight: bold; text-transform: uppercase; display: block; line-height: 1.2;">Commandes<br>du Mois</small>
                </div>
                <h3 class="fw-bold m-0 font-monospace text-white" style="font-size: 1.7rem;">
                    {{ $commandesMois }} <span style="font-size: 0.8rem; color: #71767b; font-weight: normal;">bons</span>
                </h3>
            </div>
        </div>
        
        <!-- Compteur 2 : Budget Consommé formaté en abréviation intelligente (K) -->
        <div class="col-6">
            <div style="background-color: #111111; border: 1px solid rgba(0, 188, 255, 0.2); border-radius: 16px; padding: 18px; aspect-ratio: 1 / 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span style="font-size: 1.4rem; display: flex; align-items: center; color: #00bcff; margin-bottom: 6px;"><i class="bi bi-cash-coin"></i></span>
                    <small style="color: #71767b; font-size: 0.72rem; font-weight: bold; text-transform: uppercase; display: block; line-height: 1.2;">Budget<br>Consommé</small>
                </div>
                <h3 class="fw-bold m-0 font-monospace" style="font-size: 1.45rem; color: #00bcff;">
                    {{ number_format($budgetConsomme, 0, ',', ' ') }}
                    <span style="font-size: 0.8rem; color: #71767b; font-weight: normal;">F</span>
                </h3>
            </div>
        </div>
    </div>

    <!-- SERVICES ADDITIONNELS -->
    <div class="mb-2 ps-1">
        <h6 style="color: #71767b; font-size: 0.75rem; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Mes Services Connectés</h6>
    </div>
    <div style="background-color: #111111; border-radius: 16px; padding: 14px 20px; border: 1px solid #1f2022;" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span style="font-size: 1.2rem; color: #0052ff; display: flex; align-items: center;"><i class="bi bi-tv"></i></span>
            <span class="fw-bold text-white" style="font-size: 0.85rem;">Distrib237 TV</span>
        </div>
        <span class="badge bg-black bg-opacity-40 border border-secondary text-secondary rounded-pill px-2.5 py-1" style="font-size: 0.65rem; font-weight: bold;">Bientôt</span>
    </div>

</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 💡 1. INTERACTIVITÉ DU MASQUAGE DU SOLDE PRINCIPAL
    $('#toggleWalletEye').on('click', function() {
        var btn = $(this);
        var icon = btn.find('i');
        var displayZone = $('#displayWalletAmount');
        var currentStatus = btn.attr('data-status');
        
        if (currentStatus === 'visible') {
            displayZone.text('•••••• F');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
            btn.attr('data-status', 'hidden');
        } else {
            var realAmount = displayZone.attr('data-real');
            displayZone.text(realAmount);
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
            btn.attr('data-status', 'visible');
        }
    });

    // 💡 2. AJOUT SUR-MESURE : INTERACTIVITÉ DU MASQUAGE DU CASHBACK
    $('#toggleCashbackEye').on('click', function() {
        var btn = $(this);
        var icon = btn.find('i');
        var displayZone = $('#displayCashbackAmount');
        var currentStatus = btn.attr('data-status');
        
        if (currentStatus === 'visible') {
            displayZone.text('****** F');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
            btn.attr('data-status', 'hidden');
        } else {
            var realAmount = displayZone.attr('data-real');
            displayZone.text(realAmount);
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
            btn.attr('data-status', 'visible');
        }
    });
});
</script>
@endpush
