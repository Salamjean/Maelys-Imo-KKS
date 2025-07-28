<!DOCTYPE html>
<html>
<head>
    <title>Nouveau message de contact - Maelys-IMO</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background-color: #02245b;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .email-header h2 {
            margin: 0;
            font-size: 24px;
            color: white;
        }
        
        .email-body {
            padding: 25px;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            color: #02245b;
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }
        
        .message-container {
            margin-top: 25px;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #ff5e14;
            border-radius: 4px;
        }
        
        .message-container h3 {
            color: #02245b;
            margin-top: 0;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .highlight {
            color: #ff5e14;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>Nouveau message de contact</h2>
        </div>
        
        <div class="email-body">
            <div class="info-item">
                <span class="info-label">Nom:</span>
                <span class="highlight">{{ $data['name'] }}</span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Email:</span>
                <a href="mailto:{{ $data['email'] }}" style="color: #ff5e14; text-decoration: none;">{{ $data['email'] }}</a>
            </div>
            
            <div class="info-item">
                <span class="info-label">Sujet:</span>
                {{ $data['subject'] }}
            </div>
            
            <div class="message-container">
                <h3>Message:</h3>
                <p style="white-space: pre-line;">{{ $data['message'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Message envoyé depuis le formulaire de contact de <span class="highlight">Maelys-IMO</span></p>
        <p>© {{ date('Y') }} Maelys-IMO. Tous droits réservés.</p>
    </div>
</body>
</html>