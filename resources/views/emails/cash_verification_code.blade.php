@component('mail::message')
# Code de vérification de paiement

Bonjour {{ $locataire->user->name }},

Vous avez effectué un paiement en espèces. Voici votre code de vérification :

@component('mail::panel')
**Code :** {{ $code }}  
**Valide jusqu'au :** {{ $expiration }}
@endcomponent

Ce code est à remettre à votre gestionnaire pour valider votre paiement.

@component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
Accéder à mon espace
@endcomponent

Cordialement,  
L'équipe {{ config('app.name') }}
@endcomponent