@include('depot')
<div class="main" id="main">
    <div class="nv-cadre" id="nv-cadre">
        <div class="nv">
            <span class="nv-titre" id="titre_produit"> 📦 Nouveau Produit</span>
            <form  id="form-produit">
                @csrf
                <div class="nv-info">
                    <div>
                        <label for="ref">Reference</label>
                        <br><input type="text" name="ref_disable" id="ref_disable" disabled>
                        <input type="hidden" name="ref" id="ref">
                    </div>
                    <div>
                        <label for="ref">NOM PRoduit</label>
                        <br><input type="text" name="nom_p" id="nom_p" placeholder="BEAUFORT ORDINAIRE" >
                    </div>
                </div>
                <div class="nv-info">
                    <div>
                        <label for="ref">Prix ( FCFA )</label>
                        <br><input type="number" name="prix_p" id="prix_p" placeholder="7800">
                    </div>
                    <div>
                        <label for="ref">stock initial</label>
                        <br><input type="text" name="stock_p" id="stock_p" placeholder="10">
                    </div>
                </div>
                <div class="nv-info">
                    <div>
                        <label for="ref">Fourniseur</label>
                        <br><select name="fournisseur" id="fournisseur">
                            <option value="">Choisir Fournisseur</option>
                            @foreach($fournisseur as $fournisseur)
                                <option value="{{ $fournisseur->ref_f }}">{{ $fournisseur->nom_f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ref">Categorie</label>
                        <br><select name="categorie" id="categorie">
                        <option value="C">CASIER</option>
                        <option value="P">PACK</option>
                        </select>
                    </div>
                </div>
                <p style="color: red; text-align: center;" id="error"></p>
                <div class="nv-button">
                    <button class="nv-annule" id="nv-annule"  type="button">Annuler</button>
                    <button class="nv-enregistre" id="nv-enregistre" type="button">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <div id="chaine">
        <div class="dash-head position">
            <h1>Gestion Des Produits</h1>
            <button id="nv-prod" class="nouveau">+ Nouvau Produit</button>
        </div>
        <div class="block" id="block">
            <div class="block-contain">Liste Produit <input class="search-input" placeholder="🔍  Rechercher..." oninput="filterTable('produits', this.value)"></div>
            <table id="table">
                <tr>
                    <th></th>
                    <th>Nom</th>
                    <th>Fourniseur</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
                @foreach($produit as $produit)
                    <tr id="produit{{ $produit->id_p }}">
                        <td><img src="{{asset('img/bieres.png')}}" alt="" style="opacity: 1;"></td>
                        <td style="text-transform: uppercase; font-weight: bold;">{{ $produit->nom_p }}</td>
                        <td>{{ $produit->fournisseur->nom_f }}</td>
                        <td><span style="font-weight: bold;">{{ $produit->prix_p }}</span> FCFA</td>
                        <td style="font-weight: bold; font-family: cursive;">{{ $produit->qte_p }}</td>
                        <td><img src="{{asset('img/stylo.png')}}" alt="" class="update_produit" data-id = '{{$produit->id_p}}'><img src="{{asset('img/supprimer.png')}}" alt="" data-id="{{$produit->id_p}}" class="delete_produit"></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    
</div>
<script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $('#nv-prod').click(function(){
            $('#titre_produit').text('📦 Nouveau Produit');
            $('#nv-cadre').show();
            $('#ref_').val(genererMatricule());
            $('#ref_disable').val(genererMatricule());
            $('#chaine').css('filter', 'blur(5px)');
        });
        $('#nv-annule').click(function(){
            $('#nv-cadre').hide();
            $('#chaine').css('filter', 'none');
            $('#form-produit')[0].reset();
            $('#error').text('');
        });
        $(window).on('click', function(event) {
            if (event.target.id === 'nv-cadre') {
                $('#nv-cadre').fadeOut(400);
                $('#chaine').css('filter', 'none');
                $('#form-produit')[0].reset();
            }
        });
        function genererMatricule() {
            var prefix = 'PD-';
            var randomNumber = Math.floor(1000 + Math.random() * 9000);
            return prefix + randomNumber;
        }
        $(document).on('click', '#nv-enregistre', function(e){
            e.preventDefault();
            var formData = new FormData($('#form-produit')[0]);
            $.ajax({
                url: '/addproduit',
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,

                success: function(response){
                    if(response.success){
                        location.reload();
                    }else{
                        $('#error').text(response.errors);
                    }
                },
                
            });
        });
        $(document).on('click', '.delete_produit', function(){
            var id = $(this).data('id');
            var row = $('#produit'+ id);
            if(confirm('Voulez-vous vraiment supprimer ce produit ?')) {
                $.ajax({
                    url: '/deleteproduit/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            row.fadeOut(700, function() {
                                $(this).remove();
                            });
                            $('#text').text('Produit supprimé avec succès !');
                            $('#toast').css('display','flex').removeClass('toast-out');
                            setTimeout(() => { $('#toast').addClass('toast-out'); }, 3000);
                            setTimeout(() => { $('#toast').css('display','none').text(''); }, 4500);
                        } else {
                            alert('Erreur lors de la suppression du produit.');
                        }
                    },
                });
            }
        });
        $(document).on('click', '.update_produit', function(){
            var id = $(this).data('id');
            $.ajax({
                url: '/getproduit/' + id,
                type: 'GET',
                success: function(response) {
                    if(response.success) {
                        var produit = response.produit;
                        $('#titre_produit').text('✏️ Modifier Produit');
                        $('#ref').val(produit.id_p);
                        $('#ref_disable').val(genererMatricule());
                        $('#nom_p').val(produit.nom_p);
                        $('#prix_p').val(produit.prix_p);
                        $('#stock_p').val(produit.qte_p);
                        $('#fournisseur').val(produit.ref_f);
                        $('#categorie').val(produit.categorie);
                        $('#nv-cadre').show();
                        $('#chaine').css('filter', 'blur(5px)');
                    } else {
                        alert('Erreur lors de la récupération du produit.');
                    }
                },
            });
        });
    });
</script>
@include('footer')
