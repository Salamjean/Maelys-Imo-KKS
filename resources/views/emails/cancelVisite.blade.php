<!DOCTYPE html>
<html>
<head>
    <title>Visite Annulée</title>
</head>
<body>
    <h2>Votre visite a été annuler par l'agence</h2>
    
    <p>Bonjour {{ $visite->nom }},</p>
    
    <p>Nous vous rappelons que votre visite a été annulé pour le bien :</p>
    <h3>{{ $bien->type }} à {{ $bien->commune }}</h3>
    <p>pour plus d'informtions veuillez contactez l'agence </p>
    
    <p>Cordialement,<br>
    L'équipe de votre agence immobilière</p>
</body>
</html>