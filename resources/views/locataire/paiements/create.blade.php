@extends('locataire.layouts.template')

@section('content')
<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Paiement du loyer - {{ $mois_couvert_display }}</h4>
                <span class="badge bg-light text-dark fs-10 " style="font-size: 20px">Montant du loyer : <span style="font-weight: bold">{{ number_format($montant) }} FCFA</span> </span>
            </div>
        </div>
        
        <div class="card-body">
            <div class="alert alert-info border-start border-5 border-info p-4">
                <div class="d-flex flex-column flex-md-row align-items-center text-center text-md-start">
                    <div class="mb-3 mb-md-0 me-md-4">
                        
                    </div>
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

            <form id="paiementForm" method="POST" action="{{ route('locataire.paiements.store', $locataire) }}" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="mois_couvert" value="{{ $mois_couvert }}">
                
                <div class="mb-4 " style="text-align: center;">
                    <label class="form-label fw-bold"><i class="fas fa-credit-card me-2"></i>  Méthode de paiement</label><br>
                    <select class="form-select form-select-lg py-3 w-full" name="methode_paiement" id="methodeSelect" required>
                        <option value="Mobile Money" {{ old('methode_paiement') == 'Mobile Money' ? 'selected' : '' }}>
                            <i class="fas fa-mobile-alt me-2"></i> Mobile Money
                        </option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner une méthode de paiement</div>
                </div>

                <div class="mb-4 p-3 border rounded bg-light" id="codeVerifField" style="display:none;">
                    <label class="form-label fw-bold"><i class="fas fa-shield-alt me-2"></i>Code de vérification</label>
                    <input type="text" class="form-control form-control-lg py-3" name="verif_espece" 
                           placeholder="Saisissez le code fourni par l'agent" value="{{ old('verif_espece') }}">
                    <small class="text-muted d-block mt-2"><i class="fas fa-info-circle me-1"></i> Ce code vous sera remis par l'agent lors du paiement en espèces</small>
                </div>

                <button type="submit" class="btn btn-lg w-100 mt-3" 
                        style="background: linear-gradient(135deg, #ff5e14 0%, #ff8c00 100%); color: white; font-weight: 600; letter-spacing: 0.5px;">
                    <i class="fas fa-check-circle me-2"></i> Valider le paiement
                </button>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage dynamique
    const methodeSelect = document.getElementById('methodeSelect');
    const codeField = document.getElementById('codeVerifField');
    
    methodeSelect.addEventListener('change', function() {
        if (this.value === 'Espèces') {
            codeField.style.display = 'block';
            codeField.querySelector('input').focus();
        } else {
            codeField.style.display = 'none';
        }
    });
    
    // Initialiser l'affichage si retour avec erreur
    if (methodeSelect.value === 'Espèces') {
        codeField.style.display = 'block';
    }

    // Validation Bootstrap
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Gestion des notifications
    @if($errors->any())
        Swal.fire({
            title: '<span style="color:#02245b">Erreur</span>',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            icon: 'error',
            confirmButtonColor: '#02245b',
            background: '#fff',
            backdrop: `
                rgba(2,36,91,0.4)
                url("/images/nyan-cat.gif")
                left top
                no-repeat
            `
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: '<span style="color:#02245b">Erreur</span>',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonColor: '#02245b',
            timer: 5000,
            timerProgressBar: true
        });
    @endif

    @if(session('success'))
        Swal.fire({
            title: '<span style="color:#02245b">Succès</span>',
            html: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#ff5e14',
            showConfirmButton: false,
            timer: 3000,
            willClose: () => {
                window.location.href = "{{ route('locataire.paiements.index') }}";
            }
        });
    @endif
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
    .form-select, .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
    }
    .form-select:focus, .form-control:focus {
        border-color: #02245b;
        box-shadow: 0 0 0 0.25rem rgba(2, 36, 91, 0.25);
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 94, 20, 0.3);
        transition: all 0.3s ease;
    }
</style>
@endsection