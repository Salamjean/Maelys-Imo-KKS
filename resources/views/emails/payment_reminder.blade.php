<!DOCTYPE html>
<html>
<head>
    <title>Rappel de paiement</title>
    <style type="text/css">
        /* Styles email-safe */
        body { font-family: Arial, sans-serif; line-height: 1.5; color: #333; }
        .header { background-color: #2c3e50; color: white; padding: 20px; }
        .content { padding: 20px; }
        .footer { padding: 10px; font-size: 12px; color: #777; }
        .card { border: 1px solid #e0e0e0; padding: 15px; margin: 15px 0; }
        .warning { background-color: #fff3cd; padding: 15px; border-left: 4px solid #f39c12; }
        .highlight { color: #e74c3c; font-weight: bold; }
        .label { font-weight: bold; display: inline-block; width: 120px; }
    </style>
</head>
<body style="margin:0; padding:0;">
    <!-- Container principal -->
    <div style="max-width:600px; margin:0 auto;">
        
        <!-- En-tête -->
        <div class="header" style="text-align:center;">
            <h2 style="margin:0;">Rappel de paiement</h2>
        </div>
        
        <!-- Contenu -->
        <div class="content">
            <p>Bonjour {{ $locataire->prenom }} {{ $locataire->nom }},</p>
            <p>
                Nous vous rappelons que le règlement du loyer du mois en cours est attendu au plus tard le {{$locataire->bien->date_fixe}} du mois.
                Conformément aux dispositions de votre contrat de location, une pénalité sera appliquée en cas de retard.
            </p>
            <div class="card">
                <p><span class="label">Adresse :</span> {{ $locataire->bien->commune }}</p>
                <p><span class="label">Montant :</span> 
                    <span class="highlight">{{ number_format($nouveauMontant, 0, ',', ' ') }} FCFA</span>
                </p>
                
                @if($tauxMajoration > 0)
                <div class="warning">
                    <p><span class="label">Majoration :</span> {{ $tauxMajoration }}%</p>
                    <p><span class="label">Montant initial :</span> {{ number_format($montantOriginal, 0, ',', ' ') }} FCFA</p>
                </div>
                @endif
            </div>
            
            <p><strong>Modes de paiement :</strong></p>
            <ul style="margin-top:0;">
                <li>Paiement en espèces</li>
                <li>Mobile Money</li>
            </ul>
        </div>
        
        <!-- Pied de page -->
        <div class="footer">
            <p>Service gestion locative<br>
            © {{ date('Y') }} Maelys-Imo</p>
        </div>
    </div>
</body>
</html>