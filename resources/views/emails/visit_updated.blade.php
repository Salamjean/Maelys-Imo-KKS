<!DOCTYPE html>
<html>
<head>
    <title>Modification de votre visite</title>
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
            background-color: #e67e22;
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
        .change-box {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            background: #fff9f2;
        }
        .old-rdv, .new-rdv {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .old-rdv {
            background-color: #f2dede;
            border-left: 4px solid #d9534f;
            text-decoration: line-through;
        }
        .new-rdv {
            background-color: #dff0d8;
            border-left: 4px solid #5cb85c;
            font-weight: bold;
        }
        .info-label {
            font-weight: bold;
            color: #e67e22;
            display: inline-block;
            width: 120px;
        }
        .agent-contact {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin:0;">Modification de votre visite</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $details['nom'] }},</p>
            
            <p>Nous vous informons d'un changement concernant votre visite programmée pour le bien :</p>
            <h3 style="color:#e67e22;">{{ $details['bien'] }}</h3>
            
            <div class="change-box">
                <div class="old-rdv">
                    <p><strong>Ancien rendez-vous :</strong></p>
                    <p><span class="info-label">Date :</span> {{ $details['old_date'] }}</p>
                    <p><span class="info-label">Heure :</span> {{ $details['old_time'] }}</p>
                </div>
                <p>Nous avons dû modifier la date et l'heure de votre visite pour la raison de : <br> <span><strong>{{ $details['motif'] }}</strong></span>.</p>
                <div class="new-rdv">
                    <p><strong>Les détails du nouveau rendez-vous :</strong></p>
                    <p><span class="info-label">Date :</span> {{ $details['new_date'] }}</p>
                    <p><span class="info-label">Heure :</span> {{ $details['new_time'] }}</p>
                </div>
            </div>
            
            <p>Nous nous excusons sincèrement pour ce changement et faisons notre possible pour minimiser ce type de désagrément.</p>
            
            
            <p>Nous vous remercions pour votre compréhension et restons à votre disposition pour toute information complémentaire.</p>
            
            <p>Cordialement,<br>
        </div>
        

    </div>
</body>
</html>