<!DOCTYPE html>
<html>
<head>
    <title>Modification de votre visite</title>
</head>
<body>
    <p>Bonjour {{ $details['nom'] }},</p>
    
    <p>Nous vous informons que votre visite prévue initialement le {{ $details['old_date'] }} à {{ $details['old_time'] }} 
    pour le bien "{{ $details['bien'] }}" a été modifiée.</p>
    
    <p><strong>Nouvelle date et heure:</strong> {{ $details['new_date'] }} à {{ $details['new_time'] }}</p>
    
    <p>Nous nous excusons pour ce changement et restons à votre disposition pour toute information complémentaire.</p>
    
    <p>Cordialement,<br>
    L'équipe de votre agence</p>
</body>
</html>