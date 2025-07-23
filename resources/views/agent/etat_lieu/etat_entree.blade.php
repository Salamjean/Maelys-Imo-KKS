@extends('comptable.layouts.template')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0">
                <!-- En-tête avec numéro de bien -->
                <div class="card-header bg-primary-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0 text-white">
                            <i class="fas fa-clipboard-check fa-fw me-2"></i>État des lieux - Entrée
                        </h2>
                        <span class="badge bg-accent rounded-pill fs-6 px-3 py-2">
                            {{ $bien->numero_bien ?? 'N/A' }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body px-4 py-4">
                    <!-- Section Informations -->
                    <div class="row g-4 mb-5">
                        <!-- Carte Bien -->
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary-light">
                                    <h3 class="h5 mb-0 text-primary-dark">
                                        <i class="fas fa-home fa-fw me-2"></i>Informations du bien
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Type:</strong> {{ $bien->type }}</p>
                                            <p class="mb-2"><strong>Utilisation:</strong> {{ $bien->utilisation }}</p>
                                            <p class="mb-2"><strong>Ville:</strong> {{ $bien->commune }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Surface:</strong> {{ $bien->superficie }} m²</p>
                                            <p class="mb-2"><strong>Pièces:</strong> {{ $bien->nombre_de_chambres + $bien->nombre_de_toilettes }}</p>
                                            <p class="mb-2"><strong>Chambres:</strong> {{ $bien->nombre_de_chambres }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Carte Locataire -->
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary-light">
                                    <h3 class="h5 mb-0 text-primary-dark">
                                        <i class="fas fa-user-tie fa-fw me-2"></i>Informations locataire
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Nom:</strong> {{ $locataire->name }} {{ $locataire->prenom }}</p>
                                            <p class="mb-2"><strong>Contact:</strong> {{ $locataire->contact }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Email:</strong> {{ $locataire->email }}</p>
                                            <p class="mb-2"><strong>Profession:</strong> {{ $locataire->profession }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Séparateur -->
                    <div class="hr-divider my-4">
                        <span class="hr-divider-line bg-primary-light"></span>
                    </div>
                    
                    <!-- Formulaire principal -->
                    <form method="POST" action="{{ route('etat.entree.store') }}" id="etat-lieux-form" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="locataire_id" value="{{ $locataire->id }}">
                        <input type="hidden" name="bien_id" value="{{ $bien->id }}">
                        <input type="hidden" name="type_bien" value="{{ $bien->type }}">
                        <input type="hidden" name="commune_bien" value="{{ $bien->commune }}">
                        
                        <!-- Section Parties Communes -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header text-white bg-primary-dark">
                                <h3 class="h5 mb-0">
                                    <i class="fas fa-home fa-fw me-2"></i>Parties communes
                                </h3>
                            </div>
                            <div class="card-body px-4 py-3">
                                @include('agent.etat_lieu.parties_communes')
                            </div>
                        </div>
                        
                        <!-- Sections Chambres -->
                        @for($i = 0; $i < $bien->nombre_de_chambres; $i++)
                            <div class="card mb-4 border-primary">
                                <div class="card-header text-white bg-primary-dark">
                                    <h3 class="h5 mb-0">
                                        <i class="fas fa-bed fa-fw me-2"></i>Chambre {{ $i + 1 }}
                                    </h3>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <input type="hidden" name="chambres[{{ $i }}][nom]" value="Chambre {{ $i + 1 }}">
                                    @include('agent.etat_lieu.partials_chambre', ['index' => $i])
                                </div>
                            </div>
                        @endfor
                        
                        <!-- Section Clés et Signature -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header text-white bg-primary-dark">
                                <h3 class="h5 mb-0">
                                    <i class="fas fa-key fa-fw me-2"></i>Clés et signature
                                </h3>
                            </div>
                            <div class="card-body px-4 py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre_cle" class="form-label fw-semibold">Nombre de clés remises</label>
                                        <input type="number" class="form-control" id="nombre_cle" name="nombre_cle" 
                                               min="1" value="1" required>
                                        <div class="invalid-feedback">
                                            Veuillez préciser le nombre de clés.
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-none">
                                        <label for="presence_partie" class="form-label fw-semibold">Présence des parties</label>
                                        <select class="form-select" id="presence_partie" name="presence_partie" required>
                                            <option value="oui">Oui</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="accord_parties" required>
                                            <label class="form-check-label fw-semibold" for="accord_parties">
                                                Je certifie que ces informations correspondent à l'état réel des lieux
                                                <span class="text-danger">*</span>
                                            </label>
                                            <div class="invalid-feedback">
                                                Vous devez accepter cette condition.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between mt-5">
                            <a href="{{ route('accounting.current') }}" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left fa-fw me-2"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-accent btn-lg px-4">
                                <i class="fas fa-save fa-fw me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-dark: #02245b;
        --primary-light: rgba(2, 36, 91, 0.1);
        --accent: #ff5e14;
    }
    
    .bg-primary-dark {
        background-color: var(--primary-dark) !important;
    }
    
    .bg-primary-light {
        background-color: var(--primary-light) !important;
    }
    
    .bg-accent {
        background-color: var(--accent) !important;
    }
    
    .text-primary-dark {
        color: var(--primary-dark) !important;
    }
    
    .border-primary {
        border-color: var(--primary-dark) !important;
    }
    
    .btn-accent {
        background-color: var(--accent);
        color: white;
        border: none;
        transition: all 0.3s;
    }
    
    .btn-accent:hover {
        background-color: #e04a00;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .card {
        border-radius: 0.5rem;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .hr-divider {
        position: relative;
        text-align: center;
    }
    
    .hr-divider-line {
        height: 2px;
        width: 100%;
        display: block;
    }
    
    .etat-item {
        padding: 1.25rem;
        background-color: var(--primary-light);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--accent);
        transition: all 0.3s;
    }
    
    .etat-item:hover {
        background-color: rgba(2, 36, 91, 0.15);
    }
    
    .observation-disabled {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
        border-color: #dee2e6;
    }
    
    .form-control, .form-select {
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 0.25rem rgba(2, 36, 91, 0.25);
    }
    
    .invalid-feedback {
        font-size: 0.875rem;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.5px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des observations
    function setupEtatSelect(select) {
        const handleChange = function() {
            const observationField = this.closest('.etat-item').querySelector('.observation');
            if (this.value === 'bon') {
                observationField.value = 'Bon état - Aucune observation';
                observationField.classList.add('observation-disabled');
                observationField.readOnly = true;
            } else {
                observationField.value = '';
                observationField.classList.remove('observation-disabled');
                observationField.readOnly = false;
            }
        };
        
        select.addEventListener('change', handleChange);
        
        // Initialisation
        if (select.value === 'bon') {
            handleChange.call(select);
        }
    }

    // Appliquer à tous les selects
    document.querySelectorAll('.etat-select').forEach(setupEtatSelect);

    // Validation Bootstrap
    (function() {
        'use strict';
        
        const form = document.getElementById('etat-lieux-form');
        
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Scroll vers le premier champ invalide
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
            
            form.classList.add('was-validated');
        }, false);
        
        // Validation en temps réel
        form.querySelectorAll('[required]').forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                }
            });
        });
    })();
});
</script>
@endsection