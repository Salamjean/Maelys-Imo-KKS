<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Locataire</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('login/assets/img/favicon.png') }}">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('login/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login/assets/css/uf-style.css') }}">
    
    <!-- SweetAlert2 CSS (chargé en dernier pour priorité) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="bg-dark">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Nouveau mot de passe</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('locataire.password.update') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <!-- Vos champs mot de passe -->
                        <div class="form-group">
                            <label>Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Réinitialiser</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>