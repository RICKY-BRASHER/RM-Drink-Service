@include('depot')

<div class="main" id="main">
    <div class="dash-head position">
        <h1>Gestion Facturation</h1>
        <button id="facture" class="nouveau">Enregistrer</button>
    </div>
    <div class="block">
        <div class="block-contain" style="background-color: azure;height: 50px;padding-top: 10px">Client: <select name="client" id="client" style="text-transform: uppercase;">
            <option value="">Choisir Client</option>
            @foreach($client as $cl)
                <option value="{{ $cl->id_c }}">{{ $cl->nom_c }}</option>
            @endforeach
            </select>
            <span class="font"> FACTURE#438</span>
        </div>
        <div class="infovente">
            <div class="info">
                <label for="">Matricule</label>
                <br><input type="text" name="matricule" id="matricule" readonly>
            </div>
            <div class="info">
                <label for="">Nom </label>
                <br><input type="text" name="nom_c" id="nom_c" readonly>
            </div>
            <div class="info">
                <label for="">Adresse</label>
                <br><input type="text" name="adresse_c" id="adresse_c" readonly>
            </div>
            <div class="info">
                <label for="">Contact</label>
                <br><input type="text" name="contact_c" id="contact_c" readonly>
            </div>
        </div>
        <div class="block-contain" style="background-color: whitesmoke;height: 50px;padding-top: 10px; border-top: 0.1px solid; margin-top: 20px">Produits : <select name="produit" id="produit"  style="text-transform: uppercase;">
            <option value="">Choisir Boisson</option>
            @foreach($produit as $prod)
                <option value="{{ $prod->id_p }}">{{ $prod->nom_p }}</option>
            @endforeach
        </select></div>
        <div class="infovente">
            <div class="prod">
                <label for="">Nom</label>
                <br><input type="text" name="nom_p" id="nom_p" readonly>
            </div>
            <div class="prod">
                <label for="">Prix Unitaire</label>
                <br><input type="text" name="prix_p" id="prix_p" readonly>
            </div>
            <div class="prod">
                <label for="">Quantite</label>
                <br><input type="text" name="qte_p" id="qte_p">
            </div>
            <div class="prod">
                <label for="">Total</label>
                <br><input type="text" name="total" id="total" readonly>
            </div>
        </div>
        <div class="infovente">
            <div class="prod">
                <label for="">Status</label>
                <br><select name="etat" id="etat">
                    <option value="payer">Payee</option>
                    <option value="attente">En attente</option>
                </select>
            </div>
            <div class="prod">
                <label for="">Payement</label>
                <br><select name="payement" id="payement">
                    <option value="Espece">Espece</option>
                    <option value="OM-MOMO">Mobile</option>
                </select>
            </div>
            <div class="prod">
                <button type="button" id="annuler">Annuler</button>
            </div>
            <div class="prod">
                <button type="button" id="ajouter">Ajouter</button>
            </div>
        </div>
    </div>
    <div class="liste">
        <table>
            <tr>
                <th>Reference</th>
                <th>Quantite</th>
                <th>Nom Produit</th>
                <th>Prix Unitaire</th>
                <th>Prix Total</th>
            </tr>
        </table>
        <p>Total Facture : 0 FCFA</p>
    </div>
</div>
<script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $('#client').change(function(){
            var id_c = $(this).val();
            if(id_c == ''){
                $('#matricule').val('');
                $('#nom_c').val('');
                $('#adresse_c').val('');
                $('#contact_c').val('');
                return;
            }
            $.ajax({
                url: '/getclient/' + id_c,
                type: 'GET',
                
                success: function(response){
                    if(response.success){
                        $('#matricule').val(response.client.id_c);
                        $('#nom_c').val(response.client.nom_c);
                        $('#adresse_c').val(response.client.adresse_c);
                        $('#contact_c').val(response.client.tel_c);
                    }
                }
            });
        });
        $('#produit').change(function(){
            var id_p = $(this).val();
            if(id_p == ''){
                $('#nom_p').val('');
                $('#prix_p').val('');
                $('#qte_p').val('');
                $('#total').val('');
                return;
            }
            $.ajax({
                url: '/getproduit/' + id_p,
                type: 'GET',
                
                success: function(response){
                    if(response.success){
                        $('#nom_p').val(response.produit.nom_p);
                        $('#prix_p').val(response.produit.prix_p);
                    }
                }
            });
        });
    });
</script>
@include('footer')