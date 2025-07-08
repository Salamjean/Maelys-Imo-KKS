<!DOCTYPE html>
<html>
<head>
    <title>Code de vérification</title>
</head>
<body>
    <h2>Bonjour,</h2>
    <p>Un paiement par chèque a été initié en votre nom.</p>
    <p>Pour valider ce paiement, veuillez fournir le code suivant à l'agence :</p>
    
    <div style="font-size: 24px; font-weight: bold; margin: 20px 0;">
        {{ $code }}
    </div>
    
    <p>Ce code est nécessaire pour confirmer le paiement.</p>
    
    <p>Cordialement,<br>Votre agence immobilière</p>
</body>
</html>