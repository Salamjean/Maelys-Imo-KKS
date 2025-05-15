<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('login/assets/img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/bootstrap.min.css') }}">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/uf-style.css') }}">
    <title>Inscrire un comptable</title>
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body>
    <div class="uf-form-signin">
      <div class="text-center">
        <a href="#"><img src="{{ asset('login/assets/img/logo-fb.png') }}" alt="" width="100" height="100"></a>
      <h1 class="text-white h3">Definir - accès - comptable</h1>
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