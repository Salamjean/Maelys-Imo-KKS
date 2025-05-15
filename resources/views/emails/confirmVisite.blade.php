<!DOCTYPE html>
<html>
<head>
    <title>Visite Confirmé</title>
</head>
<body>
    <h2>Votre visite a été confirmer par l'agence</h2>
    
    <p>Bonjour {{ $visite->nom }},</p>
    
    <p>Nous vous rappelons que votre visite a été confirmer pour le bien :</p>
    <h3>{{ $bien->type }} à {{ $bien->commune }}</h3>
    <p>Nous vous attendons pour la visite qui aura lieu :</p>
    <p>Date: {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}</p>
    <p>Heure: {{ $visite->heure_visite }}</p>
    
    <p>Cordialement,<br>
    L'équipe de votre agence immobilière</p>
</body>
</html>