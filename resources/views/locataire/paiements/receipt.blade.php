<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quittance de loyer</title>
       <link rel="icon" type="image/png" href="{{ asset('assets/images/mae-imo.png') }}">
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
        .receipt-container::before {
            content: "";
            position: absolute;
            top: 25%;
            left: 10%;
            width: 80%;
            height: 50%;
            background-image: url(assets/images/mae-imo.png);
            background-size: cover;
            background-position: center;
            opacity: 0.1;
            z-index: -1;
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
            margin-bottom: 10px;
        }
        .section-title {
            font-weight: bold;
            color: #02245b;
            margin-bottom: 6px;
            font-size: 13px;
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
            margin-top: 15px;
            font-size: 10px;
            text-align: center;
            color: #777;
            padding-top: 8px;
        }
    

         .signature {
            font-family: 'Courier New', Courier, monospace; /* Exemple de police */
            font-size: 16px; /* Taille de la police */
            margin-top: 20px; /* Espacement au-dessus de la signature */
        }

        .signature-text {
            font-weight: bold; /* Texte en gras */
        }

        .signature-line {
            border-top: 1px solid #000; /* Ligne de signature */
            text-align: center; /* Centrer le texte */
            margin-top: 10px; /* Espacement au-dessus de la ligne */
            padding-top: 5px; /* Espacement à l'intérieur de la ligne */
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="title">QUITTANCE DE LOYER</div>
            <div style="font-size: 15px; color: #000; margin-top: 5px; font-weight:bold">
                Réf: {{ $paiement->reference }}
            </div>
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

        <div class="section">
            <div class="section-title">DÉCLARATION</div>
            <p>
                Je soussigné(e) 
                @if($paiement->bien->agence_id && $paiement->bien->agence)
                    {{ $paiement->bien->agence->name }}, Agence
                @elseif($paiement->bien->proprietaire_id && $paiement->bien->proprietaire)
                    {{ $paiement->bien->proprietaire->name.' '.$paiement->bien->proprietaire->prenom }}, Propriétaire
                @else
                    Maelys-imo, Agence
                @endif/
                bailleur du bien ci-dessus désigné, déclare avoir reçu de :
            </p>
            <div class="highlight-box">
                <strong>Monsieur/Madame :</strong> {{ $paiement->bien->locataire ? $paiement->bien->locataire->name . ' ' . $paiement->bien->locataire->prenom : 'il n\'est plus locataire'  }}<br>
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
                <span class="info-label">Mode de paiement :</span>
                <span>{{ $paiement->methode_paiement }}</span>
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

       <div class="section">
            <p>Fait à {{ $paiement->bien->commune }}, le {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}.</p>
            
            <div class="signature">
                <p class="signature-text">
                   @if($paiement->bien->agence_id && $paiement->bien->agence)
    {{ $paiement->bien->agence->name }}, Agence
@elseif($paiement->bien->proprietaire_id && $paiement->bien->proprietaire)
    {{ $paiement->bien->proprietaire->name.' '.$paiement->bien->proprietaire->prenom }}, Propriétaire
@else
    Maelys-imo, Agence
@endif
                </p>
                <div class="signature-line">Signature</div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 5px;">
        <div style="font-size: 11px; color: #555; margin-bottom: 5px;">
                Scannez ce code pour vérifier
            </div>
            <img src="{{ $qrCode }}" alt="QR Code du reçu" style="width: 80px; height: 80px; border: 1px solid #eee; padding: 5px;">
            
        </div>

        <div class="footer">
            <p>Article 285 du Code pénal. Cet article stipule qu'une personne est coupable d'infractions en rapport avec la falsification,
                la reproduction ou l'usage de faux documents dans des contextes où ces actes sont destinés à induire en erreur
                l'autorité publique ou des tiers.
                Est puni de deux (02) à dix (10) ans et une amende de 200 000 à 2 000 000 de franc CFA.</p>
            <p>Ce document constitue un reçu officiel de paiement de loyer.</p>
            <p>Merci pour votre confiance.</p>
        </div>
    </div>
</body>
</html>