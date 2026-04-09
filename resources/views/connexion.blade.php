<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion-RM DRINK</title>
    <link rel="stylesheet" href="{{asset('css\login.css')}}">
</head>
<body>
    <form id="connectetoi">
        @csrf
        <h1>RM-DRINK SERVICE</h1>
        <span class="bnv">Bon Retour</span>
        <br><span>C'est un plaisir de vous revoir</span>
        <p><input type="text" id="nom" name="nom" placeholder="Email ou Numero telepone" style="margin: 15px 0px" value=""></p>
        <p><input type="password" id="password" name="mdp" placeholder="Mot de passe"></p>
        <p class="forgot"><a href="#">Mot de Passe oubliee</a></p>
        <p><button type="button" id="envoyer">LOGIN</button></p>
        <p class="text">Pas de Compte ?<a href="{{ route('inscrit') }}">Creer un compte</a></p>
    </form>
    <div class="toast" id="toast">
        <p><img src="{{asset('img\cloche-de-notification.png')}}" alt="">RM-Manager</p>
        @if(session('error'))
            <span><img src="{{asset('img\message-derreur.png')}}" id="img"><span id="text">{{ session('error') }}</span></span>
        @elseif(session('success'))
            <span><img src="{{asset('img\verifier.png')}}" id="img"><span id="text">{{ session('success') }}</span></span>
        @else
            <span><img src="{{asset('img\verifier.png')}}" id="img"><span id="text"></span></span>
        @endif
    </div>
</body> 
<script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
<script>
    $(document).ready(function(){
            // 1. FONCTION GLOBALE : Pour que tout le monde puisse l'utiliser
        function hideToast(toastElement) {
            setTimeout(() => { 
                toastElement.addClass('toast-out'); 
            }, 4000);
            setTimeout(() => { 
                toastElement.hide(); 
            }, 5500);
        }
        let existingToast = $('#toast');
        let text = $('#text').text();
        if(text.length > 0 ) {
            existingToast.css('display','flex').removeClass('toast-out'); // Affiche le toast et réinitialise l'animation
            hideToast(existingToast);
        }   
        
        $("#envoyer").click(function(e) {
            e.preventDefault(); // Empêche le rechargement classique
            var formData = new FormData($("#connectetoi")[0]);
            
            $.ajax({
                url: '/connecte',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    if(response.success) {
                        if(response.redirect) {
                            // On remplace la page pour effacer l'historique
                            window.location.replace(response.redirect);
                        }
                    } else {
                        if(response.errors) {
                            let toast = $('#toast'); 
                            let url = "{{asset('img/message-derreur.png')}}"; // Image de notification
                            $('#text').text(response.errors);   
                            $('#img').attr('src', url); // Image d'erreur
                            toast.css('display','flex').removeClass('toast-out');
                            setTimeout(() => { toast.addClass('toast-out'); }, 4000);
                            setTimeout(() => { toast.css('display','none'); }, 5500);
                            $('#connectetoi')[0].reset();
                        } else {
                            alert("Une erreur est survenue");
                        }
                    }
                    
                },
               
            });
            
        });
    });

</script>
</html>