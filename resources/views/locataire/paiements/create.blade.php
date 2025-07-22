@extends('locataire.layouts.template')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Paiement du loyer - {{ $mois_couvert_display }}</h4>
                <span class="badge bg-light text-dark fs-10" style="font-size: 20px">Montant : <span style="font-weight: bold">{{ number_format($montant, 0, ',', ' ') }} FCFA</span></span>
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
                                        <span class="text-black">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4" style="display: flex; justify-content:center">
                <label for="paymentMethod" class="form-label fw-bold text-center">Méthode de paiement</label>
                <select class="form-select form-select-lg" id="paymentMethod" name="payment_method">
                    <option value="">-- Sélectionnez une méthode --</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="virement">Virement Bancaire</option>
                </select>
            </div>

            <div id="mobileMoneySection" style="display: none;">
                <button id="payButton" class="btn btn-lg w-100 mt-3" 
                        style="background: linear-gradient(135deg, #ff5e14 0%, #ff8c00 100%); color: white; font-weight: 600; letter-spacing: 0.5px;">
                    <i class="fas fa-check-circle me-2"></i> Payer avec Mobile Money
                </button>
            </div>

            <div id="bankTransferSection" style="display: none;">
                <form id="bankTransferForm" method="POST" action="{{ route('locataire.paiements.store', $locataire) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="mois_couvert" value="{{ $mois_couvert }}">
                    <input type="hidden" name="methode_paiement" value="virement">
                    <input type="hidden" name="transaction_id" value="VIR_{{ uniqid() }}">
                    
                    <div class="mb-3">
                        <label for="proofFile" class="form-label fw-bold">Preuve de virement (PDF ou image)</label>
                        <input class="form-control form-control-lg" type="file" id="proofFile" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Taille maximale : 2MB (formats acceptés: PDF, JPG, JPEG, PNG)</div>
                    </div>
                    
                    <button type="submit" class="btn btn-lg w-100 mt-3" 
                            style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%); color: white; font-weight: 600; letter-spacing: 0.5px;">
                        <i class="fas fa-paper-plane me-2"></i> Envoyer la preuve de paiement
                    </button>
                </form>
            </div>

            <!-- Formulaire caché pour Mobile Money -->
            <form id="paymentForm" method="POST" action="{{ route('locataire.paiements.store', $locataire) }}" style="display: none;">
                @csrf
                <input type="hidden" name="mois_couvert" value="{{ $mois_couvert }}">
                <input type="hidden" name="methode_paiement" value="mobile_money">
                <input type="hidden" name="statut" value="payé">
                <input type="hidden" name="transaction_id" id="transaction_id">
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.cinetpay.com/seamless/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('paymentMethod');
    const mobileMoneySection = document.getElementById('mobileMoneySection');
    const bankTransferSection = document.getElementById('bankTransferSection');
    
    // Gérer le changement de méthode de paiement
    paymentMethodSelect.addEventListener('change', function() {
        const method = this.value;
        
        mobileMoneySection.style.display = 'none';
        bankTransferSection.style.display = 'none';
        
        if (method === 'mobile_money') {
            mobileMoneySection.style.display = 'block';
        } else if (method === 'virement') {
            bankTransferSection.style.display = 'block';
        }
    });
    
    // Gestion du paiement Mobile Money
    const payButton = document.getElementById('payButton');
    
    payButton.addEventListener('click', function() {
        Swal.fire({
            title: 'Initialisation du paiement',
            html: 'Veuillez patienter...',
            allowOutsideClick: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        CinetPay.setConfig({
            apikey: '{{ config("services.cinetpay.api_key") }}',
            site_id: '{{ config("services.cinetpay.site_id") }}',
            notify_url: '{{ route("cinetpay.notify") }}',
            mode: 'PRODUCTION'
        });

        const transactionId = 'PAY_' + Date.now();
        document.getElementById('transaction_id').value = transactionId;

        CinetPay.getCheckout({
            transaction_id: transactionId,
            amount: {{ $montant }},
            currency: 'XOF',
            channels: 'ALL',
            description: 'Paiement loyer ' + '{{ $mois_couvert_display }}',
            customer_name: '{{ $locataire->nom }}',
            customer_surname: '{{ $locataire->prenom }}',
            customer_phone_number: '{{ $locataire->telephone }}'
        });

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
        
        CinetPay.onError(function(error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors du paiement. La page va se recharger...',
                showConfirmButton: false,
                timer: 3000
            }).then(() => {
                window.location.reload();
            });
        });
    });
    
    // Gestion du formulaire de virement bancaire
    const bankTransferForm = document.getElementById('bankTransferForm');
    
    bankTransferForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Confirmation',
            text: 'Êtes-vous sûr de vouloir envoyer cette preuve de virement ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#02245b',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, envoyer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Envoi en cours',
                    html: 'Traitement de votre preuve de virement...',
                    allowOutsideClick: true,
                    didOpen: () => {
                        Swal.showLoading();
                        setTimeout(() => {
                            bankTransferForm.submit();
                        }, 500);
                    }
                });
            }
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
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    #paymentMethod {
        padding: 0.75rem 1rem;
        font-size: 1.1rem;
    }
</style>
@endsection