@include('depot')
<div class="main" id="main">
    <div class="dash-head position">
        <h1>Presentation Historique</h1>
        <button id="nv-vente" class="nouveau">+ Nouvelle Vente</button>
    </div>
    <div class="block" style="height: 500px">
        <div class="block-contain"><select name="historique" id="historique" style="font-size: 14px; text-transform: uppercase; font-weight: bold; color: blueviolet">
            <option value="Ventes">Toutes les Ventes</option>
            <option value="Achats">Tous les Achats</option>
        </select> <input class="search-input" placeholder="🔍  Rechercher..." oninput="filterTable('produits', this.value)"></div>
        <table>
            <tr>
                <th>ID_V</th>
                <th>Nom</th>
                <th>Contact</th>
                <th>Nb Produit</th>
                <th>Total Vente</th>
                <th>Action</th>
            </tr>
        </table>
    </div>

</div>

@include('footer')