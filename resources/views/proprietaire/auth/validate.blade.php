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
    <title>Inscrire un propriétaire</title>
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <style>
    body {
    display: flex;
    align-items: center;
    /* Remplacez le dégradé par l'image */
    background-image: url("{{ asset('assets/images/proprio.png') }}");
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
      <h1 class="text-white h3">Accès - Propriétaire</h1>
      </div>
      <form class="mt-4" action="{{ route('owner.validate', $email) }}" method="POST" id="registerForm">
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
          <input type="password" class="form-control " name="password" placeholder="Mot de passe">
        </div>
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-lock"></span>
          <input type="password" class="form-control " name="password_confirm" placeholder="Mot de passe de confirmation">
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