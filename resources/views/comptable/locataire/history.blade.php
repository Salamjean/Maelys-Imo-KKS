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

     .search-container {
        margin-bottom: 20px;
    }
    .search-input {
        padding: 10px 15px;
        border-radius: 50px;
        border: 1px solid #ddd;
        width: 100%;
        max-width: 400px;
        transition: all 0.3s;
    }
    .search-input:focus {
        border-color: #3a7bd5;
        box-shadow: 0 0 0 0.2rem rgba(58, 123, 213, 0.25);
    }
    .no-results {
        display: none;
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }
</style>

<div class="col-lg-12 stretch-card mt-4">
    <div class="card payment-card">
        <div class="card-header payment-header">
            <h4 class="card-title payment-title text-center text-white"><i class="fas fa-history me-2"></i>Historique des versements</h4>
            <p class="card-description payment-subtitle text-center mb-0 text-white">
                Liste des versements effectués par les agents pour le compte des locataires.
            </p>
        </div>
        <div class="card-body">
            <!-- Ajout de la barre de recherche -->
            <div class="search-container mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="searchInput" class="form-control search-input border-start-0" placeholder="Rechercher un agent, montant ou date...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="versementsTable">
                    <thead class="table text-center" style="background-color: #f1f5fd;">
                        <tr>
                            <th class="py-3 px-4">Agent</th>
                            <th class="py-3 px-4 text-end">Montant versé</th>
                            <th class="py-3 px-4 text-end">Reste à verser</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Heure</th>
                        </tr>
                    </thead>
                    <tbody id="versementsBody">
                        @forelse($versements as $versement)
                        <tr class="text-center versement-row">
                            <td class="text-center agent-cell">
                                <div class="d-flex text-center align-items-center justify-content-center">
                                    <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center text-center">
                                        <i class="mdi mdi-camera-front-variant"></i>
                                    </div>
                                    <div class="text-center">
                                        <h6 class="mb-0 text-center agent-name">{{ $versement->agent->prenom }} {{ $versement->agent->name }}</h6>
                                        <small class="text-muted agent-email">{{ $versement->agent->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 align-middle text-end fw-bold text-success amount-cell">
                                {{ number_format($versement->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="py-3 px-4 align-middle text-end">
                                <div class="d-flex flex-column">
                                    <span class="text-danger mt-1 pt-1 reste-cell">{{ number_format($versement->montant_percu - $versement->montant, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 align-middle date-cell">
                                {{ $versement->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 align-middle time-cell">
                                {{ $versement->created_at->format('H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-muted">
                                <i class="fas fa-info-circle me-2"></i>Aucun versement enregistré
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div id="noResults" class="no-results">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <h5>Aucun résultat trouvé</h5>
                    <p class="text-muted">Essayez avec d'autres termes de recherche</p>
                </div>

                @if($versements->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($versements->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $versements->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($versements->getUrlRange(1, $versements->lastPage()) as $page => $url)
                                @if ($page == $versements->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($versements->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $versements->nextPageUrl() }}" rel="next" aria-label="Next">
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
<script src="{{ asset('login/assets/js/popper.min.js') }}"></script>
<script src="{{ asset('login/assets/js/bootstrap.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const versementsBody = document.getElementById('versementsBody');
    const versementRows = document.querySelectorAll('.versement-row');
    const noResults = document.getElementById('noResults');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let hasResults = false;

        versementRows.forEach(row => {
            const agentName = row.querySelector('.agent-name').textContent.toLowerCase();
            const agentEmail = row.querySelector('.agent-email').textContent.toLowerCase();
            const amount = row.querySelector('.amount-cell').textContent.toLowerCase();
            const reste = row.querySelector('.reste-cell').textContent.toLowerCase();
            const date = row.querySelector('.date-cell').textContent.toLowerCase();
            const time = row.querySelector('.time-cell').textContent.toLowerCase();

            if (agentName.includes(searchTerm) || 
                agentEmail.includes(searchTerm) || 
                amount.includes(searchTerm) || 
                reste.includes(searchTerm) || 
                date.includes(searchTerm) || 
                time.includes(searchTerm)) {
                row.style.display = '';
                hasResults = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Afficher/masquer le message "Aucun résultat"
        if (hasResults || searchTerm === '') {
            noResults.style.display = 'none';
        } else {
            noResults.style.display = 'block';
        }
    });

    // Gestion des erreurs et succès (votre code existant...)
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Erreur de connexion',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Connexion réussie',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000,
            showConfirmButton: true,
            confirmButtonColor: '#3085d6'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 2000,
            showConfirmButton: true,
            confirmButtonColor: '#3085d6'
        });
    @endif
});
</script>
@endsection