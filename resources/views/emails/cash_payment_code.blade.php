<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .payment-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            background-color: #f9f9f9;
        }
        .code-display {
            font-size: 24px;
            letter-spacing: 3px;
            color: #2c3e50;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f0f7fd;
            border-left: 4px solid #3498db;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .amount-highlight {
            color: blue;
            font-weight: bold;
            font-size: 18px;
        }
        ul {
            margin-top: 5px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <h1>🏦 Confirmation de votre code de paiement</h1>

    <p>Bonjour {{ $locataire->prenom }} {{ $locataire->nom }},</p>

    <p>Votre demande de paiement en espèces a bien été enregistrée. Voici les détails de votre transaction :</p>

    <div class="payment-card">
        <p><strong>Montant à régler :</strong> <span class="amount-highlight">{{ number_format($montant, 0, ',', ' ') }} FCFA</span></p>
        <p><strong>Date limite de validité :</strong> {{ $expiration }}</p>
        <strong>Votre code de paiement</strong> : <span style="color:#27ae60"><strong>{{ $code }}</strong></span>
    </div>

    <div class="warning-box">
        <strong>⚠️ Sécurité importante :</strong>
        <ul style="margin-bottom:0;">
            <li>Ce code est strictement personnel</li>
            <li>Ne le communiquez à personne par téléphone ou email</li>
            <li>Seul un agent habilité doit vous le demander</li>
            <li>Validez l'identité de l'agent avant paiement</li>
        </ul>
    </div>

    <p>Pour finaliser votre paiement, présentez ce code à l'agent de l'agence immobilière.</p>
</body>
</html>