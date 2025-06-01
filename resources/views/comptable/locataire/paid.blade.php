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
            <form action="{{ route('versement.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf

                <div class="mb-4">
                    <label for="agentSelect" class="form-label fw-semibold">
                        <i class="fas fa-user-tie me-2"></i>Agent de recouvrement
                    </label>
                    <select name="agent_id" id="agentSelect" class="form-select form-select-lg shadow-sm" required>
                        <option value="" disabled selected>Choisir un agent...</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">
                                {{ $agent->prenom }} {{ $agent->name }} 
                                <small class="text-muted">({{ $agent->email }})</small>
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un agent</div>
                </div>

                <div class="mb-4">
                    <label for="amountInput" class="form-label fw-semibold">
                        <i class="fas fa-coins me-2"></i>Montant (FCFA)
                    </label>
                    <div class="input-group input-group-lg shadow-sm">
                        <span class="input-group-text bg-light">
                            <i class="mdi mdi-cash-multiple text-primary"></i>
                        </span>
                        <input type="number" name="montant" id="amountInput" 
                               class="form-control form-control-lg" 
                               placeholder="5000.00" 
                               step="0.01" 
                               min="1000" 
                               required>
                        <span class="input-group-text">FCFA</span>
                        <div class="invalid-feedback">Le montant doit être d'au moins 1000 FCFA</div>
                    </div>
                </div>

                <div class="d-grid pt-2">
                    <button type="submit" class="btn btn-lg shadow" style="background-color: #02245b; color: white; border-radius: 8px; width: 100%;">
                        <i class="mdi mdi-content-save"></i>Enregistrer le versement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des versements -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-header bg-gradient-primary py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-white me-3">
                        <i class="mdi mdi-book-multiple-variant"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white fw-bold">Historique des Versements</h5>
                        <p class="mb-0 text-white-50">Derniers versements enregistrés</p>
                    </div>
                </div>
                <span class="badge bg-white text-primary fs-6">{{ $versements->count() }} enregistrements</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">Agent</th>
                            <th class="py-3 px-4 text-end">Montant</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements as $versement)
                        <tr>
                            <td class="py-3 px-4 align-middle">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="mdi mdi-camera-front-variant"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $versement->agent->prenom }} {{ $versement->agent->name }}</h6>
                                        <small class="text-muted">{{ $versement->agent->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 align-middle text-end fw-bold text-success">
                                {{ number_format($versement->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3 px-4 align-middle">
                                {{ $versement->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 align-middle text-center">
                                <button class="btn btn-sm btn-outline-danger rounded-circle" title="Supprimer">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>Aucun versement enregistré
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($versements->hasPages())
        <div class="card-footer bg-light">
            {{ $versements->links() }}
        </div>
        @endif
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
@endsection