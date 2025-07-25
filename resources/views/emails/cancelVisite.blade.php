<!DOCTYPE html>
<html>
<head>
    <title>Annulation de visite</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #444;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .email-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #d9534f;
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
            color: #999;
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
        .contact-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #02245b;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .contact-btn:hover {
            background-color: #02245b;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2>Annulation de votre visite</h2>
        </div>
        
        <div class="content">
            <p>Bonjour {{ $visite->nom }},</p>
            
            <p>Nous regrettons de vous informer que votre visite programmée a dû être annulée par notre agence.</p>
            
            <div class="bien-card">
                <h3 style="margin-top: 0; color: #d9534f;">{{ $bien->type }} à {{ $bien->commune }}</h3>
                @if($bien->prix)
                    <p><strong>Prix :</strong> {{ number_format($bien->prix, 0, ',', ' ') }} Fcfa</p>
                    <p><strong>Motif : {{ $visite->motif }}</strong></p>
                @endif
            </div>
            
            <p>Pour plus d'informations concernant cette annulation ou pour reprogrammer une visite, notre équipe reste à votre disposition :</p>
            
            <div style="text-align: center;">
                <a href="mailto:contact@votreagence.com" class="contact-btn">Contactez notre agence</a>
            </div>
            
            <p>Nous nous excusons pour la gêne occasionnée et restons à votre disposition pour toute question.</p>
            
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