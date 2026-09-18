@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 100vh;">
    
    <!-- 📄 EN-TÊTE -->
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="bi bi-tags text-primary me-2"></i>Catalogue des Promotions</h4>
            <small class="text-muted font-monospace">Configuration des casiers gratuits (Mix parfums) et des paliers de prix de gros</small>
        </div>
    </div>

    <div class="row g-4">
        <!-- 📝 BLOC DE GAUCHE : FORMULAIRE DE CRÉATION -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-plus-circle-fill text-success me-2"></i>Lancer une offre</h6>
                
                <form id="formCreatePromotion">
                    <!-- 💡 SÉLECTEUR DE DÉPÔT APPORTÉ A L'INTERFACE -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Dépôt d'affectation :</label>
                        <select name="depot_id" class="form-select font-monospace">
                            <option value="" selected>🌍 Tous les dépôts (Campagne Générale)</option>
                            @foreach($depots as $d)
                                <option value="{{ $d->id }}">🏬 Dépôt : {{ strtoupper($d->nom_depot) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 1. Type de Promotion -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Type de gratification :</label>
                        <select name="type" id="selectPromoType" class="form-select font-monospace" required>
                            <option value="quantite_offerte" selected>🎁 Articles offerts (Ex: 10 achetés 1 offert)</option>
                            <option value="reduction_prix">📉 Prix dégressif / Gros (Ex: Seuil franchi = moins cher)</option>
                        </select>
                    </div>

                    <!-- 2. Cible de la Promotion -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Cible de l'offre :</label>
                        <select name="cible" id="selectPromoCible" class="form-select font-monospace" required>
                            <option value="produit_unique" selected>🎯 Produit unique (Ex: Opur 1.5L uniquement)</option>
                            <option value="marque_globale">🍹 Marque complète (Ex: Tous les parfums Planet)</option>
                        </select>
                    </div>

                    <!-- 3. Champ dynamique : Sélecteur de Produit -->
                    <div class="mb-3" id="groupCibleProduit">
                        <label class="form-label small fw-bold text-secondary">Sélectionner la boisson :</label>
                        <select name="produit_id" id="selectPromoProduit" class="form-select font-monospace">
                            <option value="" data-depot="tous">-- Choisir un produit --</option>
                            @foreach($ProduitsParDepot as $depotId => $listeProduits)
                                @foreach($listeProduits as $p)
                                    <option value="{{ $p->id_produit }}" data-depot="{{ $p->depot_id }}">
                                        {{ $p->nom_p }} ({{ $p->format_p }})
                                    </option>
                                @endforeach
                            @endforeach
                        </select>

                    </div>


                    <!-- 4. Champ dynamique : Saisie de la Marque (Texte) -->
                    <div class="mb-3 d-none" id="groupCibleMarque">
                        <label class="form-label small fw-bold text-secondary">Mot-clé de la marque (Recherche textuelle) :</label>
                        <input type="text" name="marque" class="form-control font-monospace text-uppercase" placeholder="Ex: PLANET, TOP, DJINO">
                        <small class="text-muted d-block mt-1" style="font-size:0.7rem;">S'appliquera à toutes les boissons contenant ce mot dans leur nom.</small>
                    </div>

                    <div class="row g-2">
                        <!-- 5. Seuil de déclenchement -->
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Seuil (Qté Min) :</label>
                            <input type="number" name="seuil" class="form-control text-center font-monospace fw-bold" value="10" min="1" required>
                        </div>

                        <!-- 6. Récompense Dynamique A : Quantité Offerte -->
                        <div class="col-6 mb-3" id="groupRewardOffert">
                            <label class="form-label small fw-bold text-secondary">Qté Offerte :</label>
                            <input type="number" name="offert" class="form-control text-center font-monospace fw-bold text-success" value="1" min="1">
                        </div>

                        <!-- 6. Récompense Dynamique B : Prix Promo (Caché au départ) -->
                        <div class="col-6 mb-3 d-none" id="groupRewardPrix">
                            <label class="form-label small fw-bold text-secondary">Prix de gros (F) :</label>
                            <input type="number" name="prix_promo" class="form-control text-center font-monospace fw-bold text-primary" placeholder="1100" min="0">
                        </div>
                    </div>

                    <!-- 7. Dates de validité -->
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Date Début :</label>
                            <input type="date" name="date_debut" class="form-control small font-monospace" value="{{ date('Y-m-day') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Date Fin :</label>
                            <input type="date" name="date_fin" class="form-control small font-monospace" value="{{ date('Y-m-day', strtotime('+1 month')) }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow" id="btnSubmitPromo">
                        <i class="bi bi-lightning-fill me-1"></i> Activer la promotion
                    </button>
                </form>
            </div>
        </div>

        <!-- 📜 BLOC DE DROITE : LISTE DES CAMPAGNES EN COURS -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3"><i class="bi bi-list-stars me-2"></i>Registre des campagnes en cours</h6>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:0.88rem;">
                        <thead>
                            <tr class="bg-light font-monospace small text-muted">
                                <th>Portée</th>
                                <th class="ps-3">Cible / Marque</th>
                                <th>Type</th>
                                <th class="text-center">Seuil Min</th>
                                <th class="text-end">Gratification</th>
                                <th class="text-center">Période</th>
                                <th class="text-center">statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $promo)
                                <tr id="promoRow_{{ $promo->id_promo }}">
                                    <td>
                                        @if($promo->depot_id)
                                            <span class="badge bg-info bg-opacity-10 text-info border px-2 py-1 rounded-2 font-monospace" style="font-size: 0.72rem;">
                                                🏬 {{ $promo->depot->nom_depot }}
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border px-2 py-1 rounded-2 font-monospace" style="font-size: 0.72rem;">
                                                🌍 GÉNÉRALE
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Cible -->
                                    <td class="ps-3">
                                        @if($promo->produit_id)
                                            <strong class="text-dark">{{ $promo->produit->nom_p }}</strong>
                                            <small class="text-muted d-block font-monospace">Produit Unique</small>
                                        @else
                                            <span class="badge bg-dark bg-opacity-10 text-dark border px-2.5 py-1 rounded-3 fw-bold text-uppercase font-monospace">
                                                🍹 MARQUE : {{ $promo->marque }}
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Type -->
                                    <td>
                                        @if($promo->type === 'quantite_offerte')
                                            <span class="badge bg-success bg-opacity-10 text-success border px-2 py-1 rounded-pill small">🎁 Offert</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary border px-2 py-1 rounded-pill small">📉 Remise</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Seuil -->
                                    <td class="text-center fw-bold font-monospace text-secondary">
                                        {{ $promo->seuil }} unités
                                    </td>
                                    
                                    <!-- Gratification -->
                                    <td class="text-end fw-bold font-monospace">
                                        @if($promo->type === 'quantite_offerte')
                                            <span class="text-success">+{{ $promo->offert }} gratuit(s)</span>
                                        @else
                                            <span class="text-primary">{{ number_format($promo->prix_promo, 0, ',', ' ') }} F / u</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Période -->
                                    <td class="text-center small font-monospace text-muted">
                                        Du {{ date('d/m', strtotime($promo->date_debut)) }} au {{ date('d/m/Y', strtotime($promo->date_fin)) }}
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <!-- 💡 Le Toggle Button réactif -->
                                            <input class="form-check-input toggle-promo-status" type="checkbox" role="switch" 
                                                data-id="{{ $promo->id_promo }}" 
                                                {{ $promo->est_active ? 'checked' : '' }} 
                                                style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                        </div>
                                    
                                    </td>
                                    <td>
                                        <!-- Bouton supprimer classique conservé à côté -->
                                        <button type="button" class="btn btn-sm btn-light border text-danger rounded-3 delete-promo-trigger ms-2" data-id="{{ $promo->id_promo }}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted small">
                                        <i class="bi bi-tags-fill display-5 opacity-30 text-secondary d-block mb-2"></i>
                                        Aucune campagne promotionnelle n'est configurée actuellement.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var produitsParDepot = @json($ProduitsParDepot);

    document.querySelector('select[name="depot_id"]').addEventListener('change', function() {
        var depotId = this.value;
        var selectProduit = document.getElementById('selectPromoProduit');

        // On vide la liste actuelle
        selectProduit.innerHTML = '<option value="">-- Choisir un produit --</option>';

        if (depotId === "") {
            // 🌍 Tous les dépôts → on affiche tous les produits
            @foreach($ProduitsParDepot as $depotId => $listeProduits)
                @foreach($listeProduits as $p)
                    var opt = document.createElement('option');
                    opt.value = "{{ $p->id_produit }}";
                    opt.textContent = "{{ $p->nom_p }} ({{ $p->format_p }})";
                    selectProduit.appendChild(opt);
                @endforeach
            @endforeach
        } else {
            // 🏬 Dépôt spécifique → on affiche seulement les produits de ce dépôt
            if (produitsParDepot[depotId]) {
                produitsParDepot[depotId].forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id_produit;
                    opt.textContent = p.nom_p + " (" + p.format_p + ")";
                    selectProduit.appendChild(opt);
                });
            }
        }
    });

    // ====================================================================
    // 💡 INTERACTION FORMULAIRE : Affichage dynamique selon les sélections
    // ====================================================================
    
    // A. Permutation Type (Offert vs Réduction de prix)
    $('#selectPromoType').on('change', function() {
        if ($(this).val() === 'quantite_offerte') {
            $('#groupRewardOffert').removeClass('d-none');
            $('#groupRewardPrix').addClass('d-none').find('input').val('');
        } else {
            $('#groupRewardPrix').removeClass('d-none');
            $('#groupRewardOffert').addClass('d-none').find('input').val('');
        }
    });

    // B. Permutation Cible (Produit Unique vs Marque Globale)
    $('#selectPromoCible').on('change', function() {
        if ($(this).val() === 'produit_unique') {
            $('#groupCibleProduit').removeClass('d-none');
            $('#groupCibleMarque').addClass('d-none').find('input').val('');
        } else {
            $('#groupCibleMarque').removeClass('d-none');
            $('#groupCibleProduit').addClass('d-none').find('select').val('');
        }
    });

    // ====================================================================
    // ⚡ ENVOI ASYNCHRONE AJAX : CRÉATION DE LA PROMO
    // ====================================================================
    $('#formCreatePromotion').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitPromo');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Activation...');

        $.ajax({
            url: "{{ route('promotions.store') }}",
            type: "POST",
            data: form.serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(response) {
                if (response.success) {

                    location.reload();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="bi bi-lightning-fill me-1"></i> Activer la promotion');
                alert("Échec : " + (xhr.responseJSON?.message || "Erreur lors de la création de la promotion."));
            }
        });
    });

    // ====================================================================
    // ❌ SUPPRESSION D'UNE PROMO (AJAX)
    // ====================================================================
    $(document).on('click', '.delete-promo-trigger', function() {
        var idPromo = $(this).data('id');
        var row = $('#promoRow_' + idPromo);

        if (confirm("Voulez-vous vraiment stopper et supprimer définitivement cette campagne promotionnelle ?")) {
            $.ajax({
                url: "/Distrib237/admin/promotions/supprimer/" + idPromo,
                type: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        row.fadeOut(300, function() { $(this).remove(); });
                    }
                },
                error: function(xhr) {
                    alert("Erreur lors de la suppression.");
                }
            });
        }
    });

        // ====================================================================
    // 🎛️ TOGGLE SWITCH : ACTIVATION / DÉSACTIVATION RAPIDE DE PROMO
    // ====================================================================
    $(document).on('change', '.toggle-promo-status', function() {
        var checkbox = $(this);
        var idPromo = checkbox.data('id');

        $.ajax({
            url: "/Distrib237/admin/promotions/toggle/" + idPromo,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr) {
                // En cas d'échec réseau, on remet le bouton dans son état précédent
                checkbox.prop('checked', !checkbox.prop('checked'));
                alert("Impossible de modifier le statut de la promotion.");
            }
        });
    });

});
</script>
@endpush

