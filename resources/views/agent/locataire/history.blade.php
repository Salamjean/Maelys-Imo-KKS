@extends('comptable.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .payment-card {
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
    }
    
    .payment-header {
        background: linear-gradient(135deg, #02245b 0%, #3a7bd5 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .payment-title {
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .payment-subtitle {
        opacity: 0.9;
        font-weight: 300;
    }
    
    .payment-table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    
    .payment-table thead th {
        background-color: #02245b;
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px;
    }
    
    .payment-table tbody tr {
        background-color: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .payment-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .payment-table td {
        padding: 15px;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .payment-table td:first-child {
        border-left: 4px solid #3a7bd5;
        border-radius: 10px 0 0 10px;
    }
    
    .payment-table td:last-child {
        border-radius: 0 10px 10px 0;
    }
    
    .amount-cell {
        font-weight: 700;
        color: #02245b;
        font-size: 1.1rem;
    }
    
    .status-badge {
        padding: 8px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-paid {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .empty-state {
        padding: 3rem;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    
    .empty-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }
    
    .pagination-rounded .page-item:first-child .page-link {
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
    }
    
    .pagination-rounded .page-item:last-child .page-link {
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }
    
    .page-link {
        color: #02245b;
        border: 1px solid #dee2e6;
        margin: 0 3px;
    }
    
    .page-item.active .page-link {
        background-color: #02245b;
        border-color: #02245b;
    }
    
    .payment-method-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 8px;
        background-color: #f1f5fd;
        color: #3a7bd5;
    }
</style>

<div class="col-lg-12 stretch-card mt-4">
    <div class="card payment-card">
        <div class="card-header payment-header">
            <h4 class="card-title payment-title text-center text-white"><i class="fas fa-history me-2"></i>Historique des paiements</h4>
            <p class="card-description payment-subtitle text-center mb-0 text-white">
                Liste complète des paiements que vous avez validés
            </p>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table payment-table">
                    <thead>
                        <tr class="text-center">
                            <th>Montant</th>
                            <th>Type</th>
                            <th>Code</th>
                            <th>Mois</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paiements as $paiement)
                            <tr class="text-center">
                                <td class="amount-cell">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if($paiement->methode_paiement === 'Espèces')
                                        <span class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    @elseif($paiement->methode_paiement === 'Mobile Money')
                                        <span class="payment-method-icon"><i class="fas fa-mobile-alt"></i></span>
                                    @else
                                        <span class="payment-method-icon"><i class="fas fa-credit-card"></i></span>
                                    @endif
                                    {{ $paiement->methode_paiement }}
                                </td>
                                <td>
                                    @if($paiement->verif_espece)
                                        <span class="badge bg-light text-dark">{{ $paiement->verif_espece }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->translatedFormat('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($paiement->created_at)->format('H:i') }}</td>
                                <td>
                                    @if($paiement->statut === 'payé')
                                        <span class="status-badge status-paid"><i class="fas fa-check-circle me-1"></i> Payé</span>
                                    @else
                                        <span class="status-badge status-pending"><i class="fas fa-clock me-1"></i> En attente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <h5>Aucun paiement enregistré</h5>
                                        <p class="text-muted">Vous n'avez encore validé aucun paiement.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($paiements->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($paiements->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $paiements->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($paiements->getUrlRange(1, $paiements->lastPage()) as $page => $url)
                                @if ($page == $paiements->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($paiements->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $paiements->nextPageUrl() }}" rel="next" aria-label="Next">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection