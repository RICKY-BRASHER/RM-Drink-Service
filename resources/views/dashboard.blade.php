@include('depot')
<div class="main" id="main">
    <div class="dash-head position">
        <h1>My Dashboard</h1>
    </div>
    <div class="dash-up">
        <div class="stat">
            <span class="stat-text">CHIFFRE JOURNALIER</span>
            <span class="stat-value" id="stat-value">0 FCFA</span>
            <span class="stat-cmd" id="stat-cmd">0 Ventes Enregistrees</span>
        </div>
        <div class="stat">
            <span class="stat-text">CHIFFRE MENSUEL</span>
            <span class="stat-value" id="stat-value">0 FCFA</span>
            <span class="stat-cmd" id="stat-cmd">0 Ventes Enregistrees</span>
        </div>
        <div class="stat">
            <span class="stat-text">NOUVEAUX CLIENTS</span>
            <span class="stat-value" id="stat-value" style='color: darkorange'>0</span>
            <span class="stat-cmd" id="stat-cmd">Clients Enregistrees</span>
        </div>
        <div class="stat">
            <span class="stat-text">ALERTES ( STOCKS FAIBLES )</span>
            <span class="stat-value" id="stat-value" style='color: red'>0</span>
            <span class="stat-cmd" id="stat-cmd">Produits a reapprovisionner</span>
        </div>
        
    </div>
    <div class="dash-down">
        <div class="client" id="client">
            <div class="best-client h1">Clientelle Fidele</div>
            <div class="best-info">
                <table>
                    <tr style="background-color: rgb(230, 207, 93);">
                        <th>N°</th>
                        <th>Nom</th>
                        <th>Adresse</th>
                        <th>Nb Achat</th>
                        <th>Montant</th>
                    </tr>
                </table>
            </div>
        </div>
        <div class="level" id="level">
            <div class="h1">Performances</div>
        </div>
    </div>
</div>
@include('footer')
