@extends('agence.layouts.template')
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
            <h4 class="card-title payment-title text-center text-white"><i class="fas fa-history me-2"></i>Tous les proprietaires abonnés</h4>
            <p class="card-description payment-subtitle text-center mb-0 text-white">
                Liste complète des propriétaires abonnés avec leurs paiements et détails associés.
            </p>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un locataire...">
            </div>
            <div class="table-responsive">
                <table class="table payment-table">
                    <thead>
                        <tr class="text-center">
                            <th>Propriétaire</th>
                            <th>Mode de paiement</th>
                            <th>Date de validation</th>
                            <th>Bénéficiaire</th>
                            <th>Montant</th>
                            <th>Numéro CNI</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paiements as $paiement)
                        <tr class="text-center">
                            <td>{{ $paiement->proprietaire->prenom }} {{ $paiement->proprietaire->name }}</td>
                            <td>{{ $paiement->mode_paiement }}</td>
                            <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                            <td>
                                @if($paiement->est_proprietaire)
                                    Propriétaire
                                @else
                                    {{ $paiement->beneficiaire_prenom ?? 'Propriétaire' }} {{ $paiement->beneficiaire_nom }} 
                                @endif
                            </td>
                            <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                            <td>
                                @if($paiement->numero_cni)
                                                @php
                                                    $contratPath = asset('storage/' . $paiement->numero_cni);
                                                    $contratPathPdf = strtolower(pathinfo($contratPath, PATHINFO_EXTENSION)) === 'pdf';
                                                @endphp
                                                @if ($contratPathPdf)
                                                    <a href="{{ $contratPath }}" target="_blank">
                                                        <img src="{{ asset('assets/images/pdf.jpg') }}" alt="PDF" width="30" height="30">
                                                    </a>
                                                @else
                                                    <img src="{{ $contratPath }}" 
                                                        alt="Pièce du parent" 
                                                        width="50" 
                                                        height=50
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#imageModal" 
                                                        onclick="showImage(this)" 
                                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/profiles/bébé.jpg') }}'">
                                                @endif
                                            @else
                                                <p>Paiement non effectué</p>
                                            @endif
                            </td>
                            <td>
                                @if($paiement->statut === 'en attente')
                                    <span class="badge badge-warning">En attente</span>
                                @else
                                    <span class="badge badge-success">Payé</span>
                                    @if($paiement->date_validation)
                                        <br><small>Validé le {{ \Carbon\Carbon::parse($paiement->date_validation)->format('d/m/Y') }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $paiement->created_at->format('d/m/Y') }}</td>
                            <td>
                                @if($paiement->statut === 'en attente' && $paiement->mode_paiement === 'Chèques' && !$paiement->est_proprietaire)
                                    <a href="{{ route('partner.payment.validate.form', $paiement->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        Valider
                                    </a>
                                @else
                                   <p>Paiement reçu</p>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script>
$(document).ready(function() {
    // Système de recherche en temps réel
    $('#searchInput').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        
        $('.subscription-table tbody tr').each(function() {
            // Exclure la ligne "Aucun résultat" si elle existe
            if ($(this).find('td[colspan="10"]').length) {
                return true; // continue à l'itération suivante
            }
            
            const rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Gérer le cas où aucune correspondance n'est trouvée
        const visibleRows = $('.subscription-table tbody tr:visible').not('tr:has(td[colspan="10"])').length;
        if (visibleRows === 0) {
            // Supprimer d'abord les anciens messages "Aucun résultat"
            $('.subscription-table tbody tr td[colspan="10"]').closest('tr').remove();
            
            // Ajouter le nouveau message seulement si ce n'est pas déjà là
            if ($('.subscription-table tbody tr td[colspan="10"]').length === 0) {
                $('.subscription-table tbody').append(`
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h5>Aucun résultat trouvé</h5>
                                <p class="text-muted">Aucun abonnement ne correspond à votre recherche.</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            // Supprimer le message "Aucun résultat" s'il existe
            $('.subscription-table tbody tr td[colspan="10"]').closest('tr').remove();
        }
    });
});
</script>
<style>
#searchInput {
    padding: 10px 15px;
    border-radius: 20px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

#searchInput:focus {
    border-color: #4b7bec;
    box-shadow: 0 2px 10px rgba(75, 123, 236, 0.3);
    outline: none;
}

.empty-state .empty-icon {
    font-size: 3rem;
    color: #a5b1c2;
    margin-bottom: 1rem;
}
</style>
@endsection