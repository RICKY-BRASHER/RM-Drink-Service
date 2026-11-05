@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h4 class="fw-bold text-dark m-0"><i class="bi bi-tags text-primary me-2"></i>Promotions des dépôts</h4>
            <small class="text-muted">Offres cumulées par dépôt, appliquées automatiquement à la caisse.</small>
        </div>
    </div>

    <div class="alert alert-info">
        Le stock n’est pas réservé : les quantités et cadeaux affichés sont une estimation à partir du stock physique. La caisse contrôle le stock réel au moment de la vente.
    </div>

    <div class="row g-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="bi bi-plus-circle-fill text-success me-2"></i>Créer une offre
                </h6>

                <form id="formCreatePromotion">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Type d’offre :</label>
                        <select name="type" id="selectPromoType" class="form-select" required>
                            <option value="quantite_offerte">Achetez X, recevez Y offerts</option>
                            <option value="reduction_prix">Prix réduit à partir d’un seuil</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Dépôt :</label>
                        <select name="depot_id" id="selectPromoDepot" class="form-select" required>
                            <option value="">-- Choisir un dépôt --</option>
                            @foreach($depots as $depot)
                                <option value="{{ $depot->id }}">{{ $depot->nom_depot }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Produits regroupés dans cette promotion :</label>
                        <select name="produits[]" id="selectPromoProduits" class="form-select font-monospace" multiple size="7" required disabled>
                            @foreach($ProduitsParDepot as $depotId => $products)
                                @foreach($products as $product)
                                    <option value="{{ $product->id_produit }}" data-depot="{{ $depotId }}">
                                        {{ $product->nom_p }} ({{ $product->format_p }}) — {{ number_format($product->prix_p, 0, ',', ' ') }} F
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Cette sélection constitue la catégorie de l’offre. Le seuil cumule ses quantités; les prix catalogue doivent être identiques. Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs références.</small>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Quantité achetée :</label>
                            <input type="number" name="seuil" class="form-control text-center fw-bold" value="10" min="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Quantité offerte :</label>
                            <input type="number" name="offert" id="promoGiftQuantity" class="form-control text-center fw-bold text-success" value="1" min="1">
                        </div>
                        <div class="col-6 mb-3 d-none" id="groupPromoPrice">
                            <label class="form-label small fw-bold text-secondary">Prix réduit (F / unité) :</label>
                            <input type="number" name="prix_promo" id="promoPrice" class="form-control text-center fw-bold text-primary" min="0" step="1">
                        </div>
                    </div>
                    <small class="text-muted d-none mb-3" id="promoPriceHelp">Dès que le seuil cumulatif est atteint, toutes les unités des produits sélectionnés passent à ce tarif.</small>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Date début :</label>
                            <input type="date" name="date_debut" class="form-control font-monospace" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">Date fin :</label>
                            <input type="date" name="date_fin" class="form-control font-monospace" value="{{ date('Y-m-d', strtotime('+1 month')) }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3" id="btnSubmitPromo">
                        <i class="bi bi-lightning-fill me-1"></i> Enregistrer l’offre
                    </button>
                </form>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
                    <i class="bi bi-list-stars me-2"></i>Campagnes et suivi du stock
                </h6>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 0.88rem;">
                        <thead>
                            <tr class="bg-light small text-muted">
                                <th>Dépôt</th>
                                <th>Produits admissibles</th>
                                <th class="text-center">Règle</th>
                                <th class="text-center">Stock physique</th>
                                <th class="text-center">Stock / estimation</th>
                                <th class="text-center">Période</th>
                                <th class="text-center">Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($promotions as $promo)
                                @php
                                    $eligibleProducts = $promo->produits;
                                    if ($eligibleProducts->isEmpty() && $promo->produit) {
                                        $eligibleProducts = collect([$promo->produit]);
                                    }
                                    $legacyBrand = $eligibleProducts->isEmpty() ? $promo->marque : null;
                                @endphp
                                <tr id="promoRow_{{ $promo->id_promo }}">
                                    <td>{{ $promo->depot?->nom_depot ?? 'Tous les dépôts (ancienne offre)' }}</td>
                                    <td>
                                        @forelse($eligibleProducts as $product)
                                            <span class="badge bg-light text-dark border mb-1">{{ $product->nom_p }} ({{ $product->format_p }})</span>
                                        @empty
                                            <span class="text-muted">{{ $legacyBrand ? 'Marque : ' . $legacyBrand : 'Cible non définie' }}</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        @if($promo->type === 'quantite_offerte')
                                            <span class="badge bg-success-subtle text-success">{{ $promo->seuil }} achetés + {{ $promo->offert }} offert(s)</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary">{{ $promo->seuil }}+ unités : {{ number_format($promo->prix_promo, 0, ',', ' ') }} F</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace">{{ number_format($promo->stock_eligible, 0, ',', ' ') }}</td>
                                    <td class="text-center font-monospace">
                                        @if($promo->type === 'quantite_offerte')
                                            {{ number_format($promo->cadeaux_estimables, 0, ',', ' ') }} cadeau(x) estimé(s)
                                            <small class="text-muted d-block">{{ number_format($promo->cadeaux_distribues, 0, ',', ' ') }} distribué(s)</small>
                                        @else
                                            <span class="text-muted">Réduction selon stock disponible</span>
                                        @endif
                                    </td>
                                    <td class="text-center small">
                                        {{ date('d/m/Y', strtotime($promo->date_debut)) }} – {{ date('d/m/Y', strtotime($promo->date_fin)) }}
                                    </td>
                                    <td class="text-center">
                                        <input class="form-check-input toggle-promo-status" type="checkbox"
                                            data-id="{{ $promo->id_promo }}" {{ $promo->est_active ? 'checked' : '' }}>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-light border text-danger delete-promo-trigger"
                                            data-id="{{ $promo->id_promo }}" aria-label="Supprimer la promotion">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        Aucune promotion n’est configurée.
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
    const depotSelect = document.getElementById('selectPromoDepot');
    const productSelect = document.getElementById('selectPromoProduits');
    const allProductOptions = Array.from(productSelect.options).map((option) => option.cloneNode(true));

    depotSelect.addEventListener('change', function() {
        productSelect.replaceChildren();
        productSelect.disabled = !this.value;

        allProductOptions
            .filter((option) => option.dataset.depot === this.value)
            .forEach((option) => productSelect.appendChild(option.cloneNode(true)));
    });

    function showPromoError(xhr, fallback) {
        const errors = xhr.responseJSON?.errors;
        const message = errors
            ? Object.values(errors).flat().join('\n')
            : (xhr.responseJSON?.message || fallback);

        Swal.fire({
            icon: 'warning',
            title: 'Promotion non enregistrée',
            text: message,
            confirmButtonText: 'Compris'
        });
    }

    $('#selectPromoType').on('change', function() {
        const giftOffer = this.value === 'quantite_offerte';
        $('#promoGiftQuantity').prop('required', giftOffer);
        $('#promoPrice').prop('required', !giftOffer);
        $('#groupPromoPrice, #promoPriceHelp').toggleClass('d-none', giftOffer);
        $('#promoGiftQuantity').closest('.col-6').toggleClass('d-none', !giftOffer);
    }).trigger('change');

    $('#formCreatePromotion').on('submit', function(event) {
        event.preventDefault();

        const form = $(this);
        const button = $('#btnSubmitPromo');
        button.prop('disabled', true).text('Enregistrement...');

        $.ajax({
            url: "{{ route('promotions.store') }}",
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Promotion enregistrée',
                        text: response.message,
                        timer: 1400,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function(xhr) {
                showPromoError(xhr, 'Impossible d’enregistrer cette promotion.');
                button.prop('disabled', false).html('<i class="bi bi-lightning-fill me-1"></i> Enregistrer l’offre');
            }
        });
    });

    $(document).on('click', '.delete-promo-trigger', function() {
        const id = $(this).data('id');
        const row = $('#promoRow_' + id);

        Swal.fire({
            icon: 'warning',
            title: 'Supprimer cette promotion ?',
            text: 'Si elle figure déjà sur une facture, désactivez-la pour préserver l’historique.',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Garder',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: '/Distrib237/admin/promotions/supprimer/' + id,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        row.remove();
                        Swal.fire({ icon: 'success', title: 'Promotion supprimée', timer: 1200, showConfirmButton: false });
                    }
                },
                error: function(xhr) {
                    showPromoError(xhr, 'Impossible de supprimer cette promotion.');
                }
            });
        });
    });

    $(document).on('change', '.toggle-promo-status', function() {
        const checkbox = $(this);
        const previousValue = !checkbox.prop('checked');

        $.ajax({
            url: '/Distrib237/admin/promotions/toggle/' + checkbox.data('id'),
            type: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            error: function(xhr) {
                checkbox.prop('checked', previousValue);
                showPromoError(xhr, 'Impossible de modifier le statut de la promotion.');
            }
        });
    });
});
</script>
@endpush
