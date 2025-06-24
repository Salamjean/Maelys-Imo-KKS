@extends('comptable.layouts.template')
@section('content')
<style>
    .dashboard-card {
        border-radius: 12px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
        position: relative;
        margin-bottom: 25px;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
    }
    
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: rgba(255, 255, 255, 0.3);
    }
    
    .card-primary {
        background: linear-gradient(135deg, #02245b 0%, #0250a3 100%);
    }
    
    .card-success {
        background: linear-gradient(135deg, #00a65a 0%, #00c271 100%);
    }
    
    .card-info {
        background: linear-gradient(135deg, #8628e1 0%, #9c4dff 100%);
    }
    
    .card-warning {
        background: linear-gradient(135deg, #ff5e14 0%, #ff8a4d 100%);
    }
    
    .card-danger {
        background: linear-gradient(135deg, #ff9d14 0%, #ffc04d 100%);
    }
    
    .card-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #8e9ba4 100%);
    }
    
    .card-body {
        padding: 25px;
        position: relative;
        z-index: 1;
    }
    
    .card-icon {
        font-size: 40px;
        opacity: 0.3;
        position: absolute;
        right: 20px;
        top: 20px;
    }
    
    .card-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .card-value {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .card-text {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .dashboard-header {
        margin-bottom: 40px;
        text-align: center;
    }
    
    .dashboard-header h1 {
        font-weight: 700;
        color: #2c3e50;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
    }
    
    .dashboard-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: linear-gradient(to right, #02245b, #00a65a);
    }
    
    .recent-payments {
        max-height: 400px;
        overflow-y: auto;
         -webkit-overflow-scrolling: touch; /* Pour mobile */
    overscroll-behavior: contain; /* Empêche le scroll sur le parent */
    }
    
    .payment-item {
        border-left: 3px solid;
        padding-left: 10px;
        margin-bottom: 15px;
    }
    
    .payment-paid {
        border-left-color: #00a65a;
    }
    
    .payment-pending {
        border-left-color: #ffc107;
    }
    
    .payment-method {
        font-size: 12px;
        color: #6c757d;
    }
    
    @media (max-width: 768px) {
        .card-value {
            font-size: 24px;
        }
        
        .card-icon {
            font-size: 30px;
        }
    }
</style>

<div class="container-fluid">
    <div class="dashboard-header">
        <h1>Tableau de Bord Comptable</h1>
        <p class="text-muted">Statistiques financières et indicateurs clés</p>
    </div>

    <div class="row">
        <!-- Card Nombre total de locataires -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-primary text-white">
                <div class="card-body">
                    <i class="fas fa-users card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Locataires</h5>
                    <h2 class="card-value">{{ $totalLocataires }}</h2>
                    <p class="card-text">Total des locataires</p>
                </div>
            </div>
        </div>

        <!-- Card Locataires actifs -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-success text-white">
                <div class="card-body">
                    <i class="fas fa-user-check card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Locataires actifs</h5>
                    <h2 class="card-value">{{ $locatairesActifs }}</h2>
                    <p class="card-text">Actuellement actifs</p>
                </div>
            </div>
        </div>

        <!-- Card Biens loués -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-info text-white">
                <div class="card-body">
                    <i class="fas fa-home card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Biens loués</h5>
                    <h2 class="card-value">{{ $biensLoues }}</h2>
                    <p class="card-text">Occupés actuellement</p>
                </div>
            </div>
        </div>

        <!-- Card Biens disponibles -->
        <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-warning text-white">
                <div class="card-body">
                    <i class="fas fa-door-open card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Biens disponibles</h5>
                    <h2 class="card-value">{{ $biensDisponibles }}</h2>
                    <p class="card-text">Prêts à être loués</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Card Loyers ce mois -->
        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-danger text-white">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Loyers payer ce mois</h5>
                    <h2 class="card-value">{{ number_format($loyersMoisCourant, 0, ',', ' ') }} FCFA</h2>
                    <p class="card-text">Perçus ce mois-ci</p>
                </div>
            </div>
        </div>

        <!-- Card Loyers cette année -->
        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-secondary text-white">
                <div class="card-body">
                    <i class="fas fa-chart-line card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Loyers cette année</h5>
                    <h2 class="card-value">{{ number_format($loyersAnneeCourante, 0, ',', ' ') }} FCFA</h2>
                    <p class="card-text">Cumul annuel</p>
                </div>
            </div>
        </div>

        <!-- Card Paiements en attente -->
        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="card dashboard-card card-primary text-white">
                <div class="card-body">
                    <i class="fas fa-clock card-icon"></i>
                    <h5 class="card-title" style="color:white; font-weight:bold">Locataires en retard</h5>
                    <h2 class="card-value">{{ $latePayersCount }}</h2>
                    <p class="card-text">En validation</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Activité récente -->
   <div class="row">
        <!-- Derniers paiements -->
        <div class="col-lg-8 mb-4">
            <div class="card dashboard-card">
                <div class="card-header" style="background-color: #02245b">
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
                <div class="card-header" style="background-color: #02245b">
                    <h5 class="card-title text-white"><i class="fas fa-exclamation-circle me-2"></i>Locataires en Retard</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($latePayers as $locataire)
                        <div class="list-group-item border-0 mb-2 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $locataire->name }} {{ $locataire->prenom }}</h6>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Animation au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.dashboard-card');
        
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
            
            // Style initial pour l'animation
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });

         // Graphique des loyers mensuels
        const ctx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                    label: 'Loyers perçus (FCFA)',
                    data: @json($data),
                    backgroundColor: 'rgba(2, 36, 91, 0.7)',
                    borderColor: 'rgba(2, 80, 163, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection