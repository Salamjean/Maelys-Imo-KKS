@extends('comptable.layouts.template')

@section('content')
<div class="container py-4">
    <!-- Formulaire de versement -->
    <div class="card border-0 shadow-lg overflow-hidden mb-4">
        <div class="card-header bg-gradient-primary py-3">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-white me-3">
                    <i class="mdi mdi-cash-100 text-primary fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 text-white fw-bold">Nouveau Versement</h5>
                    <p class="mb-0 text-white-50">Enregistrement des versements des agents</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
        <!-- Formulaire de versement -->
        <form action="{{ route('versement.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf

            <div class="row">
                <!-- Première colonne -->
                <div class="col-md-6">
                    <!-- Sélection de l'agent -->
                    <div class="mb-4">
                        <label for="agentSelect" class="form-label fw-semibold">
                            <i class="fas fa-user-tie me-2"></i>Selectionné un agent de recouvrement
                        </label><br>
                        <select name="agent_id" id="agentSelect" class="form-select form-select-lg shadow-sm col-md-12" required>
                            <option value="" disabled selected>Choisir un agent...</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" 
                                    data-total-percu="{{ $agent->total_percu }}"
                                    data-total-verse="{{ $agent->total_verse }}"
                                    data-reste-actuel="{{ $agent->reste_actuel }}">
                                    {{ $agent->name }} {{ $agent->prenom }} 
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Montant perçu (readonly) -->
                    <div class="mb-4">
                        <label for="totalPercu" class="form-label fw-semibold">
                            <i class="fas fa-money-bill-wave me-2"></i>Total perçu
                        </label>
                        <input type="text" id="totalPercu" placeholder="5000"  class="form-control form-control-lg bg-light" readonly>
                    </div>

                    <!-- Total déjà versé (readonly) -->
                    <div class="mb-4">
                        <label for="totalVerse" class="form-label fw-semibold">
                            <i class="fas fa-exchange-alt me-2"></i>Total déjà versé
                        </label>
                        <input type="text" id="totalVerse" placeholder="5000"  class="form-control form-control-lg bg-light" readonly>
                    </div>
                </div>

                <!-- Deuxième colonne -->
                <div class="col-md-6">
                    <!-- Reste actuel (readonly) -->
                    <div class="mb-4">
                        <label for="resteActuel" class="form-label fw-semibold">
                            <i class="fas fa-calculator me-2"></i>Reste à verser
                        </label>
                        <input type="text" id="resteActuel" placeholder="5000"  class="form-control form-control-lg bg-light" readonly>
                    </div>

                    <!-- Montant à verser (saisie) -->
                    <div class="mb-4">
                        <label for="montantVerse" class="form-label fw-semibold">
                            <i class="fas fa-coins me-2"></i>Montant à verser (FCFA)
                        </label>
                        <input type="number" name="montant" id="montantVerse" 
                            class="form-control form-control-lg" 
                            placeholder="5000" 
                            step="0.01" 
                            min="1000" 
                            required>
                    </div>

                    <!-- Nouveau reste (calculé en JS) -->
                    <div class="mb-4">
                        <label for="nouveauReste" class="form-label fw-semibold">
                            <i class="fas fa-calculator me-2"></i>Nouveau reste après versement
                        </label>
                        <input type="text" placeholder="5000"  id="nouveauReste" class="form-control form-control-lg bg-light" readonly>
                    </div>
                </div>
            </div>

            <!-- Bouton submit - sur toute la largeur -->
            <div class="row mt-10">
                <div class="col-12">
                    <button type="submit" class="btn btn-lg shadow" style="background-color: #02245b; color: white; width: 100%;">
                        <i class="mdi mdi-content-save"></i> Enregistrer le versement
                    </button>
                </div>
            </div>
        </form>
    </div>
    </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #02245b 0%, #1e3c72 100%);
    }
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        border-radius: 12px;
        overflow: hidden;
    }
    .form-control-lg, .form-select-lg {
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
    }
    .input-group-text {
        transition: all 0.3s;
    }
    .input-group:focus-within .input-group-text {
        background-color: #e9f5ff;
    }
    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .avatar-sm {
        width: 36px;
        height: 36px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(2, 36, 91, 0.03);
    }
</style>

<script>
// Enable Bootstrap validation
(function() {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const agentSelect = document.getElementById('agentSelect');
    const totalPercu = document.getElementById('totalPercu');
    const totalVerse = document.getElementById('totalVerse');
    const resteActuel = document.getElementById('resteActuel');
    const montantVerse = document.getElementById('montantVerse');
    const nouveauReste = document.getElementById('nouveauReste');

    // Formatage de l'argent
    const formatMoney = (amount) => {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount) + ' FCFA';
    };

    // Mise à jour des champs quand l'agent change
    agentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        totalPercu.value = formatMoney(selectedOption.dataset.totalPercu);
        totalVerse.value = formatMoney(selectedOption.dataset.totalVerse);
        resteActuel.value = formatMoney(selectedOption.dataset.resteActuel);
        
        // Met à jour le max du montant à verser
        montantVerse.max = selectedOption.dataset.resteActuel;
        montantVerse.value = '';
        nouveauReste.value = '';
    });

    // Calcul du nouveau reste quand le montant change
    montantVerse.addEventListener('input', function() {
        const selectedOption = agentSelect.options[agentSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        
        const reste = parseFloat(selectedOption.dataset.resteActuel);
        const verse = parseFloat(this.value) || 0;
        
        if (verse > reste) {
            this.setCustomValidity('Le montant ne peut pas dépasser le reste à verser');
        } else {
            this.setCustomValidity('');
            nouveauReste.value = formatMoney(reste - verse);
        }
    });
});
</script>
@endsection