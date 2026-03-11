@extends('commercial.layouts.template')

@section('content')
<div class="content-wrapper p-4" style="background: #f8fafc !important;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold" style="color: #02245b;">Mes Statistiques d'Activité</h2>
            <p class="text-muted">Suivez vos performances quotidiennes, hebdomadaires et globales.</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="bg-white p-3 rounded-lg shadow-sm mr-3">
                <span class="text-muted small">Aujourd'hui :</span>
                <span class="font-weight-bold ml-2">{{ now()->translatedFormat('d F Y') }}</span>
            </div>
            <a href="{{ route('commercial.statistics.pdf') }}" class="btn btn-primary shadow-sm" style="background: #02245b; border: none; border-radius: 12px; padding: 12px 20px;">
                <i class="mdi mdi-file-pdf mr-2"></i> Exporter en PDF
            </a>
        </div>
    </div>

    <!-- Section: Statistiques Globales -->
    <h4 class="font-weight-bold mb-3" style="color: #02245b;">Performance Globale</h4>
    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: white; border-left: 5px solid #02245b !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small text-uppercase font-weight-bold mb-1">Total Agences</p>
                            <h2 class="font-weight-bold mb-0" style="color: #02245b;">{{ $totalAgences }}</h2>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="mdi mdi-domain mdi-24px text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: white; border-left: 5px solid #ff5e14 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small text-uppercase font-weight-bold mb-1">Total Propriétaires</p>
                            <h2 class="font-weight-bold mb-0" style="color: #ff5e14;">{{ $totalProprietaires }}</h2>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="mdi mdi-account mdi-24px text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: white; border-left: 5px solid #00d082 !important;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small text-uppercase font-weight-bold mb-1">Total Biens</p>
                            <h2 class="font-weight-bold mb-0" style="color: #00d082;">{{ $totalBiens }}</h2>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="mdi mdi-home-modern mdi-24px text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Daily Activity Graph -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <h4 class="card-title font-weight-bold mb-4" style="color: #02245b;">Activité des 7 derniers jours</h4>
                    <div style="height: 350px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Snapshot -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; background: linear-gradient(135deg, #02245b 0%, #053a8a 100%); color: white;">
                <div class="card-body p-4">
                    <h4 class="font-weight-bold mb-4">Aujourd'hui</h4>
                    <div class="d-flex flex-column justify-content-around h-100 pb-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="mr-3 p-3 rounded-lg" style="background: rgba(255,255,255,0.1);">
                                <i class="mdi mdi-domain mdi-24px"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 font-weight-bold">{{ $dailyAgences }}</h3>
                                <p class="mb-0 small opacity-75">Nouvelles Agences</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-4">
                            <div class="mr-3 p-3 rounded-lg" style="background: rgba(255,255,255,0.1);">
                                <i class="mdi mdi-account mdi-24px"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 font-weight-bold">{{ $dailyProprietaires }}</h3>
                                <p class="mb-0 small opacity-75">Nouveaux Propriétaires</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="mr-3 p-3 rounded-lg" style="background: rgba(255,255,255,0.1);">
                                <i class="mdi mdi-home-modern mdi-24px"></i>
                            </div>
                            <div>
                                <h3 class="mb-0 font-weight-bold">{{ $dailyBiens }}</h3>
                                <p class="mb-0 small opacity-75">Nouveaux Biens</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Journal d'Activité -->
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4">
            <h4 class="card-title font-weight-bold mb-4" style="color: #02245b;">Journal d'Activité Récente</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0 text-center">Agences</th>
                            <th class="border-0 text-center">Propriétaires</th>
                            <th class="border-0 text-center">Biens</th>
                            <th class="border-0 text-center">Total Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $day)
                            <tr>
                                <td class="py-3 font-weight-bold text-muted">{{ $day['date'] }}</td>
                                <td class="py-3 text-center">
                                    <span class="badge badge-pill badge-outline-primary" style="width: 40px;">{{ $day['agences'] }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge badge-pill badge-outline-warning" style="width: 40px;">{{ $day['proprietaires'] }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge badge-pill badge-outline-success" style="width: 40px;">{{ $day['biens'] }}</span>
                                </td>
                                <td class="py-3 text-center font-weight-bold" style="color: #02245b;">
                                    {{ $day['agences'] + $day['proprietaires'] + $day['biens'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('activityChart').getContext('2d');
        
        const labels = @json($stats['labels']);
        const agencesData = @json($stats['agences']);
        const proprietairesData = @json($stats['proprietaires']);
        const biensData = @json($stats['biens']);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Agences',
                        data: agencesData,
                        backgroundColor: '#02245b',
                        borderRadius: 5,
                    },
                    {
                        label: 'Propriétaires',
                        data: proprietairesData,
                        backgroundColor: '#ff5e14',
                        borderRadius: 5,
                    },
                    {
                        label: 'Biens',
                        data: biensData,
                        backgroundColor: '#00d082',
                        borderRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { borderDash: [5, 5] }
                    }
                }
            }
        });
    });
</script>
@endsection
