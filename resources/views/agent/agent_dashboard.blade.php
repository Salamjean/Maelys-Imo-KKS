@extends('comptable.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .dashboard-card {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: none;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #02245b 0%, #3a7bd5 100%);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.2rem;
    }
    
    .card-title {
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #02245b;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.2;
        position: absolute;
        right: 20px;
        top: 20px;
        color: #02245b;
    }
    
    .recent-payments-table {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    
    .recent-payments-table tbody tr {
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }
    
    .badge-paid {
        background-color: #d4edda;
        color: #155724;
    }
    
    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .progress {
        height: 10px;
        border-radius: 5px;
    }
    
    .progress-bar {
        background-color: #3a7bd5;
    }
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold text-center mt-4">Tableau de Bord Comptable</h3>
            <p class="text-muted text-center" >Aperçu des activités de paiement</p>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Statistiques principales -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Paiements ce mois</h6>
                            <h3 class="stat-number">{{ $paidThisMonthCount }}</h3>
                        </div>
                        <i class="fas fa-calendar-check stat-icon"></i>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-success">
                            <span class="fw-bold">{{ $paymentPercentage }}%</span> des locataires
                        </p>
                        <div class="progress mt-2">
                            <div class="progress-bar" role="progressbar" style="width: {{ $paymentPercentage }}%" 
                                 aria-valuenow="{{ $paymentPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Montant total</h6>
                            <h3 class="stat-number">{{ number_format($totalAmountThisMonth, 0, ',', ' ') }} FCFA</h3>
                        </div>
                        <i class="fas fa-coins stat-icon"></i>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-muted">
                            Moyenne: {{ number_format($averagePayment, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">En attente</h6>
                            <h3 class="stat-number">{{ $pendingPaymentsCount }}</h3>
                        </div>
                        <i class="fas fa-clock stat-icon"></i>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-warning">
                            {{ $pendingAmount }} FCFA non perçus
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Locataires en retard</h6>
                            <h3 class="stat-number">{{ $latePayersCount }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle stat-icon"></i>
                    </div>
                    <div class="mt-3">
                        <p class="mb-0 text-danger">
                            {{ $latePayersPercentage }}% des locataires
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Derniers paiements -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title text-white"><i class="fas fa-history me-2"></i>Derniers Paiements Enregistrés</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table recent-payments-table">
                            <thead>
                                <tr >
                                    <th class="text-center">Locataire</th>
                                    <th class="text-center">Montant</th>
                                    <th class="text-center">Méthode</th>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPayments as $payment)
                                <tr class="text-center">
                                    <td>
                                        <div class="d-flex align-items-center text-center" style="justify-content: center;">
                                            <div class=" text-center">
                                                <h6 class="mb-0 text-center" >{{ $payment->locataire->name }} {{ $payment->locataire->prenom }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($payment->montant, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @if($payment->methode_paiement === 'Mobile Money')
                                            <span class="badge bg-info text-white">{{ $payment->methode_paiement }}</span>
                                        @elseif($payment->methode_paiement === 'Espèces')
                                            <span class="badge bg-secondary">{{ $payment->methode_paiement }}</span>
                                        @else
                                            <span class="badge bg-primary">{{ $payment->methode_paiement }}</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if($payment->statut === 'payé')
                                            <span class="badge badge-paid"><i class="fas fa-check-circle me-1"></i> Payé</span>
                                        @else
                                            <span class="badge badge-pending"><i class="fas fa-clock me-1"></i> En attente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="alert alert-info">
                                            Aucun paiement récent trouvé
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

        <!-- Locataires en retard -->
        <div class="col-lg-4 mb-4">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="card-title text-white"><i class="fas fa-exclamation-circle me-2"></i>Locataires en Retard</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($latePayers as $locataire)
                        <div class="list-group-item border-0 mb-2 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $locataire->name }} {{ $locataire->prenom }} ?? 'lojfkr'</h6>
                                    <small class="text-muted">{{ $locataire->contact }}</small>
                                </div>
                                <span class="badge bg-danger">+{{ $locataire->days_late }} jours</span>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Dernier paiement: 
                                    @if($locataire->last_payment_date)
                                        {{ \Carbon\Carbon::parse($locataire->last_payment_date)->translatedFormat('d M Y') }}
                                    @else
                                        Jamais payé
                                    @endif
                                </small>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-success">
                            Tous les locataires sont à jour dans leurs paiements!
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection