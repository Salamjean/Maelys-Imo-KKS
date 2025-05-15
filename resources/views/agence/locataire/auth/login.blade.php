<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('login/assets/img/favicon.png') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/bootstrap.min.css') }}">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/uf-style.css') }}">
    <title>Connexion - Locataire</title>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  </head>
  <body>
    <div class="uf-form-signin">
      <div class="text-center">
        <a href="#"><img src="{{ asset('login/assets/img/logo-fb.png') }}" alt="" width="100" height="100"></a>
      <h1 class="text-white h3">Connexion - Locataire</h1>
      </div>
      <form class="mt-4" action="{{ route('locataire.authenticate') }}" method="POST" id="loginForm">
        @csrf
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-envelope"></span>
          <input type="text" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email">
        </div>
        <div class="input-group uf-input-group input-group-lg mb-3">
          <span class="input-group-text fa fa-lock"></span>
          <input type="password" class="form-control " name="password" placeholder="Mot de passe">
        </div>
        <div class="d-grid mb-4">
          <button type="submit" class="btn uf-btn-primary btn-lg">Se connecter</button>
        </div>
      </form>
    </div>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @if($errors->any())
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          html: `{!! implode('<br>', $errors->all()) !!}`,
          confirmButtonText: 'OK',
          confirmButtonColor: '#3085d6',
        });
      </script>
    @endif

    @if(session('account_error'))
      <script>
        const status = "{{ session('account_error.status') }}";
        const icon = status === 'Pas sérieux' ? 'warning' : 'error';
        const title = "{{ session('account_error.title') }}";
        const message = "{{ session('account_error.message') }}";
        
        Swal.fire({
          icon: icon,
          title: title,
          html: message,
          confirmButtonText: 'OK',
          confirmButtonColor: '#3085d6',
          backdrop: `
            rgba(0,0,0,0.7)
            url("{{ asset('login/assets/img/stop.png') }}")
            center top
            no-repeat
          `,
          customClass: {
            title: 'text-danger',
            confirmButton: 'btn btn-danger'
          }
        });
      </script>
    @endif

    <!-- JavaScript -->
    <script src="{{ asset('login/assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('login/assets/js/bootstrap.min.js') }}"></script>
  </body>
</html>