<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion-RM DRINK</title>
    <link rel="stylesheet" href="{{asset('css\login.css')}}">
</head>
<body>
    <form  id="formInscription">
        @csrf
        <h1>RM-DRINK SERVICE</h1>
        <span class="bnv">Bienvenue</span>
        <br><span>C'est un plaisir de vous Connaitre</span>
        <div><label for="">Nom Utilisateur</label><br><input type="text" id="nom" name="nom" placeholder="John Terry" value="{{old('nom')}}" maxlength="15"></div>
        <div><label for="">Adresse Mail</label><br><input type="email" id="mail" name="email" placeholder="abcd@gmail.com" value="{{old('email')}}"></div>
        <div><label for="">Adresse </label><br><input type="text" id="localisation" name="localisation" placeholder="Douala Pk14" value="{{old('localisation')}}" maxlength="15"></div>
        <div><label for="">Numero Telephone</label><br><input type="text" id="contact" name="contact_u" placeholder="ex: 636554884" value="{{old('contact_u')}}"></div>
        <div><label for="">Mot de passe</label><br><input type="password" id="password" name="mdp" placeholder="Votre mot de passe"></div>
        <p><button type="button" id="envoyer">Inscription</button></p>
        <p class="text">Deja un compte ?<a href="{{ route('connexion') }}">Se connecter</a></p>
    </form>
    <div class="toast" id="toast">
        <p><img src="{{asset('img\cloche-de-notification.png')}}" alt="">RM-Manager</p>
        <span><img src="{{asset('img\verifier.png')}}" id="img"><span id="text"><span></span></span>
    </div>
</body> 
<script src="{{asset('js/jquery-3.7.1.min.js')}}"></script>
<script>
    $(document).ready(function(){

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
            var formData = new FormData($("#formInscription")[0]);
            
            $.ajax({
                url: '/inscrit',
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
                            $('#formInscription')[0].reset();
                        } else {
                            showToast("Une erreur est survenue");
                        }
                    }
                    
                },
               
            });
        });
    });

</script>
</html>