@extends('comptable.layouts.template')
@section('content')
<style>
    .tenant-card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 20px;
        overflow: hidden;
        border: none;
    }
    .tenant-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .tenant-header {
        background-color: #02245b;
        color: white;
        padding: 15px;
        position: relative;
    }
    .tenant-status {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-active {
        background-color: #28a745;
    }
    .status-inactive {
        background-color: #dc3545;
    }
    .tenant-body {
        padding: 15px;
    }
    .tenant-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-top: -40px;
        margin-bottom: 10px;
        background-color: #f8f9fa;
    }
    .tenant-info {
        margin-bottom: 10px;
    }
    .tenant-info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .tenant-actions {
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
    }
    .no-tenants {
        text-align: center;
        padding: 50px;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    .payment-badge {
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 10px;
        margin-right: 5px;
    }
    .badge-paid {
        background-color: #28a745;
        color: white;
    }
    .badge-pending {
        background-color: #ffc107;
        color: black;
    }
    .badge-failed {
        background-color: #dc3545;
        color: white;
    }
    .current-month-payment {
        font-weight: bold;
        margin: 10px 0;
        padding: 8px;
        border-radius: 5px;
        text-align: center;
    }
    .payment-paid {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
    }
    .payment-pending {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffc107;
    }
    .payment-failed {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }
    .payment-none {
        background-color: rgba(108, 117, 125, 0.2);
        color: #6c757d;
    }
    .modal-property-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 15px;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mt-4 text-gray-800" style="text-align: center">Gestion des Locataires</h1>
    </div>

    @if($locataires->isEmpty())
        <div class="no-tenants">
            <i class="fas fa-user-slash fa-3x mb-3" style="color: #6c757d;"></i>
            <h4>Aucun locataire enregistré</h4>
            <p class="text-muted">Commencez par ajouter un nouveau locataire</p>
        </div>
    @else
        <div class="row">
            @foreach($locataires as $locataire)
                <!-- Modal pour les informations du bien -->
                <div class="modal fade" id="propertyModal{{ $locataire->id }}" tabindex="-1" role="dialog" aria-labelledby="propertyModalLabel{{ $locataire->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="propertyModalLabel{{ $locataire->id }}">
                                    <i class="fas fa-home"></i> Détails du bien - {{ $locataire->prenom }} {{ $locataire->name }}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @if($locataire->bien)
                                    @if($locataire->bien->images)
                                        <img src="{{ asset('storage/'.$locataire->bien->images->first()->path) }}" class="modal-property-img" alt="Image du bien">
                                    @endif
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Type:</span>
                                                <p>{{ $locataire->bien->type ?? 'Non spécifié' }}</p>
                                            </div>
                                            
                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Adresse:</span>
                                                <p>{{ $locataire->bien->commune ?? 'Non spécifié' }}</p>
                                            </div>
                                            
                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Surface:</span>
                                                <p>{{ $locataire->bien->superficie ?? 'Non spécifié' }} m²</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Loyer:</span>
                                                <p class="font-weight-bold">{{ number_format($locataire->bien->prix ?? 0, 2, ',', ' ') }} FCFA/mois</p>
                                            </div>
                                            
                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Nombre de chambre:</span>
                                                <p>{{ $locataire->bien->nombre_de_chambres ?? 'Non spécifié' }}</p>
                                            </div>

                                            <div class="tenant-info">
                                                <span class="tenant-info-label">Nombre de toillette:</span>
                                                <p>{{ $locataire->bien->nombre_de_toilettes ?? 'Non spécifié' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($locataire->bien->description)
                                        <div class="tenant-info text-center mt-4">
                                            <span class="tenant-info-label">Description:</span>
                                            <p>{{ $locataire->bien->description }}</p>
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-circle"></i> Aucun bien associé à ce locataire
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal pour l'historique des paiements -->
                <div class="modal fade" id="paymentsModal{{ $locataire->id }}" tabindex="-1" role="dialog" aria-labelledby="paymentsModalLabel{{ $locataire->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="paymentsModalLabel{{ $locataire->id }}">
                                    <i class="fas fa-money-bill-wave"></i> Historique des paiements - {{ $locataire->prenom }} {{ $locataire->name }}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Statut du paiement du mois en cours -->
                                @php
                                    $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                                    $currentMonthPayment = $locataire->paiements->where('mois_couvert', $currentMonth)->first();
                                @endphp
                                
                                <div class="current-month-payment 
                                    @if($currentMonthPayment)
                                        @if($currentMonthPayment->statut == 'payé') payment-paid
                                        @elseif($currentMonthPayment->statut == 'En attente') payment-pending
                                        @else payment-failed @endif
                                    @else
                                        payment-none
                                    @endif">
                                    <i class="fas fa-calendar-check"></i> 
                                    Paiement du mois en cours ({{ \Carbon\Carbon::now()->format('m/Y') }}):
                                    
                                    @if($currentMonthPayment)
                                        <strong>{{ $currentMonthPayment->statut }}</strong> - 
                                        {{ number_format($currentMonthPayment->montant, 2, ',', ' ') }} FCFA
                                        <br>
                                        <small>
                                            Méthode: {{ $currentMonthPayment->methode_paiement }} - 
                                            Date: {{ \Carbon\Carbon::parse($currentMonthPayment->date_paiement)->format('d/m/Y') }}
                                        </small>
                                    @else
                                        <strong style="color: #dc3545">Non payé</strong>
                                    @endif
                                </div>
                                
                                <!-- Historique complet des paiements -->
                                <h6 class="mt-4 mb-3"><i class="fas fa-history"></i> Historique complet</h6>
                                
                                @if($locataire->paiements->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mois</th>
                                                    <th>Montant</th>
                                                    <th>Statut</th>
                                                    <th>Méthode</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($locataire->paiements->sortByDesc('date_paiement') as $paiement)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $paiement->mois_couvert)->format('m/Y') }}</td>
                                                        <td>{{ number_format($paiement->montant, 2, ',', ' ') }} FCFA</td>
                                                        <td>
                                                            <span class="payment-badge 
                                                                @if($paiement->statut == 'payé') badge-paid
                                                                @elseif($paiement->statut == 'En attente') badge-pending
                                                                @else badge-failed @endif">
                                                                {{ $paiement->statut }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $paiement->methode_paiement }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <div class="text-right mt-3">
                                        <strong>Total: {{ number_format($locataire->paiements->sum('montant'), 2, ',', ' ') }} FCFA</strong>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Aucun paiement enregistré pour ce locataire
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte du locataire -->
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="card tenant-card">
                        <div class="tenant-header">
                            <span class="tenant-status {{ $locataire->status == 'Actif' ? 'status-active' : 'status-inactive' }}">
                                {{ $locataire->status }}
                            </span>
                            <h5 class="mb-0">Locataire : {{ $locataire->prenom }} {{ $locataire->name }}</h5>
                        </div>
                        
                        <div class="tenant-body text-center">
                            <br><br>
                            @if($locataire->agence && $locataire->profile_image)
                                <img src="{{ asset('storage/'.$locataire->profile_image) }}" 
                                    class="tenant-avatar" 
                                    alt="Photo agence"
                                    title="Agence: {{ $locataire->agence->name }}">
                            @elseif($locataire->image1)
                                <img src="{{ asset('storage/'.$locataire->image1) }}" 
                                    class="tenant-avatar" 
                                    alt="Photo locataire">
                            @else
                                <img src="{{ asset('assets/images/useriii.jpeg') }}" 
                                class="tenant-avatar" 
                                alt="Avatar par défaut">
                            @endif
                            
                            <div class="tenant-info">
                                <span class="tenant-info-label">Email:</span>
                                <p>{{ $locataire->email }}</p>
                            </div>
                            
                            <div class="tenant-info">
                                <span class="tenant-info-label">Téléphone:</span>
                                <p>{{ $locataire->contact }}</p>
                            </div>
                            
                            <div class="tenant-info">
                                <span class="tenant-info-label">Adresse:</span>
                                <p>{{ $locataire->adresse }}</p>
                            </div>
                            
                            <!-- Boutons pour ouvrir les modales -->
                            <div class="d-flex justify-content-center mt-3">
                                @if($locataire->bien)
                                    <button class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#propertyModal{{ $locataire->id }}">
                                        <i class="fas fa-home"></i> Voir le bien
                                    </button>
                                @endif
                                
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#paymentsModal{{ $locataire->id }}">
                                    <i class="fas fa-money-bill-wave"></i> Paiements
                                </button>
                            </div>
                            
                            <!-- Statut du paiement du mois en cours (mini version) -->
                            @php
                                $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                                $currentMonthPayment = $locataire->paiements->where('mois_couvert', $currentMonth)->first();
                            @endphp
                            
                            <div class="mt-3 text-center">
                                <small>Statut du mois:</small>
                                <span class="payment-badge 
                                    @if($currentMonthPayment)
                                        @if($currentMonthPayment->statut == 'payé') badge-paid
                                        @elseif($currentMonthPayment->statut == 'En attente') badge-pending
                                        @else badge-failed @endif
                                    @else
                                        badge-failed
                                    @endif">
                                    @if($currentMonthPayment)
                                        {{ $currentMonthPayment->statut }}
                                    @else
                                        Non payé
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Script pour gérer les modales -->
<script>
    $(document).ready(function() {
        // Initialisation des modales Bootstrap
        $('.modal').modal({
            show: false
        });
    });
</script>
@endsection