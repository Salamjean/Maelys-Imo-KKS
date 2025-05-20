<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #02245b; color: white; padding: 15px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nouveau message de {{ $userName }}</h1>
        </div>
        
        <div class="content">
            <p><strong>De :</strong> {{ $userName }} ({{ $userEmail }})</p>
            <p><strong>Objet :</strong> {{ $subject }}</p>
            
            <hr>
            
            <p>{{ $content }}</p>
        </div>
        
        <div class="footer">
            <p>Merci,<br>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>