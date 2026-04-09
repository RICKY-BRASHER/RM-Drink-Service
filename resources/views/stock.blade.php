@include('depot')
<div class="main" id="main">
    <div class="dash-head position">
        <h1>Gestion Du Stock</h1>
        <button id="nv-approv" class="nouveau">+ Approvisionnement</button>
    </div>
    <div class="block" style="height: 500px">
        <div class="block-contain"><select name="stock" id="stock" style="font-size: 14px; text-transform: uppercase; font-weight: bold; color: gray">
            <option value="Brasserie">Etat Stock Brasserie</option>
            <option value="Guinness">Etat Stock Guinness</option>
            <option value="Kadji">Etat Stock Kadji</option>
            <option value="SP">Etat Stock SP</option>
            <option value="Autre">Etat Stock Autre</option>
        </select> <input class="search-input" placeholder="🔍  Rechercher..." oninput="filterTable('produits', this.value)"></div>
        <table>
            <tr>
                <th>Ref</th>
                <th>Nom</th>
                <th>Fourniseur</th>
                <th>Stock Init</th>
                <th>Approv</th>
                <th>Stock Actuel</th>
                <th>Etat</th>
            </tr>
        </table>
    </div>
    
</div>
@include('footer')