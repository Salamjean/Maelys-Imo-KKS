<!DOCTYPE html>
<html>
<head>
    <title>Maelys-Imo - Confirmation d'inscription Locataire</title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 5px;
        }
        .button {
            background-color: #02245b;
            color: white !important;
            padding: 12px 25px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777777;
        }
        .code {
            font-size: 24px;
            letter-spacing: 3px;
            color: #02245b;
            font-weight: bold;
            padding: 10px;
            background-color: #f0f0f0;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $logoUrl }}" alt="Logo Maelys-Imo" class="logo">
        </div>
        
        <div class="content">
            <h1 style="color: #02245b;">Bienvenue chez {{ $agenceName }}</h1>
            <p>Bonjour,</p>
            <p>L'agence <strong>{{ $agenceName }}</strong> vous a enregistré comme locataire sur notre plateforme.</p>
            
            <h2 style="color: #02245b;">Validation de votre compte</h2>
            <p>Pour activer votre compte locataire, veuillez :</p>
            <ol>
                <li>Cliquer sur le bouton ci-dessous</li>
                <li>Saisir le code de validation suivant :</li>
            </ol>
            
            <div class="code">{{ $code }}</div>
            
            <p style="text-align: center;">
                <a href="{{ url('/validate-locataire-account/' . $email) }}" class="button">Valider mon compte</a>
            </p>
            
            <p>Merci d'utiliser notre plateforme.</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Maelys-Imo. Tous droits réservés.</p>
            <p>Si vous n'êtes pas à l'origine de cette inscription, veuillez ignorer cet email.</p>
        </div>
    </div>
</body>
</html>