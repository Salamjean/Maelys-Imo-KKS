<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Agence</title>
    
    <!-- Favicon -->
   <link rel="icon" type="image/png" href="{{ asset('assets/images/mae-imo.png') }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/uf-style.css') }}">
    
    <!-- SweetAlert2 CSS (chargé en dernier pour priorité) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<style>
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

/* Style pour le bouton de visibilité du mot de passe */
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
</style>
<body class="bg-dark">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-12 col-lg-5">
                <div class="uf-form-signin p-4 rounded shadow">
                    <!-- Logo et Titre -->
                    <div class="text-center mb-4">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/mae-imo.png') }}" alt="" style="border-radius: 30px" width="100" height="100">
                        </a>
                        <h1 class="text-white h3 mb-0">Connexion - Agence</h1>
                    </div>
                    
                    <!-- Formulaire -->
                    <form action="{{ route('agence.authenticate') }}" method="POST" novalidate>
                        @csrf
                        
                        <!-- Champ Email -->
                        <div class="input-group uf-input-group input-group-lg mb-3">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Email"
                                   required
                                   autofocus>
                        </div>
                        
                        <!-- Champ Mot de passe avec œil de visibilité -->
                        <div class="input-group uf-input-group input-group-lg mb-4">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password-field"
                                   name="password" 
                                   placeholder="Mot de passe"
                                   required>
                            <button type="button" class="input-group-text password-toggle" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                         <!-- Lien Mot de passe oublié -->
                        <div class="text-end mb-3">
                            <a href="{{ route('password.request') }}" class="text-white">Mot de passe oublié ?</a>
                        </div>
                        
                        <!-- Boutons -->
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn uf-btn-primary btn-lg py-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                            </button>
                            
                            <a href="{{ url('/') }}" class="btn btn-outline-light btn-lg py-3">
                                <i class="fas fa-home me-2"></i> Retour à l'accueil
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('login/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('login/assets/js/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script pour basculer la visibilité du mot de passe -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password-field');
            const togglePassword = document.getElementById('toggle-password');
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
        });
    </script>

    <!-- Gestion des erreurs -->
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Erreur de connexion',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
    @endif

    <!-- Pop-up de succès après connexion -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Connexion réussie',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                showConfirmButton: true,
                confirmButtonColor: '#3085d6'

            });
        });
    </script>
    @endif
    <!-- Pop-up de succès après connexion -->
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                showConfirmButton: true,
                confirmButtonColor: '#3085d6'

            });
        });
    </script>
    @endif
</body>
</html>