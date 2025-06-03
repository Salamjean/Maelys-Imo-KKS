@extends('comptable.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --accent-color: #4895ef;
        --dark-blue: #02245b;
        --light-bg: #f8f9fa;
        --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .page-title {
        color: var(--dark-blue);
        font-weight: 700;
        margin-bottom: 2rem;
        position: relative;
        padding-bottom: 15px;
        text-align: center;
    }
    
    .page-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        border-radius: 2px;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 2rem;
        width: 100%;
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--dark-blue), var(--secondary-color));
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
    }
    
    .card-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1.4rem;
    }
    
    .card-description {
        color: #6c757d;
        font-size: 0.95rem;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background: var(--dark-blue);
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        vertical-align: middle;
        border-bottom: none;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
    }
    
    .table tbody td {
        padding: 1.1rem;
        vertical-align: middle;
        border-top: 1px solid rgba(0,0,0,0.03);
    }
    
    .btn {
        font-weight: 500;
        letter-spacing: 0.5px;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border: none;
    }
    
    .btn-primary:hover {
        background-color: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    .empty-message {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
        font-size: 1.1rem;
    }
    
    .empty-message i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }
    
    .pagination {
        --bs-pagination-color: var(--dark-blue);
        --bs-pagination-active-bg: var(--dark-blue);
        --bs-pagination-active-border-color: var(--dark-blue);
        --bs-pagination-focus-color: var(--dark-blue);
        --bs-pagination-hover-color: var(--dark-blue);
    }
    
    .pagination .page-item.active .page-link {
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            border: none;
        }
        
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 0;
        }
        
        .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border: none;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }
        
        .table tbody td:before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--dark-blue);
            margin-right: 1rem;
        }
        
        .table tbody td:last-child {
            border-bottom: none;
            justify-content: center;
        }
    }
</style>

<div class="container py-4 col-lg-12">
    <h2 class="page-title">
        <i class="fas fa-money-bill-wave me-2"></i>Générer un code pour paiement en espèces
    </h2>

    <div class="card">
        <div class="card-header ">
            <h5 class="card-title mb-0 text-white">
                <i class="fas fa-users me-2 text-white"></i>Liste des locataires
            </h5>
            <p class="card-description mb-0 text-white text-center">Sélectionnez un locataire pour accéder à la page de génération de code</p>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Adresse</th>
                            <th>Mois en cours</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locataires as $locataire)
                            <tr class="text-center">
                                <td data-label="Nom">{{ $locataire->name }}</td>
                                <td data-label="Prénom">{{ $locataire->prenom }}</td>
                                <td data-label="Contact">{{ $locataire->contact }}</td>
                                <td data-label="Email">{{ $locataire->email }}</td>
                                <td data-label="Adresse">{{ $locataire->adresse }}</td>
                                <td>
                                    {{ $moisEnCours }} <br><br>
                                    @if($locataire->paiements->isNotEmpty())
                                        <span class="badge bg-success text-white">Payé</span>
                                    @else
                                        <span class="badge bg-danger">Non payé</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('locataires.generateCodePage', $locataire->id) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-qrcode me-1"></i>Valider le paiement
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-message">
                                        <i class="fas fa-user-slash"></i>
                                        <p>Aucun locataire trouvé</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($locataires->hasPages())
            <div class="p-3">
                <div class="d-flex justify-content-center">
                    {{ $locataires->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection