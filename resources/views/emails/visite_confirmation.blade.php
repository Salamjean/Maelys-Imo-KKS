<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de demande de visite</title>
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
        .bien-card {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            background: #f9f9f9;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 100px;
        }
        .rdv-details {
            background-color: #f0f7fd;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
        }
        .agent-info {
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
            <h2 style="margin:0;">Confirmation de votre demande de visite</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $visite->nom }},</p>
            
            <p>Nous accusons réception de votre demande de visite pour le bien suivant :</p>
            
            <div class="bien-card">
                <h3 style="margin-top:0;color:#2c3e50;">{{ $bien->type }} à {{ $bien->commune }}</h3>
                <p><span class="info-label">Superficie :</span> {{ $bien->superficie }} m²</p>
                <p><span class="info-label">Prix :</span> {{ number_format($bien->prix, 0, ',', ' ') }} FCFA</p>
                @if($bien->reference)
                    <p><span class="info-label">Référence :</span> {{ $bien->reference }}</p>
                @endif
            </div>
            
            <div class="rdv-details">
                <h4 style="margin-top:0;color:#2980b9;">Votre demande de rendez-vous</h4>
                <p><span class="info-label">Date souhaitée :</span> {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}</p>
                <p><span class="info-label">Heure souhaitée :</span> {{ $visite->heure_visite }}</p>
            </div>
            
            <p>Notre équipe traite actuellement votre demande et vous contactera dans les plus brefs délais pour confirmer définitivement ce rendez-vous.</p>
            
            <p>En attendant notre confirmation, nous vous invitons à :</p>
            <ul style="padding-left:20px;">
                <li>Préparer vos questions pour la visite</li>
                <li>Vérifier votre disponibilité à la date demandée</li>
            </ul>
            
            <p>Nous vous remercions pour votre confiance et restons à votre disposition pour toute information complémentaire.</p>
            
            <p>Cordialement,<br>
            <strong>L'équipe de votre agence immobilière</strong></p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} 
                @if($bien->agence_id)
                    {{ $bien->agence->name ?? 'Votre Agence Immobilière' }}
                @elseif($bien->proprietaire_id)
                    @if($bien->proprietaire->gestion == 'agence')
                        Maelys-imo
                    @else
                        {{ $bien->proprietaire->name.' '.$bien->proprietaire->prenom ?? 'Maelys-imo' }}
                    @endif
                @else
                    Maelys-imo
                @endif
                . Tous droits réservés.
            </p>
            <p>
                <a href="tel:+33123456789" style="color:#5cb85c;text-decoration:none;">
                    @if($bien->agence_id)
                        {{ $bien->agence->contact ?? 'Votre Agence Immobilière' }}
                            @elseif($bien->proprietaire_id)
                                @if($bien->proprietaire->gestion == 'agence')
                                    +225 27 22 36 50 27
                                @else
                                    {{ $bien->proprietaire->contact ?? '+225 27 22 36 50 27' }}
                                @endif
                            @else
                                +225 27 22 36 50 27
                    @endif
                </a> | 
                <a href="#" style="color:#5cb85c;text-decoration:none;">
                    @if($bien->agence_id)
                        {{ $bien->agence->email ?? 'contact@maelysimo.com' }}
                            @elseif($bien->proprietaire_id)
                                @if($bien->proprietaire->gestion == 'agence')
                                    contact@maelysimo.com
                                @else
                                    {{ $bien->proprietaire->email ?? 'contact@maelysimo.com' }}
                                @endif
                            @else
                                contact@maelysimo.com
                    @endif
                </a>
            </p>
            <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        </div>
    </div>
</body>
</html>