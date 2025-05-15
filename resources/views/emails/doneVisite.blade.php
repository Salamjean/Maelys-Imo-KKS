<!DOCTYPE html>
<html>
<head>
    <title>Visite effectuée</title>
</head>
<body>
    <h2>Votre visite a été effectuée par l'agence</h2>
    
    <p>Bonjour {{ $visite->nom }},</p>
    
    <p>Nous vous remercions d'avoir conctacter notre agence pour le bien :</p>
    <h3>{{ $bien->type }} à {{ $bien->commune }}</h3>
    <p>Merci a vous d'utiliser toujours notre service</p>
    
    <p>Cordialement,<br>
    L'équipe de votre agence immobilière</p>
</body>
</html>