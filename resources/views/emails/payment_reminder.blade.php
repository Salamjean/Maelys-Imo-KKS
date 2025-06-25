<!DOCTYPE html>
<html>
<head>
    <title>Rappel de paiement de loyer</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #2c3e50;
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
            color: #777;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        .payment-card {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            background: #f9f9f9;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .amount-highlight {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
        }
        .payment-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 160px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin:0;">Rappel de paiement de loyer</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $locataire->prenom }} {{ $locataire->nom }},</p>
            
            <p>Nous vous rappelons que le paiement de votre loyer pour le bien suivant est désormais dû :</p>
            
            <div class="payment-card">
                <p><span class="info-label">Adresse du bien :</span> {{ $locataire->bien->adresse_complete ?? $locataire->bien->commune }}</p>
                <p><span class="info-label">Référence :</span> {{ $locataire->bien->superficie ?? 'N/A' }}</p>
                
                @if($tauxMajoration > 0)
                <div class="warning-box">
                    <h4 style="margin-top:0;color:#e74c3c;">⚠️ Paiement en retard</h4>
                    <p><span class="info-label">Montant initial :</span> {{ number_format($montantOriginal, 0, ',', ' ') }} FCFA</p>
                    <p><span class="info-label">Majoration ({{ $tauxMajoration }}%) :</span> {{ number_format($nouveauMontant - $montantOriginal, 0, ',', ' ') }} FCFA</p>
                    <p><span class="info-label">Total à régler :</span> <span class="amount-highlight">{{ number_format($nouveauMontant, 0, ',', ' ') }} FCFA</span></p>
                </div>
                @else
                <p style="margin-top:15px;"><span class="info-label">Montant à payer :</span> {{ number_format($montantOriginal, 0, ',', ' ') }} FCFA</p>
                @endif
            </div>
            
            <p><strong>Modes de paiement acceptés :</strong></p>
            <ul>
                <li>💵 Paiement en espèces à l'agence (sur rendez-vous)</li>
                <li>📱 Paiement mobile (Wave, Orange Money, etc.) via votre plateforme locataire</li>
            </ul>
            
            <p>Pour toute question concernant votre loyer ou en cas de difficulté de paiement, n'hésitez pas à contacter.</p>
            
            <p>Nous vous remercions de régulariser votre situation dans les meilleurs délais.</p>
            
            <p>Cordialement,<br>
            <strong>Le service gestion locative</strong><br>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }}. Tous droits réservés.</p>
            <p>
                <a href="https://www.agence.sn" style="color:#2c3e50;text-decoration:none;">www.agence.sn</a> | 
                <a href="mailto:contact@agence.sn" style="color:#2c3e50;text-decoration:none;">contact@agence.sn</a>
            </p>
        </div>
    </div>
</body>
</html>