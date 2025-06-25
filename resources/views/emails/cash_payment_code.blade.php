@component('mail::message')
<style>
    .payment-card {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        background-color: #f9f9f9;
    }
    .code-display {
        font-size: 24px;
        letter-spacing: 3px;
        color: #2c3e50;
        font-weight: bold;
        text-align: center;
        margin: 15px 0;
        padding: 10px;
        background-color: #f0f7fd;
        border-left: 4px solid #3498db;
    }
    .warning-box {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .amount-highlight {
        color: blue;
        font-weight: bold;
        font-size: 18px;
    }
</style>

# 🏦 Confirmation de votre code de paiement

Bonjour {{ $locataire->prenom }} {{ $locataire->nom }},

Votre demande de paiement en espèces a bien été enregistrée. Voici les détails de votre transaction :

<div class="payment-card">
    <p><strong>Montant à régler :</strong> <span class="amount-highlight">{{ number_format($montant, 0, ',', ' ') }} FCFA</span></p>
    <p><strong>Date limite de validité :</strong> {{ $expiration }}</p>
    <strong>Votre code de paiement</strong> : <span style="color:#27ae60"><strong>{{ $code }}</strong></span>
</div>

<div class="warning-box">
    <strong>⚠️ Sécurité importante :</strong>
    <ul style="margin-bottom:0;">
        <li>Ce code est strictement personnel</li>
        <li>Ne le communiquez à personne par téléphone ou email</li>
        <li>Seul un agent habilité doit vous le demander</li>
        <li>Validez l'identité de l'agent avant paiement</li>
    </ul>
</div>
<p>Pour finaliser votre paiement, présentez ce code à l'agent de l'agence immobilière.</p>

Pour toute question ou en cas d'urgence :  
📞 <a href="tel:+221338699999">+221 33 86 99 999</a> (Service client)  
✉️ <a href="mailto:paiement@agence.sn">paiement@agence.sn</a>

Cordialement,  

<small style="color:#777;">
    Ce message est confidentiel. En cas de réception par erreur, merci de le détruire et de nous avertir immédiatement.
</small>
@endcomponent