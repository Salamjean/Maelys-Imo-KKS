<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu de paiement</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
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
        <div class="title">REÇU DE PAIEMENT DE LOYER</div>
        <div>Agence Immobilière</div>
        <div>123 Avenue des Locations, Ville</div>
        <div>Tél: +123 456 789</div>
    </div>

    <div class="info">
        <div class="info-item"><strong>Référence:</strong> {{ $reference }}</div>
        <div class="info-item"><strong>Date d'émission:</strong> {{ $date_emission }}</div>
    </div>

    <table>
        <tr>
            <th>Locataire</th>
            <td>{{ $locataire->prenom }} {{ $locataire->name }}</td>
        </tr>
        <tr>
            <th>Bien loué</th>
            <td>{{ $bien->adresse }} ({{ $bien->type }})</td>
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