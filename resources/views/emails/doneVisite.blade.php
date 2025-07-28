<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de visite effectuée</title>
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
            background-color: #27ae60;
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
            color: #27ae60;
            display: inline-block;
            width: 100px;
        }
        .cta-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 15px 0;
            font-weight: bold;
        }
        .cta-button:hover {
            background-color: #219653;
        }
        .agent-signature {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin:0;">Merci pour votre visite</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $visite->nom }},</p>
            
            <p>Nous vous remercions d'avoir choisi notre agence pour visiter le bien suivant :</p>
            
            <div class="bien-card">
                <h3 style="margin-top:0;color:#27ae60;">{{ $bien->type }} à {{ $bien->commune }}</h3>
                @if($bien->reference)
                    <p><span class="info-label">Référence :</span> {{ $bien->reference }}</p>
                @endif
                @if($bien->prix)
                    <p><span class="info-label">Prix :</span> {{ number_format($bien->prix, 0, ',', ' ') }} FCFA</p>
                @endif
                @if($bien->superficie)
                    <p><span class="info-label">Superficie :</span> {{ $bien->superficie }} m²</p>
                @endif
            </div>
            
            <p>Nous espérons que cette visite a répondu à vos attentes et nous restons à votre disposition pour :</p>
            <ul style="padding-left:20px;">
                <li>Toute information complémentaire sur ce bien</li>
                <li>Organiser une nouvelle visite si nécessaire</li>
                <li>Vous proposer d'autres biens correspondant à vos critères</li>
            </ul>
            
            <div style="text-align:center;">
                <a href="mailto:contact@agence.sn" style="color: white" class="cta-button">Contacter notre équipe</a>
            </div>
            
            <div class="agent-signature">
                <p>Votre conseiller dédié :</p>
                <p><strong>[Nom du conseiller]</strong></p>
                <p>Téléphone : <a href="tel:+221338699999">+221 33 86 99 999</a></p>
                <p>Email : <a href="mailto:conseiller@agence.sn">conseiller@agence.sn</a></p>
            </div>
            
            <p>Merci pour votre confiance et à très bientôt,</p>
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