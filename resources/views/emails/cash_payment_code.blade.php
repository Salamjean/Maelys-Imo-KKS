@component('mail::message')
# Code de vérification pour paiement en espèces

Bonjour {{ $locataire->prenom }} {{ $locataire->name }},

**Montant à payer :** {{ number_format($montant, 0, ',', ' ') }} FCFA  
**Code de vérification :** <strong>{{ $code }}</strong>  
**Valable jusqu'au :** {{ $expiration }}

Ce code est nécessaire pour que l'agence puisse valider votre paiement en espèces. Ne le partagez avec personne d'autre que l'agent immobilier.

@component('mail::button', ['url' => route('locataire.dashboard')])
Accéder à votre espace
@endcomponent

En cas de problème, contactez-nous immédiatement.

Cordialement,  
L'équipe de {{ config('app.name') }}
@endcomponent