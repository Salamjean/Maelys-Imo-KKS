@extends('locataire.layouts.template')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header text-white" style="background-color: #02245b">
            <h4>Paiement en ligne - {{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</h4>
        </div>
        
        <div class="card-body text-center">
            <div class="alert alert-primary">
                <h5>Montant : {{ number_format($paiement->montant) }} FCFA</h5>
                <p>Vous allez être redirigé vers la plateforme de paiement sécurisée CinetPay</p>
            </div>
            
            <div id="cinetpay-container" class="my-4"></div>
            
            <div class="alert alert-info">
                <a href="{{ route('locataire.paiements.create', $paiement->locataire_id) }}" 
                   class="btn btn-secondary">
                    Annuler et retour
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.cinetpay.com/seamless/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration CinetPay
    CinetPay.setConfig({
        apikey: '{{ $apiKey }}',
        site_id: {{ $siteId }},
        notify_url: '{{ $notify_url }}'
    });

    // Initialisation du paiement
    CinetPay.getCheckout({
        transaction_id: '{{ $transactionId }}',
        amount: {{ $paiement->montant }},
        currency: 'XOF',
        channels: 'ALL',
        description: 'Paiement loyer {{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat("F Y") }}',
        customer_name: '{{ $paiement->locataire->nom }} {{ $paiement->locataire->prenom }}',
        customer_phone_number: '{{ $paiement->locataire->telephone }}',
        return_url: '{{ $return_url }}',
        cancel_url: '{{ $cancel_url }}'
    });

    // Gestion des erreurs
    CinetPay.onError(function(data) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur de paiement',
            text: 'Une erreur est survenue lors du processus de paiement. Veuillez réessayer.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = '{{ $cancel_url }}';
        });
    });
});
</script>
@endsection