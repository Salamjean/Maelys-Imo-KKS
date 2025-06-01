@extends('proprietaire.layouts.template')
@section('content')
<div class="container-fluid px-4 py-5">
    <div class="d-sm-flex align-items-center justify-content-center mb-5">
        <h1 class="text-gradient text-primary mb-0">
            <i class="fas fa-home me-2"></i> Mes Biens Immobiliers
        </h1>
    </div>

    <div class="row">
        @forelse($biens as $bien)
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="property-card card h-100 shadow-lg border-0 overflow-hidden">
                <!-- Badge statut -->
                <div class="position-absolute end-0 top-0 m-3">
                    <span class="badge bg-{{ $bien->status == 'Disponible' ? 'success' : 'warning' }} py-2 px-3">
                        {{ $bien->status }}
                    </span>
                </div>

                <!-- Image du bien -->
                <div class="property-img-container">
                    <img src="{{ asset('storage/' . $bien->image) }}" 
                         class="card-img-top property-img" 
                         alt="{{ $bien->titre }}">
                    <div class="property-price-badge">
                        {{ number_format($bien->prix, 0, ',', ' ') }} FCFA/mois
                    </div>
                </div>

                <div class="card-body">
                    <h5 class="card-title text-primary">{{ $bien->type }}</h5>
                    <p class="text-muted mb-3">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                         {{ $bien->commune }}
                    </p>
                    
                    <div class="property-features d-flex flex-wrap gap-2 mb-3">
                        @if($bien->nombre_de_chambres)
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-bed me-1"></i> {{ $bien->nombre_de_chambres }} chambres
                        </span>
                        @endif
                        @if($bien->superficie)
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-ruler-combined me-1"></i> {{ $bien->superficie }} m²
                        </span>
                        @endif
                         @if($bien->nombre_de_toilettes)
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-bed me-1"></i> {{ $bien->nombre_de_toilettes }} Toillettes
                        </span>
                        @endif
                         @if($bien->garage)
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-bed me-1"></i> Garage: {{ $bien->garage }} 
                        </span>
                        @endif
                    </div>

                    <p class="card-text text-muted">{{ Str::limit($bien->description, 120) }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card shadow border-0 text-center py-5">
                <div class="card-body">
                    <i class="fas fa-home fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Aucun bien enregistré</h4>
                    <p class="text-muted mb-4">Vous n'avez pas encore ajouté de biens immobiliers</p>
                    
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($biens->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $biens->links() }}
    </div>
    @endif
</div>

<style>
    .text-gradient {
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-image: linear-gradient(310deg, #7928CA 0%, #02245b 100%);
    }
    
    .property-card {
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .property-img-container {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .property-img {
        height: 100%;
        width: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .property-card:hover .property-img {
        transform: scale(1.05);
    }
    
    .property-price-badge {
        position: absolute;
        bottom: 15px;
        left: 15px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
    }
    
    .property-features .badge {
        border-radius: 8px;
        font-weight: 500;
    }
    
    .card-footer {
        background-color: rgba(241, 243, 245, 0.5);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .pagination .page-link {
        color: #5e72e4;
        margin: 0 5px;
        border-radius: 8px;
    }
</style>
@endsection