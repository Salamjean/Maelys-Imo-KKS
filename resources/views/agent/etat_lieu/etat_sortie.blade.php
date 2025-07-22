


@extends('comptable.layouts.template')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header" style="background-color: #02245b;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 text-white"><i class="fas fa-clipboard-check me-2"></i>État des lieux - Entrée</h3>
                        <span class="badge" style="background-color: #ff5e14; color: white; font-size: 1rem;">{{ $bien->numero_bien ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informations générales -->
                    <div class="row mb-4 g-4">
                        <!-- Informations du bien -->
                        <div class="col-md-6">
                            <div class="card h-100" style="border-color: #02245b;">
                                <div class="card-header" style="background-color: rgba(2, 36, 91, 0.1);">
                                    <h4 class="mb-0" style="color: #02245b;"><i class="fas fa-home me-2"></i>État des lieux - Entrée des parties communes</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <p><strong>Sol : </strong> {{ $etatEntree->parties_communes['sol'] ?? 'uuu' }}</p>
                                            <p><strong>Murs : </strong> {{ $etatEntree->parties_communes['murs'] ?? 'uuu' }}</p>
                                            <p><strong>Plafond : </strong> {{ $etatEntree->parties_communes['plafond'] ?? 'uuu' }}</p>
                                        </div>
                                        <div class="col-4">
                                            <p><strong>Portes : </strong> {{ $etatEntree->parties_communes['porte_entre'] ?? 'uuu' }}</p>
                                            <p><strong>Electricité : </strong> {{ $etatEntree->parties_communes['interrupteur'] ?? 'uuu' }}</p>
                                            <p><strong>Robinetterie : </strong> {{ $etatEntree->parties_communes['robinet'] ?? 'uuu' }}</p>
                                        </div>
                                        <div class="col-4">
                                            <p><strong>Lavabo : </strong> {{ $etatEntree->parties_communes['lavabo'] ?? 'uuu' }}</p>
                                            <p><strong>Douche : </strong> {{ $etatEntree->parties_communes['douche'] ?? 'uuu' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations locataire -->
                        <div class="col-md-6">
                            <div class="card h-100" style="border-color: #02245b;">
                                <div class="card-header" style="background-color: rgba(2, 36, 91, 0.1);">
                                    <h4 class="mb-0" style="color: #02245b;"><i class="fas fa-user-tie me-2"></i>Informations locataire</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Nom:</strong> {{ $locataire->name }} {{ $locataire->prenom }}</p>
                                            <p><strong>Contact:</strong> {{ $locataire->contact }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Email:</strong> {{ $locataire->email }}</p>
                                            <p><strong>Profession:</strong> {{ $locataire->profession }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4" style="border-color: #02245b;">
                    
                    <!-- Formulaire d'état des lieux -->
                    <form method="POST" action="{{ route('etat.sortie.store') }}" id="etat-lieux-form">
                        @csrf
                        <input type="hidden" name="locataire_id" value="{{ $locataire->id }}">
                        <input type="hidden" name="bien_id" value="{{ $bien->id }}">
                        <input type="hidden" name="type_bien" value="{{ $bien->type }}">
                        <input type="hidden" name="commune_bien" value="{{ $bien->commune }}">
                        
                        <!-- Section parties communes -->
                        <div class="card mb-4" style="border-color: #02245b;">
                            <div class="card-header text-white" style="background-color: #02245b;">
                                <h4 class="mb-0"><i class="fas fa-home me-2"></i>Parties communes</h4>
                            </div>
                            <div class="card-body">
                                @include('agent.etat_lieu.parties_communes')
                            </div>
                        </div>
                        
                        <!-- Sections pour chaque chambre -->
                        @for($i = 0; $i < $bien->nombre_de_chambres; $i++)
                            <div class="card mb-4" style="border-color: #02245b;">
                                <div class="card-header text-white" style="background-color: #02245b;">
                                    <h4 class="mb-0"><i class="fas fa-bed me-2"></i>Chambre {{ $i + 1 }}</h4>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="chambres[{{ $i }}][nom]" value="Chambre {{ $i + 1 }}">
                                    @include('agent.etat_lieu.partials_chambre', ['index' => $i])
                                </div>
                            </div>
                        @endfor
                        
                        <!-- Section clés et signature -->
                        <div class="card mb-4" style="border-color: #02245b;">
                            <div class="card-header text-white" style="background-color: #02245b;">
                                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Clés et signature</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre_cle" class="form-label">Nombre de clés remises</label>
                                        <input type="number" class="form-control" id="nombre_cle" name="nombre_cle" 
                                               min="1" value="1" required>
                                    </div>
                                    <div class="col-md-6" style="display: none">
                                        <label for="presence_partie" class="form-label">Présence des parties</label>
                                        <select class="form-select" id="presence_partie" name="presence_partie" required>
                                            <option value="oui">Oui</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="accord_parties" required>
                                            <label class="form-check-label" for="accord_parties">
                                                Je certifie que ces informations correspondent à l'état réel des lieux
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Boutons de soumission -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('accounting.current') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-lg" style="background-color: #ff5e14; color: white;">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(2, 36, 91, 0.1);
    }
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .etat-item {
        padding: 1rem;
        background-color: rgba(2, 36, 91, 0.05);
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid #ff5e14;
    }
    .observation-disabled {
        background-color: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
    }
    .form-check-input:required ~ .form-check-label::after {
        content: " *";
        color: #ff5e14;
    }
    .btn-outline-primary {
        border-color: #02245b;
        color: #02245b;
    }
    .btn-outline-primary:hover {
        background-color: #02245b;
        color: white;
    }
    .is-invalid {
        border-color: #ff5e14 !important;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion des observations grisées
        function setupEtatSelect(select) {
            select.addEventListener('change', function() {
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
            });
            
            // Initialiser l'état au chargement
            if (select.value === 'bon') {
                select.dispatchEvent(new Event('change'));
            }
        }

        // Appliquer à tous les selects
        document.querySelectorAll('.etat-select').forEach(setupEtatSelect);

        // Validation du formulaire
        document.getElementById('etat-lieux-form').addEventListener('submit', function(e) {
            if (!document.getElementById('accord_parties').checked) {
                e.preventDefault();
                alert('Vous devez certifier que les informations sont exactes');
                return;
            }
            
            let isValid = true;
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value) {
                    isValid = false;
                    field.classList.add('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    });
</script>
@endsection