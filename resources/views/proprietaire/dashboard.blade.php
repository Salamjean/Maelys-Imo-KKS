@extends('proprietaire.layouts.template')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="container-fluid px-4">

    <!-- Titre de la page -->
    <div class="d-flex align-items-center justify-content-center mb-5">
        <h1 class="text-gradient text-primary mb-0 mt-4" style="text-align: center">Tableau de Bord</h1>
    </div>

    <!-- Row des statistiques -->
    <div class="row g-4">

        <!-- Carte Total des Biens -->
        <div class="col-xl-4 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Total des Biens</h6>
                            <h2 class="font-weight-bold mb-0">{{ $totalBiens }}</h2>
                        </div>
                        <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Biens Disponibles -->
        <div class="col-xl-4 col-md-3 mb-4">
                <div class="card card-stats">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-muted mb-0">Biens Disponibles</h6>
                                <h2 class="font-weight-bold mb-0">{{ $biensDisponibles }}</h2>
                            </div>
                            <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <!-- Carte Biens Occupés -->
        <div class="col-xl-4 col-md-4">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Biens Loués</h6>
                            <h2 class="font-weight-bold mb-0">{{ $biensOccupes }}</h2>
                        </div>
                        <div class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                            <i class="fas fa-user-clock"></i>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
         <!-- Carte Cumul des Loyers -->
        <div class="col-xl-6 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Revenu Mensuel</h6>
                            <h2 class="font-weight-bold mb-0" style="font-size: 25px">{{ number_format($cumulLoyers, 0, ',', ' ') }} FCFA</h2>
                        </div>
                        <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Solde disponible</h6>
                            <h2 class="font-weight-bold mb-0">{{ number_format($soldeDisponible) }} FCFA</h2>
                        </div>
                        <div class="icon icon-shape text-white rounded-circle shadow" style="background-color: #02245b">
                            <i class="mdi mdi-cash-multiple"></i>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>

    <!-- Derniers biens ajoutés -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-lg">
                <div class="card-header bg-white border-0 pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-gradient text-primary">Derniers Biens Ajoutés</h5>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table table-flush" id="properties-table">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th class="ps-4">Type</th>
                                    <th>Description</th>
                                    <th>Loyer mensuel</th>
                                    <th>Commune</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersBiens as $bien)
                                <tr class="text-center">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="fas fa-{{ $bien->type == 'Appartement' ? 'building' : 'home' }} me-2 text-primary"></i>
                                            <span class="font-weight-bold">{{ $bien->type }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-sm mb-0">{{ Str::limit($bien->description, 50) }}</p>
                                    </td>
                                    <td class="font-weight-bold">{{ number_format($bien->prix, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $bien->commune }}</td>
                                    <td>
                                        <span class="badge badge-pill badge-{{ $bien->status == 'Disponible' ? 'success' : 'warning' }}">
                                            <i class="fas fa-{{ $bien->status == 'Disponible' ? 'check' : 'clock' }} me-1"></i> {{ $bien->status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Aucun bien enregistré</h5>
                                            <p class="text-muted mb-0">Allez voir votre agence pour ajouter votre premier bien immobilier</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles supplémentaires -->
<style>
    :root {
        --primary: #5e72e4;
        --success: #2dce89;
        --info: #11cdef;
        --warning: #fb6340;
        --danger: #f5365c;
    }
    
    .text-gradient {
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
        z-index: 1;
    }
    
    .text-primary {
        color: var(--primary) !important;
    }
    
    .text-gradient.text-primary {
        background-image: linear-gradient(310deg, #7928CA  0%, #02245b 100%);
    }
    
    .bg-gradient-primary {
        background: linear-gradient(310deg, #7928CA 0%, #02245b 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
    }
    
    .card {
        border: 10;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }
    
    .card-stats .icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .progress {
        height: 6px;
        border-radius: 3px;
        background-color: #f6f9fc;
    }
    
    .progress-bar {
        border-radius: 3px;
    }
    
    .badge {
        font-weight: 500;
        letter-spacing: 0.02em;
        padding: 0.35em 0.65em;
    }
    
    .badge-pill {
        border-radius: 50rem;
    }
    
    .table thead th {
        border-bottom: 1px solid #e9ecef;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #8898aa;
        padding: 1rem 1.5rem;
    }
    
    .table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }
    
    .table-flush tbody tr:last-child td {
        border-bottom: 0;
    }
    
    .btn-outline-primary {
        border-color: var(--primary);
        color: var(--primary);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: white;
    }
    
    .empty-state {
        padding: 2rem;
        text-align: center;
    }
    
    .rounded-circle {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<script>
    // Initialiser les tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection