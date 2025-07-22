<!DOCTYPE html>
<html>
<head>
    <title>Code de vérification</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .code { 
            font-size: 24px; 
            font-weight: bold; 
            letter-spacing: 3px; 
            text-align: center;
            padding: 10px;
            background: #f4f4f4;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bonjour,</h2>
        
        <p>Voici votre code de vérification pour accéder à l'état des lieux :</p>
        
        <div class="code">{{ $code }}</div>
        
        <p>Ce code est valable jusqu'au {{ $expiresAt }}.</p>
        
        <p>Merci de ne pas partager ce code avec d'autres personnes.</p>
        
        <p>Cordialement,<br>
        L'équipe de gestion</p>
    </div>
</body>
</html>