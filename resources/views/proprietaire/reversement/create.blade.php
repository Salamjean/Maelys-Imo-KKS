@extends('proprietaire.layouts.template')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white" style="background-color: #02245b;">
                    <h4 class="mb-0">Nouveau Reversement</h4>
                </div>
                
                <div class="card-body">
                    <!-- Carte du solde disponible -->
                    <div class="card mb-4 border-left-0 border-right-0 border-top-0 border-bottom-3 border-primary rounded-0">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Solde disponible</h6>
                                    <h3 class="font-weight-bold mb-0" style="color: #02245b">
                                        <span id="solde-disponible">{{ number_format($soldeDisponible ) }}</span> FCFA
                                    </h3>
                                </div>
                                <div class="bg-primary-light rounded-circle p-3">
                                    <i class="mdi mdi-cash-multiple" style="color: #02245b"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('reversement.store') }}" method="POST" id="reversement-form" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="banque" class="font-weight-bold">Banque</label>
                                <select class="form-control form-control-lg rounded-pill" id="banque" name="banque" required>
                                    <option value="">Sélectionnez une banque</option>
                                    @foreach($ribs as $rib)
                                        <option value="{{ $rib->id }}" data-rib="{{ $rib->rib }}">{{ $rib->banque }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner une banque</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="rib" class="font-weight-bold">RIB</label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-lg rounded-pill" id="rib" name="rib" readonly required>
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-white rounded-pill"><i class="mdi mdi-cards "style="color: #02245b"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="montant" class="font-weight-bold">Montant (FCFA)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text rounded-left-pill">FCFA</span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control rounded-right-pill" id="montant" name="montant" 
                                           required min="0.01" max="{{ $soldeDisponible }}" value="{{ old('montant') }}" placeholder="5000...">
                                    @error('montant')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_reversement" class="font-weight-bold">Date de reversement</label>
                                <input type="date" class="form-control form-control-lg rounded-pill" id="date_reversement" name="date_reversement" required>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-lg rounded-pill px-5 py-3 text-white shadow-sm" style="background-color: #02245b">
                                <i class="mdi mdi-check-circle mr-2"></i> Enregistrer le reversement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Section des 3 derniers reversements -->
            <div class="col-12 shadow-sm border-0 mt-4">
                <div class="card-header text-white" style="background-color: #02245b;">
                    <h5 class="mb-0">Derniers Reversements</h5>
                </div>
                <div class="card-body">
                    @if($lastReversements->count() > 0)
                        <div class="list-group">
                            @foreach($lastReversements as $reversement)
                            <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 border-0 shadow-sm rounded-lg">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 font-weight-bold" style="color: #02245b">{{ $reversement->rib->banque ?? "Banque non renseignée"  }}</h6>
                                    <small class="text-muted">{{ $reversement->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div>
                                        <span class="badge badge-primary">{{ $reversement->reference }}</span>
                                        <small class="text-muted ml-2">{{ $reversement->rib->rib ??  "Rib non renseignée" }}</small>
                                    </div>
                                    <h5 class="mb-0 font-weight-bold" style="color: #02245b">{{ number_format($reversement->montant, 0, ',', ' ') }} FCFA</h5>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('reversement.index') }}" class="btn btn-sm text-white" style="background-color: #02245b">
                                Voir tout l'historique <i class="mdi mdi-chevron-right"></i>
                            </a>
                        </div>
                    @else
                        <div class="alert alert-info mb-0 text-center">
                            Aucun reversement effectué pour le moment.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles additionnels -->
<style>
    .bg-primary-light {
        background-color: rgba(2, 36, 91, 0.1);
    }
    .rounded-left-pill {
        border-top-left-radius: 50rem !important;
        border-bottom-left-radius: 50rem !important;
    }
    .rounded-right-pill {
        border-top-right-radius: 50rem !important;
        border-bottom-right-radius: 50rem !important;
    }
    .card {
        transition: all 0.3s ease;
        border-radius: 10px;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .form-control-lg {
        height: calc(2.5em + 1rem + 2px);
    }
    .list-group-item {
        transition: all 0.3s ease;
    }
    .list-group-item:hover {
        transform: translateX(5px);
    }
    .badge {
        background-color: #02245b;
        font-size: 0.8em;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Afficher le RIB quand une banque est sélectionnée
document.getElementById('banque').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('rib').value = selectedOption.getAttribute('data-rib');
    updateMaxMontant();
});

// Mettre à jour le max du champ montant
function updateMaxMontant() {
    fetch("{{ route('reversement.solde') }}")
        .then(response => response.json())
        .then(data => {
            const soldeElement = document.getElementById('solde-disponible');
            const montantInput = document.getElementById('montant');
            
            montantInput.max = data.solde;
            soldeElement.textContent = new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2}).format(data.solde);
            
            // Animation du changement de solde
            soldeElement.classList.add('text-success');
            setTimeout(() => soldeElement.classList.remove('text-success'), 1000);
        });
}

// Actualisation périodique du solde
setInterval(updateMaxMontant, 30000);

// Validation Bootstrap
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Afficher le RIB quand une banque est sélectionnée
document.getElementById('banque').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('rib').value = selectedOption.getAttribute('data-rib');
    updateMaxMontant();
});

// Mettre à jour le max du champ montant
function updateMaxMontant() {
    fetch("{{ route('reversement.solde') }}")
        .then(response => response.json())
        .then(data => {
            const soldeElement = document.getElementById('solde-disponible');
            const montantInput = document.getElementById('montant');
            
            montantInput.max = data.solde;
            soldeElement.textContent = new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2}).format(data.solde);
            
            // Animation du changement de solde
            soldeElement.classList.add('text-success');
            setTimeout(() => soldeElement.classList.remove('text-success'), 1000);
        });
}

// Actualisation périodique du solde
setInterval(updateMaxMontant, 30000);

// Gestion des alertes SweetAlert
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Opération réussie',
        html: `{!! str_replace("'", "\\'", session('success')) !!}<br><br>
               <strong>Nouveau solde disponible:</strong> {{ number_format(session('solde'), 2) }} FCFA`,
        confirmButtonText: 'Fermer',
        confirmButtonColor: '#3b82f6',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
});
@endif

// Gestion du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reversement-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation Bootstrap
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            Swal.fire({
                title: 'Confirmer le reversement',
                text: "Voulez-vous vraiment effectuer ce reversement?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
});
</script>
@endsection
