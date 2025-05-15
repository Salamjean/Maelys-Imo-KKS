<p>Bonjour {{ $locataire->name }},</p>

<p>Nous vous rappelons que votre paiement de loyer pour le bien situé à {{ $locataire->bien->commune }} est dû.</p>

@if($tauxMajoration > 0)
<p>En raison d'un retard de paiement, une majoration de {{ $tauxMajoration }}% a été appliquée :</p>
<ul>
    <li>Montant initial du loyer: {{ number_format($montantOriginal, 0, ',', ' ') }} FCFA</li>
    <li>Majoration: {{ $tauxMajoration }}%</li>
    <li><strong>Nouveau montant à payer: {{ number_format($nouveauMontant, 0, ',', ' ') }} FCFA</strong></li>
</ul>
@else
<p>Montant du loyer à payer: {{ number_format($montantOriginal, 0, ',', ' ') }} FCFA</p>
@endif

<p>Merci de régulariser votre situation au plus vite.</p>

<p>Cordialement,<br>
Votre agence immobilière</p>