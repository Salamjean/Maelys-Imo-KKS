<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de paiement</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap');
        
        body {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            line-height: 1.4;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 15px;
            background-color: #f9f9f9;
            font-size: 13px;
        }
        .receipt-container {
            background-color: white;
            border: 1px solid #e1e1e1;
            border-radius: 3px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #02245b;
            padding-bottom: 12px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #02245b;
            margin-bottom: 4px;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
        }
        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-weight: bold;
            color: #02245b;
            margin-bottom: 6px;
            font-size: 13px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 2px;
        }
        .info-item {
            margin-bottom: 5px;
            display: flex;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
        }
        .highlight-box {
            background-color: #f5f9ff;
            border-left: 2px solid #02245b;
            padding: 10px;
            margin: 12px 0;
            font-size: 12px;
        }
        .amount {
            font-size: 15px;
            font-weight: bold;
            color: #02245b;
        }
        .signature {
            margin-top: 35px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 180px;
            display: inline-block;
            margin-top: 25px;
            padding-top: 3px;
            text-align: center;
            font-size: 12px;
        }
        .footer {
            margin-top: 25px;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px dashed #eee;
            padding-top: 8px;
        }
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 12px 0;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="title">QUITTANCE DE LOYER</div>
            <div class="subtitle">Reçu officiel de paiement</div>
        </div>

        <div class="section">
            <div class="section-title">INFORMATIONS SUR LE PAIEMENT</div>
            <div class="highlight-box">
                Période couverte : <strong>{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</strong>
            </div>
        </div>

        <div class="section">
            <div class="section-title">ADRESSE DU BIEN</div>
            <div class="info-item">
                <span class="info-label">Localisation :</span>
                <span>{{ $bien->commune }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Description :</span>
                <span>{{ $bien->description }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="section-title">DÉCLARATION</div>
            <p>
                Je soussigné(e) 
                @if($locataire->agence_id)
                    {{ $locataire->agence->name ?? 'Maelys-Imo' }}, Agence
                @elseif($locataire->proprietaire_id)
                    {{ $locataire->proprietaire->name.' '.$locataire->proprietaire->prenom ?? 'Maelys-imo' }}, Propriétaire
                @else
                    Maelys-imo, Agence
                @endif/
                bailleur du bien désigné ci-dessus, déclare avoir reçu de :
            </p>
            <div class="highlight-box">
                <strong>Monsieur/Madame :</strong> {{ $locataire->name.' '.$locataire->prenom ?? 'Maelys-imo' }}<br>
                <strong>Montant :</strong> <span class="amount">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span><br>
                <strong>Pour :</strong> Paiement du loyer et charges pour {{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}
            </div>
        </div>

        <div class="section">
            <div class="section-title">DÉTAILS DU PAIEMENT</div>
            <div class="info-item">
                <span class="info-label">Montant du loyer :</span>
                <span>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date de paiement :</span>
                <span>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Heure de paiement :</span>
                <span>{{ $paiement->created_at->format('H:i') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <p>Fait à {{ $bien->commune }}, le {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}.</p>
            
            <div class="signature">
                <div class="signature-line">Signature</div>
            </div>
        </div>

        <div class="footer">
            <p>Ce document constitue un reçu officiel de paiement de loyer.</p>
            <p>Merci pour votre confiance.</p>
        </div>
    </div>
</body>
</html>