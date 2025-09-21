<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation de mot de passe</title>
</head>
<body>
    <h2>Bonjour {{ $name }},</h2>
    <p>Votre code de réinitialisation de mot de passe est :</p>
    <h1 style="font-size: 32px; letter-spacing: 5px; color: #2563eb;">{{ $otp }}</h1>
    <p>Ce code est valide pendant 15 minutes.</p>
    <p>Si vous n'avez pas demandé de réinitialisation, veuillez ignorer cet email.</p>
</body>
</html>