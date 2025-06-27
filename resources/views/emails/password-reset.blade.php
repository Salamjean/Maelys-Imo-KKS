<!DOCTYPE html>
<html>
<head>
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #02245b;
            padding: 10px;
            text-align: center;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 25px;
            background-color: #ffffff;
        }
        .footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #999999;
            background-color: #f9f9f9;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #02245b;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;

        }
        .button:hover {
            background-color: #02245b;
            color: white;
        }
        .expiry-note {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- En-tête avec logo -->
        <div class="header">
            <img src="{{ asset('assets/images/mae-imo.png') }}" alt="Logo de votre entreprise" class="logo">
            <!-- Alternative pour les clients email qui bloquent les images -->
            <div style="display: none; max-height: 0px; overflow: hidden;">
                Votre Entreprise
            </div>

            
        </div>
        
        <div class="content">
            <h2 style="color: #02245b; margin-top: 0; color:white">Réinitialisation de votre mot de passe</h2>
            
            <p>Bonjour,</p>
            
            <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte. Pour procéder, veuillez cliquer sur le bouton ci-dessous :</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetLink }}" style="color: white" class="button">Réinitialiser mon mot de passe</a>
            </div>
            
            <p class="expiry-note">⚠️ Ce lien expirera dans 1 heure.</p>
            
            <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email ou nous contacter si vous pensez qu'il s'agit d'une erreur.</p>
            
            <p>Cordialement,<br>L'équipe de support</p>
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