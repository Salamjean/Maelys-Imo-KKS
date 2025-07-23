@extends('admin.layouts.template')
@section('title', 'Dashboard')
@section('content')

<style>
    .text-gradient {
        background: linear-gradient(45deg, #4e73df, #1cc88a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
    }
    
    .text-gradient-primary {
        color: #4e73df;
    }
    
    .text-gradient-success {
        color: #1cc88a;
    }
    
    .text-gradient-info {
        color: #36b9cc;
    }
    
    .text-gradient-warning {
        color: #f6c23e;
    }
    
    .section-title {
        position: relative;
        padding-bottom: 10px;
    }
    
    .section-title:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(45deg, #4e73df, #1cc88a);
        border-radius: 3px;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .nav-pills-custom .nav-link {
        color: #6c757d;
        border-radius: 20px;
        margin: 0 5px;
        padding: 5px 15px;
        transition: all 0.3s;
    }
    
    .nav-pills-custom .nav-link.active {
        background: linear-gradient(45deg, #4e73df, #1cc88a);
        color: white;
        box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
</style>
<div class="main-panel">
  <div >
    <!-- row end -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="" style="color: #02245b; text-align:center">Tableau de Bord</h2>
      <div class="date-display bg-light p-2 rounded">
        <span id="current-date" class="text-muted"></span>
      </div>
    </div>

    <!-- Cartes Statistiques -->
    <h4 class="section-title mb-4">Total de biens publiés sur la plateforme</h4>
    <div class="row">
      <!-- Carte Appartement -->
      <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow-sm h-100 py-2" style="background-color:#02245b">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Appartements</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800 text-white text-center" style="font-size: 50px">{{ $totalAppartements }}</div>
              </div>
              <div class="col-auto">
                <i class="mdi mdi-home-account text-white icon-lg"></i>
              </div>
            </div>
            <div class="mt-2">
              <span class="badge badge-pill badge-light">Sur notre plateforme</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Carte Maison -->
      <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow-sm h-100 py-2" style="background-color: #00d082">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-white text-uppercase mb-1">Maisons</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800 text-white text-center" style="font-size: 50px">{{ $totalMaisons }}</div>
              </div>
              <div class="col-auto">
                <i class="mdi mdi-home-variant text-white icon-lg"></i>
              </div>
            </div>
            <div class="mt-2">
              <span class="badge badge-pill badge-light">Sur notre plateforme</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Carte Magasin -->
      <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow-sm h-100 py-2" style="background-color: #1cbccd">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold  text-uppercase mb-1 text-white">Bureaux</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800 text-white text-center" style="font-size: 50px">{{ $totalMagasins }}</div>
              </div>
              <div class="col-auto">
                <i class="mdi mdi-home icon-lg text-white"></i>
              </div>
            </div>
            <div class="mt-2">
              <span class="badge badge-pill badge-light">Sur notre plateforme</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- row end -->


    <div class="row">
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <p class="card-title">Statistiques de biens publiés</p>
            <div class="row mb-3">
              <div class="col-md-7">
                <div class="d-flex justify-content-between traffic-status">
                  <div class="item">
                    <p class="mb-">Appartement</p>
                    <h5 class="font-weight-bold mb-0">{{ $totalAppartements }}</h5>
                    <div class="color-border"></div>
                  </div>
                  <div class="item">
                    <p class="mb-">Maison</p>
                    <h5 class="font-weight-bold mb-0">{{ $totalMaisons }}</h5>
                    <div class="color-border"></div>
                  </div>
                  <div class="item">
                    <p class="mb-">Magasin</p>
                    <h5 class="font-weight-bold mb-0">{{ $totalMagasins }}</h5>
                    <div class="color-border bg-[#2caae1]"></div>
                  </div>
                </div>
              </div>
              <div class="col-md-5">
                <ul class="nav nav-pills nav-pills-custom justify-content-md-end" id="pills-tab-custom" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="pills-home-tab-custom" data-toggle="pill" href="#pills-health" role="tab" aria-controls="pills-home" aria-selected="true">
            Jour
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="pills-profile-tab-custom" data-toggle="pill" href="#pills-career" role="tab" aria-controls="pills-profile" aria-selected="false">
            Semaine
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="pills-contact-tab-custom" data-toggle="pill" href="#pills-music" role="tab" aria-controls="pills-contact" aria-selected="false">
            Mois
        </a>
    </li>
</ul>
              </div>
            </div>
            <canvas id="property-chart"></canvas>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Liste des biens ajoutés récemment</h4>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr class="text-center">
                    <th>Image</th>
                    <th>Agence</th>
                    <th>Type</th>
                    <th>Superficie</th>
                    <th>Commune</th>
                    <th>Loyer mensuel</th>
                    <th>Date d'enregistrement</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentBiens as $bien)
                  <tr class="text-center">
                    <td class="py-1">
                      <img src="{{ asset('storage/'.$bien->image) }}" alt="image" width="50"/>
                    </td>
                    <td ><strong>{{ $bien->agence->name ?? 'Maelys-Imo' }}</strong></td>
                    <td>{{ $bien->type }}</td>
                    <td>{{ $bien->superficie }} m²</td>
                    <td>{{ $bien->commune }}</td>
                    <td>{{ number_format($bien->prix) }} FCFA</td>
                    <td>{{ $bien->created_at->format('d/m/Y') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- content-wrapper ends -->
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialisation du graphique
    var ctx = document.getElementById('property-chart').getContext('2d');
    
    // Configuration des options pour l'axe Y avec valeurs entières
    var chartOptions = {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    precision: 0,
                    callback: function(value) {
                        if (Number.isInteger(value)) {
                            return value;
                        }
                    },
                    min: 0
                }
            }
        },
        animation: {
            duration: 1000
        }
    };

    // Données initiales
    var chartData = {
        labels: ['Appartements', 'Maisons', 'Magasins'],
        datasets: [{
            label: 'Nombre de biens',
            data: [
                {{ $stats['day']['appartements'] }},
                {{ $stats['day']['maisons'] }},
                {{ $stats['day']['magasins'] }}
            ],
            backgroundColor: [
                'rgba(59, 89, 152, 0.7)',
                'rgba(221, 75, 57, 0.7)',
                'rgba(29, 161, 242, 0.7)'
            ],
            borderColor: [
                'rgba(59, 89, 152, 1)',
                'rgba(221, 75, 57, 1)',
                'rgba(29, 161, 242, 1)'
            ],
            borderWidth: 1
        }]
    };

    // Création du graphique
    var propertyChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: chartOptions
    });

    // Fonction pour mettre à jour les données
    function updateChartData(period) {
        var newData = [];
        switch(period) {
            case '#pills-health': // Jour
                newData = [
                    {{ $stats['day']['appartements'] }},
                    {{ $stats['day']['maisons'] }},
                    {{ $stats['day']['magasins'] }}
                ];
                break;
            case '#pills-career': // Semaine
                newData = [
                    {{ $stats['week']['appartements'] }},
                    {{ $stats['week']['maisons'] }},
                    {{ $stats['week']['magasins'] }}
                ];
                break;
            case '#pills-music': // Mois
                newData = [
                    {{ $stats['month']['appartements'] }},
                    {{ $stats['month']['maisons'] }},
                    {{ $stats['month']['magasins'] }}
                ];
                break;
        }
        
        // Animation de mise à jour
        propertyChart.data.datasets[0].data = newData;
        propertyChart.update();
    }

    // Gestion du changement d'onglet
    $('a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr('href');
        updateChartData(target);
    });

    // Actualisation automatique toutes les 5 secondes
    setInterval(function() {
        $.ajax({
            url: '/dashboard/stats',
            method: 'GET',
            success: function(response) {
                var activeTab = $('.nav-pills .active a').attr('href');
                switch(activeTab) {
                    case '#pills-health':
                        propertyChart.data.datasets[0].data = [
                            response.day.appartements,
                            response.day.maisons,
                            response.day.magasins
                        ];
                        break;
                    case '#pills-career':
                        propertyChart.data.datasets[0].data = [
                            response.week.appartements,
                            response.week.maisons,
                            response.week.magasins
                        ];
                        break;
                    case '#pills-music':
                        propertyChart.data.datasets[0].data = [
                            response.month.appartements,
                            response.month.maisons,
                            response.month.magasins
                        ];
                        break;
                }
                propertyChart.update();
            },
            error: function(xhr, status, error) {
                console.error('Erreur lors de la récupération des données:', error);
            }
        });
    }, 5000);
});
</script>
@endsection