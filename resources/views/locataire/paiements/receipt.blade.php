<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de paiement</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; letter-spacing : 10px }
        .info { margin: 30px 0; }
        .info-item { margin-bottom: 10px; }
        .signature { margin-top: 50px; }
        .footer { margin-top: 50px; font-size: 12px; text-align: center; }
        .border-top { border-top: 1px solid #000; padding-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">QUITTANCE DE LOYER</div>
    </div>

   <div style="border:2px; border-style: solid; border-color: #000; border-radius:10px; margin-bottom: 20px; text-align: center;">
     <p style="color:#02245b">Quittance de loyer du mois de {{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</p>
   </div>

   <div>
    <p>ADRESSE DE LA LOCATION :</p>
     <div>
        <p>Bien situé à {{ $bien->commune }}, 
        <p>Description : {{ $bien->description }}</p>
     </div>
   </div>

   <div>
    Je soussigné(e) {{ $locataire->prenom }} {{ $locataire->name }} , propriétaire du bien susmentionné, reconnais avoir reçu la somme de
    <strong>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</strong> pour le paiement du loyer du mois de
    <strong>{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</strong>.
    <p>Fait à {{ $bien->commune }}, le {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}.</p>

   </div>
    <table>
        <tr>
            <th>Locataire</th>
            <td>{{ $locataire->prenom }} {{ $locataire->name }}</td>
        </tr>
        <tr>
            <th>Bien loué</th>
            <td>{{ $bien->commune }} ({{ $bien->type }})</td>
        </tr>
        <tr>
            <th>Période couverte</th>
            <td>{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</td>
        </tr>
        <tr>
            <th>Date de paiement</th>
            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Méthode de paiement</th>
            <td>{{ $paiement->methode_paiement }}</td>
        </tr>
        <tr>
            <th>Montant</th>
            <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div class="signature">
        <div class="border-top" style="width: 300px; margin-left: auto;">
            <div style="text-align: center;">Signature</div>
        </div>
    </div>

    <div class="footer">
        <p>Ce document constitue un reçu officiel de paiement de loyer.</p>
        <p>Merci pour votre confiance.</p>
    </div>
</body>
</html>