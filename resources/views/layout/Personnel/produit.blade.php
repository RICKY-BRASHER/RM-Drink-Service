@extends('layout.Personnel.depot')

@section('content')
<div class="container-fluid p-0">

    <!-- BARRE D'ENTÊTE SUPERIEURE -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Catalogue des Produits</h1>
            <small class="text-muted">Référencement général des boissons et prix de base</small>
        </div>
        <!-- Bouton d'ouverture du formulaire (Modal Bootstrap) -->
        <button type="button" class="btn btn-danger px-4 py-2 fw-bold shadow-sm rounded-3 small" id="btnOpenNewProductModal">
            <i class="bi bi-box-seam-fill me-2"></i> Nouveau Produit
        </button>
    </div>

    <!-- BLOC TABLEAU : LISTE COMPLÈTE DES PRODUITS -->
@foreach ($produitsParFournisseur as $nomFournisseur => $listeProduits)
    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
        <!-- Header de la Card avec couleur distinctive selon le fournisseur -->
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 bg-primary-subtle text-primary rounded-3 text-center" style="width: 38px; height: 38px;">
                    <i class="bi bi-building fs-5"></i>
                </div>
                <h5 class="fw-bold text-dark m-0 text-uppercase tracking-wide" style="font-size: 1.05rem;">
                    {{ $nomFournisseur }}
                </h5>
            </div>
            <span class="badge bg-light text-primary border border-primary border-opacity-10 rounded-pill px-3 py-2 fw-semibold">
                {{ count($listeProduits) }} {{ count($listeProduits) > 1 ? 'boissons' : 'boisson' }}
            </span>
        </div>

        <div class="card-body p-0">
            <!-- 💡 CONTENEUR AVEC MAXIMUM DE HAUTEUR ET SCROLL AUTO -->
            <div class="table-responsive scrollable-table-container" style="max-height: 480px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0">
                    <!-- 💡 L'entête reste fixe pendant le scroll de la molette -->
                    <thead class="table-light text-secondary uppercase fw-semibold sticky-top bg-light" style="font-size: 0.85rem; top: 0; z-index: 10;">
                        <tr>
                            <th scope="col" class="ps-4 py-3" style="width: 40%;">Désignation / Nom</th>
                            <th scope="col" class="py-3" style="width: 15%;">Format</th>
                            <th scope="col" class="py-3 text-center" style="width: 15%;">Prix Achat</th>
                            <th scope="col" class="py-3 text-center" style="width: 15%;">Prix Vente</th>
                            <th scope="col" class="text-end py-3 pe-4" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0" style="font-size: 0.9rem;">
                        @foreach ($listeProduits as $produit)
                            <tr id="productRow_{{ $produit->id_produit }}" class="product-row align-middle">
                                <!-- Colonne Nom -->
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-primary-emphasis"><i class="bi bi-droplet-fill"></i></span>
                                        <span class="fw-bold text-dark">{{ strtoupper($produit->nom_p) }}</span>
                                    </div>
                                </td>

                                <!-- Colonne Format -->
                                <td class="py-3">
                                    <span class="badge bg-light text-primary border rounded-3 px-2 py-1.5 fw-medium text-capitalize">
                                        <i class="bi bi-box-seam me-2"></i> {{ strtoupper($produit->format_p) }}
                                    </span>
                                </td>

                                <!-- Colonne Prix Achat -->
                                <td class="py-3 text-center font-monospace text-secondary fw-medium">
                                    {{ number_format($produit->prix_achat_p, 0, ',', ' ') }} <small class="fw-semibold">FCFA</small>
                                </td>

                                <!-- Colonne Prix Vente -->
                                <td class="py-3 text-center font-monospace fw-bold text-dark">
                                    {{ number_format($produit->prix_p, 0, ',', ' ') }} <small class="text-primary fw-semibold">FCFA</small>
                                </td>

                                <!-- Colonne Actions -->
                                <td class="text-end py-3 pe-4">
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- Bouton Modifier -->
                                        <button type="button" class="btn btn-icon btn-light border btn-sm rounded-3 edit-product-btn text-primary border-primary" 
                                            data-id="{{ $produit->id_produit }}"
                                            data-name="{{ $produit->nom_p }}"
                                            data-format="{{ $produit->format_p }}"
                                            data-supplier="{{ $produit->id_fournisseur }}"
                                            data-buying="{{ $produit->prix_achat_p }}"
                                            data-selling="{{ $produit->prix_p }}"
                                            data-bs-toggle="tooltip" title="Modifier la boisson">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach


</div>

