@extends('layout.personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- EN-TÊTE DE LA DIRECTION GÉNÉRALE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">📊 Console de Pilotage National</h1>
            <small class="text-muted">Vue consolidée de la trésorerie et de la logistique multi-dépôts (Distrib 237)</small>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-3 py-2 fw-bold font-monospace">
            <i class="bi bi-shield-check me-1"></i> Direction Générale Active
        </span>
    </div>

    <!-- ⚡ LIGNE 1 : LES KPI FINANCIERS MENTORÉS DE L'ENTREPRISE -->
    <div class="row g-3 mb-4">
        <!-- CA Journalier Global -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-success bg-opacity-10 text-success fs-3"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold font-monospace d-block" style="font-size:0.68rem;">CA Réseau (Jour)</small>
                        <h4 class="fw-bold m-0 font-monospace text-dark">{{ number_format($caGlobalJour, 0, ',', ' ') }} F</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- CA Mensuel Global -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-primary fs-3"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold font-monospace d-block" style="font-size:0.68rem;">CA Réseau (Mois)</small>
                        <h4 class="fw-bold m-0 font-monospace text-primary">{{ number_format($caGlobalMois, 0, ',', ' ') }} F</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Risque Client / Dettes cumulées -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-danger bg-opacity-10 text-danger fs-3"><i class="bi bi-exclamation-octagon"></i></div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold font-monospace d-block" style="font-size:0.68rem;">Encours Risque Dettes</small>
                        <h4 class="fw-bold m-0 font-monospace text-danger">{{ number_format($detteTotaleResseau, 0, ',', ' ') }} F</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Commandes en attente -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning fs-3"><i class="bi bi-phone-vibrate"></i></div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold font-monospace d-block" style="font-size:0.68rem;">Paniers Mobiles en attente</small>
                        <h4 class="fw-bold m-0 font-monospace text-dark">{{ $totalCommandesWebEnAttente }} bons</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ⚡ LIGNE 2 : INTERACTION DU CLASSEMENT DES SUCCURSALES (DÉPÔTS VEDETTES) -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8 col-lg-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-light py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-building-fill text-muted me-2"></i>Performance et Rentabilité par Dépôt</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light small text-uppercase font-monospace text-secondary">
                            <tr>
                                <th class="ps-4">Rang</th>
                                <th>Nom de la Succursale</th>
                                <th>Localisation</th>
                                <th class="text-center">Staff Affecté</th>
                                <th class="text-center">Réf Catalogue</th>
                                <th class="text-end pe-4">Chiffre d'Affaires (Mois)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performancesDepots as $index => $depot)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary"># {{ $index + 1 }}</td>
                                    <td><span class="fw-bold text-dark text-uppercase">{{ $depot->nom_depot }}</span></td>
                                    <td class="small text-secondary"><i class="bi bi-geo-alt text-danger me-1"></i>{{ $depot->adresse_depot }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border font-monospace px-2 py-1">{{ $depot->nb_agents }} Agent(s)</span></td>
                                    <td class="text-center font-monospace small fw-semibold">{{ $depot->total_references }} boissons</td>
                                    <td class="text-end font-monospace fw-bold text-dark pe-4 fs-6">{{ number_format($depot->ca_mensuel_depot, 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ⚡ REPARTITION DE ROTATION DES FOURNISSEURS -->
        <div class="col-xl-4 col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-transparent border-light py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-muted me-2"></i>Parts de Sortie Brasseurs</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <!-- Mettre à jour cette zone dans votre carte de répartition brasseurs -->
                    @forelse($partsBrasseurs as $brasseur)
                        <div>
                            <div class="d-flex justify-content-between small font-monospace fw-bold text-uppercase text-dark mb-1">
                                <!-- 💡 Changement de nom_f par nom_fournisseur -->
                                <span>{{ $brasseur->nom_fournisseur }}</span>
                                <span>{{ number_format($brasseur->volume_vendu, 0, ',', ' ') }} u</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 8px;">
                                @php
                                    $maxVendu = $partsBrasseurs->first()->volume_vendu ?? 1;
                                    $pctProgress = round(($brasseur->volume_vendu / $maxVendu) * 100);
                                @endphp
                                <div class="progress-bar rounded-pill bg-primary" role="progressbar" style="width: {{ $pctProgress }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-5">Aucun flux de brasserie audité.</div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

    <!-- ⚡ LIGNE 3 : HIT-PARADE NATIONAL DES PLUS GROS ACHETEURS DE CASIERS -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-header bg-transparent border-light py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i>Palmarès National Clientèle (Top 5 Réseau)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="table-light small text-uppercase font-monospace text-secondary">
                            <tr>
                                <th class="text-center" style="width: 80px;">Rang</th>
                                <th>Raison Sociale / Établissement</th>
                                <th>Dépôt d'Affectation</th>
                                <th class="text-center">Bons Envoyés</th>
                                <th class="text-center">État Compte Épargne</th>
                                <th class="text-end pe-4">Volume Financier Engagé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topAcheteursNationaux as $index => $acheteur)
                                <tr>
                                    <td class="text-center fw-bold text-dark fs-6">N° {{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-dark text-uppercase d-block">{{ $acheteur->nom_u }}</strong>
                                        <small class="text-muted font-monospace">Tél: +237 {{ $acheteur->tel_u }}</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border px-2.5 py-1 rounded-3 uppercase fw-bold"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $acheteur->nom_depot }}</span></td>
                                    <!-- 4. Nombre de bons facturés -->
                                    <td class="text-center font-monospace fw-semibold">
                                        {{ $acheteur->total_factures }} Achats
                                    </td>

                                    <!-- 5. État du Compte Épargne National -->
                                    <td class="text-center font-monospace fw-bold">
                                        @if($acheteur->solde_u < 0)
                                            <span class="text-danger">⚠️ Dette: {{ number_format(abs($acheteur->solde_u), 0, ',', ' ') }} F</span>
                                        @elseif($acheteur->solde_u > 0)
                                            <span class="text-success">💳 Solde: +{{ number_format($acheteur->solde_u, 0, ',', ' ') }} F</span>
                                        @else
                                            <span class="text-dark">⚖️ Équilibré</span>
                                        @endif
                                    </td>

                                    <!-- 6. Volume Financier Engagé (CA Généré) -->
                                    <td class="text-end font-monospace fw-extrabold text-dark pe-4 fs-6">
                                        {{ number_format($acheteur->chiffre_affaire_genere, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small">
                                        <i class="bi bi-trophy-fill fs-2 d-block mb-2 text-secondary opacity-30"></i>
                                        Aucun audit de fidélité nationale calculé pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> <!-- Fin de la Ligne 3 -->

</div> <!-- Fin du container-fluid global -->
@endsection
