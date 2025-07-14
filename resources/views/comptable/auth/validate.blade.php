<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/mae-imo.png') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/bootstrap.min.css') }}">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/uf-style.css') }}">
    <title>Inscrire un comptable</title>
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <style>
    /* Style pour les boutons de visibilité des mots de passe */
      .password-toggle {
          cursor: pointer;
          background: transparent;
          border: none;
          color: #6c757d;
          transition: color 0.3s;
      }
      .password-toggle:hover {
          color: #495057;
      }
    body {
    display: flex;
    align-items: center;
    /* Remplacez le dégradé par l'image */
    background-image: url("{{ asset('assets/images/proo.png') }}");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    
    /* Overlay sombre pour améliorer la lisibilité */
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Ajustez l'opacité (0.5 = 50%) */
    z-index: -1;
}
</style>
  <body>
    <div class="uf-form-signin">
      <div class="text-center">
        <a href="#"><img src="{{ asset('assets/images/mae-imo.png') }}" alt="" style="border-radius: 30px" width="100" height="100"></a>
      <h1 class="text-white h3">Definir - accès - Agent</h1>
      </div>
      <form class="mt-4" action="{{ route('accounting.validate', $email) }}" method="POST" id="registerForm">
        @csrf
        @method('POST')
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-envelope"></span>
          <input type="text" class="form-control " name="email" value="{{ $email }}" readonly>
        </div>
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-lock"></span>
          <input type="text" class="form-control " name="code" value="{{ old('code') }}" placeholder="Code de confirmation">
         
        </div>
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-lock"></span>
          <input type="password" class="form-control " id="password-field" name="password" placeholder="Mot de passe">
           <button type="button" class="input-group-text password-toggle" id="toggle-password">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-lock"></span>
          <input type="password" class="form-control " name="password_confirm" id="password-confirm-field" placeholder="Mot de passe de confirmation">
          <button type="button" class="input-group-text password-toggle" id="toggle-password-confirm">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div class="d-grid mb-4">
          <button type="submit" class="btn uf-btn-primary btn-lg">S'inscrire</button>
        </div>
      </form>
    </div>

    <!-- JavaScript -->
    <!-- Separate Popper and Bootstrap JS -->
    <script src="{{ asset('login/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('login/assets/js/bootstrap.min.js') }}"></script>

    <!-- Script pour basculer la visibilité des mots de passe -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
          // Fonction pour basculer la visibilité
          function togglePasswordVisibility(fieldId, toggleId) {
              const passwordField = document.getElementById(fieldId);
              const togglePassword = document.getElementById(toggleId);
              const eyeIcon = togglePassword.querySelector('i');
              
              togglePassword.addEventListener('click', function() {
                  const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                  passwordField.setAttribute('type', type);
                  
                  // Changer l'icône
                  if (type === 'password') {
                      eyeIcon.classList.remove('fa-eye-slash');
                      eyeIcon.classList.add('fa-eye');
                  } else {
                      eyeIcon.classList.remove('fa-eye');
                      eyeIcon.classList.add('fa-eye-slash');
                  }
              });
          }
          
          // Appliquer aux deux champs
          togglePasswordVisibility('password-field', 'toggle-password');
          togglePasswordVisibility('password-confirm-field', 'toggle-password-confirm');
      });
    </script>
    @if($errors->any())
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          html: `{!! implode('<br>', $errors->all()) !!}`,
          confirmButtonText: 'OK'
        });
      </script>
    @endif
  </body>
</html>