<!-- MODAL BOOTSTRAP UNIQUE : CRÉATION / MODIFICATION PRODUIT -->
<div class="modal fade" id="productFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light pb-3">
                <h5 class="fw-bold text-dark m-0" id="productModalTitle">📦 Nouveau Produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <form id="productForm" method="POST" action="#">
                    @csrf
                    <!-- Champ masqué pour l'identifiant lors de la modification -->
                    <input type="hidden" name="id_p" id="input_id_p">

                    <!-- Ligne 1 : Nom et Format -->
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label form-label-custom mb-1">Nom de la Boisson</label>
                            <input type="text" class="form-control" name="nom_p" id="input_nom_p" placeholder="Ex: Beaufort Light">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-5">
                            <label class="form-label form-label-custom mb-1"><Type-Format></Type-Format></label>
                            <select class="form-select fw-semibold text-secondary" name="format_p" id="input_format_p">
                                <option value="" disabled selected> Choisir le Type et Format</option>
                                <option value="casier 12">Casier-12</option>
                                <option value="casier 24">Casier-24</option>
                                <option value="pack 6">Pack-6</option>
                                <option value="pack 12">Pack-12</option>
                                <option value="bidon">Bidon</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Ligne 2 : Fournisseur -->
                    <div class="mb-3">
                        <label class="form-label form-label-custom mb-1">Fournisseur / Distributeur</label>
                        <select class="form-select fw-semibold text-secondary" name="fournisseur_p" id="input_fournisseur_p">
                            <option value="" disabled selected>-- Choisir le Fournisseur --</option>
                            @foreach ($fournisseurs as $fournisseur)
                                <option value="{{ $fournisseur->id_fournisseur }}">{{ $fournisseur->nom_fournisseur }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Ligne 3 : Tarification Comptable -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Prix d'Achat Estimé (FCFA)</label>
                            <input type="number" class="form-control text-secondary" name="prix_achat_p" id="input_prix_achat_p" placeholder="0" min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label form-label-custom mb-1">Prix de Vente Conseillé (FCFA)</label>
                            <input type="number" class="form-control fw-bold text-dark" name="prix_p" id="input_prix_p" placeholder="0" min="0">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- Boutons de validation -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                        <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
 <script>
    $(document).ready(function() {

        // 1. FILTRAGE ET RECHERCHE LOCALE DANS LE TABLEAU SANS AJAX
        $('#searchProductInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#productTable tbody .product-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // 2. BOUTON NOUVEAU PRODUIT : Ouverture de la boîte
        $('#btnOpenNewProductModal').on('click', function() {
            // Configuration de la Modal en mode Création
            $('#productModalTitle').text('📦 Nouveau Produit');
            $('#productForm')[0].reset();
            $('#input_id_p').val('');

            $('#productFormModal').modal('show');
        });

        // 3. BOUTON MODIFIER : Capture des données "data-" et pré-remplissage
        $(document).on('click', '.edit-product-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var format = $(this).data('format');
            var supplier = $(this).data('supplier');
            var buying = $(this).data('buying');
            var selling = $(this).data('selling');

            // Pré-remplit la Modal en mode Édition
            $('#productModalTitle').text('📦 Modifier le Produit');
            $('#input_id_p').val(id);
            $('#input_nom_p').val(name);
            $('#input_format_p').val(format);
            $('#input_fournisseur_p').val(supplier);
            $('#input_prix_achat_p').val(buying);
            $('#input_prix_p').val(selling);

            $('#productFormModal').modal('show');
        });

        // 4. ACTION DE SUPPRESSION SIMULÉE (EFFACEMENT EN FONDU)
        $(document).on('click', '.delete-product-btn', function() {
            var id = $(this).data('id');
            var $row = $('#productRow_' + id);

            if (confirm("Voulez-vous vraiment retirer cette boisson de la liste générale du catalogue ?")) {
                $row.fadeOut(500, function() {
                    $(this).remove();
                    alert("Référence retirée du catalogue en local.");
                });
            }
        });

        // 5. SOUMISSION DU FORMULAIRE
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            var id = $('#input_id_p').val();
            var editMode = id !== ''; // Si l'ID est présent, c'est une modification
            $form = $(this);
            $form.find('.invalid-feedback').text(''); // Réinitialise les messages d'erreur
            $form.find('.is-invalid').removeClass('is-invalid'); // Réinitialise les classes d'erreur
            var formData = $form.serialize();
            $.ajax({
                url: "{{ route('produit.store') }}",
                type: 'POST',
                data: formData,
                success: function(response) {
                    window.location.replace(response.redirect);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            var inputField = $('#input_' + field);
                            inputField.addClass('is-invalid');
                            inputField.siblings('.invalid-feedback').text(messages[0]);
                        });
                    } else {
                        alert("Une erreur est survenue. Veuillez réessayer.");
                    }
                }
            });
        });

    });

 </script>

@endpush