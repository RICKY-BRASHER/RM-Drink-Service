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
            setTimeout(() => {
                existingToast.css('display','flex').removeClass('toast-out');
            }, 700); // Affiche le toast et réinitialise l'animation
            hideToast(existingToast);
        }   
    });
</script>
</body>
</html>