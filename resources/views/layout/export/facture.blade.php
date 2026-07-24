<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $vente->code_facture }}</title>
    <style>
        @page { margin: 15px; }
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            
            /* 💡 ADAPTATION DYNAMIQUE DU DESIGN SELON LE FORMAT CHOSI */
            @if($formatPapier === 'a4')
                width: 100%;
                font-size: 13px;
            @elseif($formatPapier === 'a5')
                width: 100%;
                font-size: 12px;
            @else
                width: 74mm; /* Reste compact pour le rouleau thermique 80mm */
                font-size: 11px;
            @endif
        }
        
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .dashed-hr { border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        .text-right { text-align: right; }
        
        /* Style pour élargir les lignes du tableau si grand format */
        th, td { 
            padding: {{ ($formatPapier === 'a4' || $formatPapier === 'a5') ? '8px 4px' : '4px 0' }};
            font-size: {{ ($formatPapier === 'a4') ? '13px' : (($formatPapier === 'a5') ? '12px' : '11px') }};
        }
    </style>

</head>
<body>

    <!-- ENTÊTE -->
    <div class="center">
        <span class="bold" style="font-size: 13px;">🌟 DISTRIBUTION AGREE 237 🌟</span><br>
        <span class="bold">PV : {{ $nomDepot }}</span><br>
        <span style="font-size: 10px;">Douala, Cameroun</span>
    </div>

    <div class="dashed-hr"></div>

    <!-- METADONNÉES -->
    <div>
        <div><strong>TICKET N°:</strong> {{ $vente->code_facture }}</div>
        <div><strong>DATE :</strong> {{ $vente->created_at->format('d/m/Y H:i:s') }}</div>
        <div><strong>AGENT :</strong> {{ strtoupper($vente->employe->nom_u ?? 'CAISSIER') }}</div>
        <div><strong>CLIENT :</strong> {{ $vente->client ? strtoupper($vente->client->nom_u) : 'CLIENT DE PASSAGE' }}</div>
        @if($vente->client && $vente->client->telephone_u)
            <div><strong>TEL :</strong> {{ $vente->client->telephone_u }}</div>
        @endif
    </div>

    <div class="dashed-hr"></div>

    <!-- ARTICLES -->
    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000; font-weight: bold;">
                <th style="text-align: left; padding-bottom: 4px;">DESIGNATION ART.</th>
                <th style="text-align: right; padding-bottom: 4px;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vente->detailVentes as $detail)
                <tr>
                    <td style="padding: 4px 0; text-transform: uppercase;">
                        <strong>{{ $detail->produit->nom_p }}</strong><br>
                        <small>{{ $detail->quantite }} x {{ number_format($detail->prix_unitaire, 0, ',', ' ') }} F [{{ $detail->produit->format_p }}]</small>
                    </td>
                    <td class="text-right" style="vertical-align: bottom; font-weight: bold;">
                        {{ number_format($detail->total_ligne, 0, ',', ' ') }} F
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-hr"></div>

    <!-- RECAPITULATIF FINANCIER -->
    <div style="font-size: 12px; border: 1px solid #000; padding: 4px; font-weight: bold; margin-bottom: 5px;">
        <table style="width: 100%;">
            <tr>
                <td>NET À PAYER :</td>
                <td class="text-right">{{ number_format($vente->montant_total, 0, ',', ' ') }} F</td>
            </tr>
        </table>
    </div>

    <!-- RECAPITULATIF COMPTABLE AVANCÉ MULTI-STATUTS -->
    <div style="font-size: 10px; margin-top: 5px;">
        <table style="width: 100%;">
            
            {{-- ==================================================================== --}}
            {{-- CAS 1 : FACTURE ENCAISSÉE ET PAYÉE DÉFINITIVEMENT (CLIENT RÉEL)       --}}
            {{-- ==================================================================== --}}
            @if($logFinancier)
                @php
                    $soldeAvant = (float)$logFinancier->solde_avant_r;
                    $soldeApres = (float)$logFinancier->solde_apres_r;
                    $cashEncaisse = (float)$logFinancier->montant_r;
                    
                    $partEpargnePrelevee = 0;
                    if ($cashEncaisse < (float)$vente->montant_total && $soldeAvant > 0) {
                        $partEpargnePrelevee = min((float)$vente->montant_total - $cashEncaisse, $soldeAvant);
                    }
                @endphp

                <tr>
                    <td>Solde Epargne Initial :</td>
                    <td class="text-right">{{ number_format($soldeAvant, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td>Montant Encaisse (Cash/Mobile) :</td>
                    <td class="text-right">{{ number_format($cashEncaisse, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td>Prelevement sur Epargne :</td>
                    <td class="text-right" style="color: #444;">{{ number_format($partEpargnePrelevee, 0, ',', ' ') }} F</td>
                </tr>
                <tr style="border-top: 1px dashed #000;">
                    <td class="bold">Total Preleve sur Facture :</td>
                    <td class="text-right bold" style="color: #ff0000;">{{ number_format($vente->montant_verse, 0, ',', ' ') }} F</td>
                </tr>
                <tr style="border-top: 1px solid #000;">
                    @if($soldeApres < 0)
                        <td class="bold" style="color: #ff0000;">SITUATION - DETTE ACTUELLE :</td>
                        <td class="text-right bold" style="color: #ff0000;">{{ number_format($soldeApres, 0, ',', ' ') }} F</td>
                    @elseif($soldeApres > 0)
                        <td class="bold" style="color: #008000;">SITUATION - NOUVEAU SOLDE EPARGNE :</td>
                        <td class="text-right bold" style="color: #008000;">{{ number_format($soldeApres, 0, ',', ' ') }} F</td>
                    @else
                        <td class="bold">SITUATION DU COMPTE :</td>
                        <td class="text-right bold">SOLDE VIDE (0 F)</td>
                    @endif
                </tr>

            {{-- ==================================================================== --}}
            {{-- CAS 2 : FACTURE EN ATTENTE DE RÈGLEMENT (CLIENT RÉEL)                 --}}
            {{-- ==================================================================== --}}
            @elseif($vente->statut === 'attente' && !is_null($vente->client_id))
                @php
                    // 1. Solde actuel (épargne ou dette) avant traitement de ce bon
                    $soldeClientAvant = (float)$vente->client->solde_u;
                    
                    // 2. L'argent liquide physique laissé en avance au comptoir
                    $cashAvanceEncaisse = (float)$vente->montant_verse;
                    
                    // 3. Total Trésorerie mobilisée = Solde actuel (si épargne) + l'avance cash
                    $epargneDisponible = $soldeClientAvant > 0 ? $soldeClientAvant : 0;
                    $totalTresorerieMobilisee = $cashAvanceEncaisse + $epargneDisponible;
                    
                    // 4. Calcul du reste net à fournir pour solder la facture
                    // Seuil requis = Prix facture + dette active précédente (si le client est déjà endetté)
                    $detteActivePrecedente = $soldeClientAvant < 0 ? abs($soldeClientAvant) : 0;
                    $totalRequisPourToutSolder = (float)$vente->montant_total + $detteActivePrecedente;
                    
                    $resteAFournir = $totalRequisPourToutSolder - $cashAvanceEncaisse - $epargneDisponible;
                    $resteAFournir = max(0, $resteAFournir);
                    
                    // 5. Simulation du futur solde si le client ne donne plus aucun complément au retrait
                    $futurSoldeSimule = $soldeClientAvant + $cashAvanceEncaisse - (float)$vente->montant_total;
                @endphp

                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold; color: #b58105; background: #fff3cd; padding: 3px; border: 1px solid #ffe69c;">
                        [TICKET DE RESERVATION / COMMANDE EN ATTENTE]
                    </td>
                </tr>
                <tr style="padding-top: 5px;">
                    <td>Solde Epargne Initial :</td>
                    <td class="text-right font-monospace">{{ number_format($soldeClientAvant, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td>Montant Encaisse (Avance Cash) :</td>
                    <td class="text-right font-monospace" style="color: green;">+ {{ number_format($cashAvanceEncaisse, 0, ',', ' ') }} F</td>
                </tr>
                <tr style="border-top: 1px dashed #ccc;">
                    <td>Total Tresorerie Mobilisee :</td>
                    <td class="text-right font-monospace" style="font-weight: bold;">{{ number_format($totalTresorerieMobilisee, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td class="bold">Reste Net a Fournir au Retrait :</td>
                    <td class="text-right bold font-monospace" style="color: red;">{{ number_format($resteAFournir, 0, ',', ' ') }} F</td>
                </tr>

                <!-- Message d'alerte comptable inaltérable selon l'issue financière -->
                <tr style="border-top: 1px solid #000;">
                    <td colspan="2" style="font-size: 9px; padding-top: 5px; text-align: justify; line-height: 1.2;">
                        @if($futurSoldeSimule < 0)
                            <strong style="color: red;">ATTENTION :</strong> Si le reste a fournir de 
                            <strong>{{ number_format($resteAFournir, 0, ',', ' ') }} F</strong> n'est pas integralement regle en especes ou mobile lors du retrait des boissons, la difference passera automatiquement en dette sur le compte du client. Le solde de son compte epargne sera alors credite a 
                            <strong>-{{ number_format(abs($futurSoldeSimule), 0, ',', ' ') }} FCFA</strong>.
                        @else
                            <strong>INFORMATION :</strong> L'epargne actuelle du client associee a son avance couvre le montant total de la facture. Lors de la livraison, son compte epargne sera debite du reste et son nouveau solde sera de 
                            <strong>+{{ number_format($futurSoldeSimule, 0, ',', ' ') }} FCFA</strong>.
                        @endif
                    </td>
                </tr>


            {{-- ==================================================================== --}}
            {{-- CAS 3 : CLIENT DE PASSAGE / WALK-IN (COMPTOIR PUR)                  --}}
            {{-- ==================================================================== --}}
            @else
                @php
                    $monnaieRendue = (float)$vente->montant_verse - (float)$vente->montant_total;
                @endphp
                <tr>
                    <td>Cash Encaisse Comptoir :</td>
                    <td class="text-right font-monospace">{{ number_format($vente->montant_verse, 0, ',', ' ') }} F</td>
                </tr>
                <tr>
                    <td class="bold">Monnaie rendue en especes :</td>
                    <td class="text-right bold font-monospace">{{ number_format(max(0, $monnaieRendue), 0, ',', ' ') }} F</td>
                </tr>
            @endif

        </table>
    </div>

    <!-- VENTILATION DES EMBALLAGES (CASIERS 12 ET 24 SEPAREMENT) -->
    @php
        $totalCasiers12 = 0;
        $totalCasiers24 = 0;

        foreach($vente->detailVentes as $detail) {
            // Lecture directe de la valeur stricte de votre option format
            $format = $detail->produit->format_p; 
            $quantiteVendue = (float)$detail->quantite;

            if ($format === 'casier 24') {
                $totalCasiers24 += $quantiteVendue;
            } elseif ($format === 'casier 12') {
                $totalCasiers12 += $quantiteVendue;
            }
        }
    @endphp

    <div style="font-size: 10px; margin-top: 5px; padding: 4px; border: 1px dashed #000;">
        <table style="width: 100%;">
            <tr style="font-weight: bold;">
                <td colspan="2" style="text-align: center; border-bottom: 1px solid #000; padding-bottom: 2px;">BILAN LOGISTIQUE DES EMBALLAGES</td>
            </tr>
            <tr style="padding-top: 4px;">
                <td>Casiers de 12 sortis :</td>
                <td class="text-right bold">{{ $totalCasiers12 > 0 ? number_format($totalCasiers12, 2, ',', ' ') : '0,00' }} cs</td>
            </tr>
            <tr>
                <td>Casiers de 24 sortis :</td>
                <td class="text-right bold">{{ $totalCasiers24 > 0 ? number_format($totalCasiers24, 2, ',', ' ') : '0,00' }} cs</td>
            </tr>
        </table>
    </div>
    <div style="margin-bottom: 6px;"></div>


    <div class="dashed-hr"></div>

    <!-- PIED DE PAGE -->
    <div class="center" style="margin-top: 10px; font-size: 9px; font-style: italic;">
        <span class="bold">Merci de votre confiance !</span><br>
        <span>Les emballages restent la propriété du dépôt.</span><br>
        <small style="font-size: 8px; color: #555; display: block; margin-top: 4px;">Facture PDF générée par le système</small>
    </div>

</body>
</html>
