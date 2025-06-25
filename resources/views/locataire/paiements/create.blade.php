@extends('locataire.layouts.template')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Paiement du loyer - {{ $mois_couvert_display }}</h4>
                <span class="badge bg-light text-dark fs-10" style="font-size: 20px">Montant : <span style="font-weight: bold">{{ number_format($montant) }} FCFA</span></span>
            </div>
        </div>
        
        <div class="card-body">
            <div class="alert alert-info border-start border-5 border-info p-4">
                <div class="d-flex flex-column flex-md-row align-items-center text-center text-md-start">
                    <div class="w-100">
                        <h5 class="alert-heading fw-bold mb-3"><i class="fas fa-info-circle fa-1x text-info me-2"></i>Informations de paiement</h5>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                                    <div>
                                        <strong class="d-block">Période</strong>
                                        <span class="text-black">{{ $mois_couvert_display }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-5">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-end">
                                    <i class="fas fa-wallet me-2 text-info"></i>
                                    <div>
                                        <strong class="d-block">Montant</strong>
                                        <span class="text-black">{{ number_format($montant) }} FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button id="payButton" class="btn btn-lg w-100 mt-3" 
                    style="background: linear-gradient(135deg, #ff5e14 0%, #ff8c00 100%); color: white; font-weight: 600; letter-spacing: 0.5px;">
                <i class="fas fa-check-circle me-2"></i> Payer avec Mobile Money
            </button>

            <!-- Formulaire caché pour soumettre après paiement réussi -->
            <form id="paymentForm" method="POST" action="{{ route('locataire.paiements.store', $locataire) }}" style="display: none;">
                @csrf
                <input type="hidden" name="mois_couvert" value="{{ $mois_couvert }}">
                <input type="hidden" name="transaction_id" id="transaction_id">
            </form>
        </div>
    </div>
</div>

<!-- Inclure les scripts nécessaires -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.cinetpay.com/seamless/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const payButton = document.getElementById('payButton');
    
    payButton.addEventListener('click', function() {
        // Afficher un loader pendant l'initialisation
        Swal.fire({
            title: 'Initialisation du paiement',
            html: 'Veuillez patienter...',
            allowOutsideClick: true,
            showConfirmButton: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Configuration de CinetPay
        CinetPay.setConfig({
            apikey: '{{ config("services.cinetpay.api_key") }}',
            site_id: '{{ config("services.cinetpay.site_id") }}',
            notify_url: '{{ route("cinetpay.notify") }}',
            mode: 'PRODUCTION'
        });

        // Générer un ID de transaction unique
        const transactionId = 'PAY_' + Date.now();
        document.getElementById('transaction_id').value = transactionId;

        // Ouvrir le popup CinetPay
        CinetPay.getCheckout({
            transaction_id: transactionId,
            amount: {{ $montant }},
            currency: 'XOF',
            description: 'Paiement loyer ' + '{{ $mois_couvert_display }}',
            // Autres options personnalisables
            customer_name: '{{ $locataire->nom }}',
            customer_surname: '{{ $locataire->prenom }}',
            customer_phone_number: '{{ $locataire->telephone }}'
        });

        // Gérer la réponse du paiement
        CinetPay.waitResponse(function(data) {
            Swal.close();
            
            if (data.status === "ACCEPTED") {
                Swal.fire({
                    icon: 'success',
                    title: 'Paiement réussi!',
                    text: 'Votre paiement a été accepté.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // Soumettre le formulaire pour enregistrer en base
                    document.getElementById('paymentForm').submit();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Paiement échoué',
                    text: 'Le paiement n\'a pas pu être effectué. Veuillez réessayer.',
                    confirmButtonColor: '#02245b'
                });
            }
        });

        // Gérer les erreurs
        CinetPay.onError(function(error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors du paiement: ' + error.message,
                confirmButtonColor: '#02245b'
            });
        });
    });
});
</script>

<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
        border: none;
    }
    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 94, 20, 0.3);
        transition: all 0.3s ease;
    }
</style>
@endsection