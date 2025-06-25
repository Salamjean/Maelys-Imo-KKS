<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de votre visite</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #444;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #5cb85c;
            color: white;
            padding: 25px;
            text-align: center;
        }
        .content {
            padding: 25px;
        }
        .footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #999;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        .bien-card {
            border-left: 4px solid #5cb85c;
            background: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
        }
        .rdv-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-label {
            font-weight: bold;
            color: #5cb85c;
            display: inline-block;
            width: 80px;
        }
        .map-link {
            color: #5cb85c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin:0;font-weight:500;">Confirmation de votre visite</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $visite->nom }},</p>
            
            <p>Nous avons le plaisir de vous confirmer votre visite pour le bien suivant :</p>
            
            <div class="bien-card">
                <h3 style="margin-top:0;color:#5cb85c;">{{ $bien->type }} à {{ $bien->commune }}</h3>
                @if($bien->reference)
                    <p><span class="info-label">Référence :</span> {{ $bien->reference }}</p>
                @endif
                @if($bien->prix)
                    <p><span class="info-label">Prix :</span> {{ number_format($bien->prix, 0, ',', ' ') }} Fcfa</p>
                @endif
            </div>
            
            <div class="rdv-box">
                <h4 style="margin-top:0;color:#2e7d32;">Détails de votre rendez-vous</h4>
                <p><span class="info-label">Date :</span> {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}</p>
                <p><span class="info-label">Heure :</span> {{ $visite->heure_visite }}</p>
                @if($bien->adresse_complete)
                    <p><span class="info-label">Adresse :</span> {{ $bien->adresse_complete }}</p>
                    <p style="margin-top:10px;">
                        <a href="https://maps.google.com/?q={{ urlencode($bien->adresse_complete) }}" class="map-link">
                            📍 Voir sur la carte
                        </a>
                    </p>
                @endif
            </div>
            
            <p><strong>Conseils pour votre visite :</strong></p>
            <ul style="padding-left:20px;">
                <li>Arrivez 5 minutes avant l'heure prévue</li>
                <li>Présentez-vous à l'accueil avec cette confirmation</li>
                <li>N'hésitez pas à préparer vos questions</li>
            </ul>
            
            <p>Nous sommes impatients de vous faire découvrir ce bien et restons à votre disposition pour toute information complémentaire.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe de votre agence immobilière</strong></p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Votre Agence Immobilière. Tous droits réservés.</p>
            <p>
                <a href="tel:+33123456789" style="color:#5cb85c;text-decoration:none;">01 23 45 67 89</a> | 
                <a href="mailto:contact@votreagence.com" style="color:#5cb85c;text-decoration:none;">contact@votreagence.com</a>
            </p>
            <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        </div>
    </div>
</body>
</html>