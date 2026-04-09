@include('depot')
<div class="main" id="main">
    <div class="nv-cadre" id="nv-cadre">
        <div class="nv">
            <span class="nv-titre" id="titre_client"> 👥 Nouveau Client</span>
            <form id="form-client">
                @csrf
                <div class="nv-info">
                    <div>
                        <label for="ref">Code Client</label>
                        <br><input type="text" name="ref_disable" id="ref_disable" placeholder="CL#DP12" maxlength="10" disabled>
                        <input type="hidden" name="ref_c" id="ref_c">
                    </div>
                    <div>
                        <label for="ref">NOM du client</label>
                        <br><input type="text" name="nom_c" id="nom_c" placeholder="John Terry" maxlength="20">
                    </div>
                </div>
                <div class="nv-info">
                    <div>
                        <label for="ref">contact</label>
                        <br><input type="number" name="tel_c" id="tel_c" placeholder="6 75 06 45 12" maxlength="12">
                    </div>
                    <div>
                        <label for="ref">Adresse</label>
                        <br><input type="text" name="adresse_c" id="adresse_c" placeholder="Sable Bonamoussadi" maxlength="20">
                    </div>
                </div>
                <p style="color: red; text-align: center;" id="error"></p>
                <div class="nv-button">
                    <button class="nv-annule" id="nvc-annule" type="button">Annuler</button>
                    <button class="nv-enregistre" id="nvc-enregistre" type="button">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    <div class="dash-head position">
        <h1>Gestion Des Clients</h1>
        <button id="nv-client" class="nouveau">+ Nouvau client</button>
    </div>
    <div class="block">
        <div class="block-contain">Liste Client <input class="search-input" placeholder="🔍  Rechercher..." oninput="filterTable('produits', this.value)"></div>
        <table id="table"> 
            <tr>
                <th></th>
                <th>Nom Client</th>
                <th>Contact</th>
                <th>Adresse</th>
                <th>Achat</th>
                <th>Solde Compte</th>
                <th>Action</th>
            </tr>
            @foreach($client as $client)
                <tr id="client{{ $client->id_c }}">
                    <td><img src="{{asset('img/client.png')}}" alt="" style="opacity: 1;"></td>
                    <td>{{ $client->nom_c }}</td>
                    <td>{{ $client->tel_c }}</td>
                    <td>{{ $client->adresse_c }}</td>
                    <td><span class="achat">0 Achats</span></td>
                    <td><span class="depense">{{ $client->solde_c }} FCFA</span></td>
                    <td><img src="{{asset('img/stylo.png')}}" alt="" class="updateclient" data-id = '{{$client->id_c}}'><img src="{{asset('img/supprimer.png')}}" alt="" data-id="{{$client->id_c}}" class="delete_client"></td>
                </tr>
            @endforeach
        </table>
    </div>
    <script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
<script>
    $(document).ready(function(){
        $('#nv-client').click(function(){
            $code = genererMatricule();
            $('#ref_c').val($code);
            $('#ref_disable').val($code);
            $('#titre_client').text('👥 Nouveau Client');
            $('#nv-cadre').hide().css('display', 'flex').fadeIn(400);
        });

        $(window).on('click', function(event) {
            if (event.target.id === 'nv-cadre') {
                $('#nv-cadre').fadeOut(400);
                $('#error').text('');
                $('#form-client')[0].reset();
            }
        });
        $('#nvc-annule').click(function(){
            $('#nv-cadre').fadeOut(400);
            $('#error').text('');
            $('#form-client')[0].reset();
        });
        function genererMatricule() {
            const prefixe = "CL";
            const caractères = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            let suite = "";
            
            // On génère 5 caractères aléatoires (ex: DP125)
            for (let i = 0; i < 8; i++) {
                suite += caractères.charAt(Math.floor(Math.random() * caractères.length));
            }
            
            return prefixe + suite;
        }
        $(document).on('click', '#nvc-enregistre', function(e){
            e.preventDefault();
            var formData = new FormData($('#form-client')[0]);
            $.ajax({
                url: '/addclient',
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
        $(document).on('click', '.delete_client', function(){
            var clientId = $(this).data('id');
            var row = $('#client'+clientId);
            if(confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
                $.ajax({
                    url: '/deleteclient/' + clientId,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if(response.success) {
                            row.fadeOut(700, function() {
                                $(this).remove();
                            });
                            $('#text').text('Client supprimé avec succès !');
                            $('#toast').css('display','flex').removeClass('toast-out');
                            setTimeout(() => { $('#toast').addClass('toast-out'); }, 3000);
                            setTimeout(() => { $('#toast').css('display','none').text(''); }, 4500);
                        } else {
                            alert(response.error)
                        }
                    },
                });
            }
        });
        $(document).on('click', '.updateclient', function(){
            var clientId = $(this).data('id');
            $.ajax({
                url: '/getclient/' + clientId,
                type: 'GET',
                success: function(response) {
                    if(response.success) {
                        var client = response.client;
                        $('#ref_disable').val(client.id_c);
                        $('#ref_c').val(client.id_c);
                        $('#nom_c').val(client.nom_c);
                        $('#tel_c').val(client.tel_c);
                        $('#adresse_c').val(client.adresse_c);
                        $('#titre_client').text('👥 Modifier Client');
                        $('#nv-cadre').css('display', 'flex').hide().fadeIn(500);
                    } else {
                        alert('Erreur lors de la récupération des données du client.');
                    }
                },

            });
        });
    });
</script>
@include('footer')