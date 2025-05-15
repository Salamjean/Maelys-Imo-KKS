<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de visite</title>
</head>
<body>
    <h2>Confirmation de votre demande de visite</h2>
    
    <p>Bonjour {{ $visite->nom }},</p>
    
    <p>Nous avons bien reçu votre demande de visite pour le bien suivant :</p>
    
    <h3>{{ $bien->type }} à {{ $bien->commune }}</h3>
    <p>Superficie: {{ $bien->superficie }} m²</p>
    <p>Prix: {{ number_format($bien->prix, 0, ',', ' ') }} FCFA</p>
    
    <h4>Détails de votre visite :</h4>
    <p>Date: {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}</p>
    <p>Heure: {{ $visite->heure_visite }}</p>
    
    <p>Nous vous contacterons dans les plus brefs délais pour confirmer ce rendez-vous.</p>
    
    <p>Cordialement,<br>
    L'équipe de votre agence immobilière</p>
</body>
</html>