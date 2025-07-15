@extends('agence.layouts.template')
@section('content')
<style>
    .owner-card {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 20px;
        overflow: hidden;
        border: none;
    }
    .owner-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    .owner-header {
        background-color: #02245b;
        color: white;
        padding: 15px;
        position: relative;
    }
    .owner-body {
        padding: 15px;
    }
    .owner-avatar {
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
    .owner-info {
        margin-bottom: 10px;
    }
    .owner-info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .owner-actions {
        border-top: 1px solid #eee;
        padding-top: 15px;
        margin-top: 15px;
    }
    .no-owners {
        text-align: center;
        padding: 50px;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    .property-count {
        font-weight: bold;
        color: #02245b;
    }
    .modal-property-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    .property-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }
    .property-status {
        font-size: 12px;
        padding: 3px 8px;
        border-radius: 20px;
        font-weight: bold;
    }
    .status-available {
        background-color: #28a745;
        color: white;
    }
    .status-occupied {
        background-color: #dc3545;
        color: white;
    }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mt-4 text-gray-800" style="text-align: center">Gestion des Propriétaires</h1>
    </div>
     <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un locataire...">
            </div>

    @if($proprietaires->isEmpty())
        <div class="no-owners">
            <i class="fas fa-user-tie fa-3x mb-3" style="color: #6c757d;"></i>
            <h4>Aucun propriétaire enregistré</h4>
            <p class="text-muted">Commencez par ajouter un nouveau propriétaire</p>
        </div>
    @else
        <div class="row">
            @foreach($proprietaires as $proprietaire)
                <!-- Modal pour les informations du propriétaire -->
                <div class="modal fade" id="ownerInfoModal{{ $proprietaire->id }}" tabindex="-1" role="dialog" aria-labelledby="ownerInfoModalLabel{{ $proprietaire->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ownerInfoModalLabel{{ $proprietaire->id }}">
                                    <i class="fas fa-user-tie"></i> Détails du propriétaire - {{ $proprietaire->prenom }} {{ $proprietaire->name }}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        @if($proprietaire->profil_image)
                                            <img src="{{ asset('storage/'.$proprietaire->profil_image) }}" class="owner-avatar" alt="Photo du propriétaire">
                                        @else
                                            <img src="{{ asset('assets/images/useriii.jpeg') }}" class="owner-avatar" alt="Avatar par défaut">
                                        @endif
                                        <h5 class="mt-2">{{ $proprietaire->prenom }} {{ $proprietaire->name }}</h5>
                                        <p class="text-muted">Code: {{ $proprietaire->code_id }}</p>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="owner-info">
                                                    <span class="owner-info-label">Email:</span>
                                                    <p>{{ $proprietaire->email }}</p>
                                                </div>
                                                
                                                <div class="owner-info">
                                                    <span class="owner-info-label">Téléphone:</span>
                                                    <p>{{ $proprietaire->contact }}</p>
                                                </div>
                                                
                                                <div class="owner-info">
                                                    <span class="owner-info-label">Commune:</span>
                                                    <p>{{ $proprietaire->commune }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="owner-info">
                                                    <span class="owner-info-label">Méthode de paiement:</span>
                                                    <p>{{ $proprietaire->choix_paiement }}</p>
                                                </div>
                                                
                                                @if($proprietaire->rib)
                                                <div class="owner-info">
                                                    <span class="owner-info-label">RIB:</span>
                                                    <p>{{ $proprietaire->rib }}</p>
                                                </div>
                                                @endif
                                                
                                                <div class="owner-info">
                                                    <span class="owner-info-label">Pourcentage:</span>
                                                    <p>{{ $proprietaire->pourcentage ?? 'Non spécifié' }}%</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($proprietaire->diaspora)
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-globe"></i> Ce propriétaire est de la diaspora
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal pour la liste des biens du propriétaire -->
                <div class="modal fade" id="ownerPropertiesModal{{ $proprietaire->id }}" tabindex="-1" role="dialog" aria-labelledby="ownerPropertiesModalLabel{{ $proprietaire->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ownerPropertiesModalLabel{{ $proprietaire->id }}">
                                    <i class="fas fa-home"></i> Biens du propriétaire - {{ $proprietaire->prenom }} {{ $proprietaire->name }}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-primary">
                                    <i class="fas fa-info-circle"></i> Ce propriétaire possède <strong>{{ $proprietaire->biens->count() }}</strong> biens enregistrés
                                </div>
                                
                                @if($proprietaire->biens->isNotEmpty())
                                    @foreach($proprietaire->biens as $bien)
                                        <div class="property-item">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    @if($bien->image)
                                                        <img src="{{ asset('storage/'.$bien->image) }}" class="img-thumbnail" alt="Image du bien">
                                                    @else
                                                        <img src="{{ asset('assets/images/default-property.jpg') }}" class="img-thumbnail" alt="Image par défaut">
                                                    @endif
                                                </div>
                                                <div class="col-md-9">
                                                    <h5>{{ $bien->type }} - {{ $bien->numero_bien }}</h5>
                                                    <p><i class="fas fa-map-marker-alt"></i> {{ $bien->commune }}</p>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><i class="fas fa-ruler-combined"></i> {{ $bien->superficie }} m²</p>
                                                            <p><i class="fas fa-bed"></i> {{ $bien->nombre_de_chambres ?? '0' }} chambres</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><i class="fas fa-money-bill-wave"></i> {{ number_format($bien->prix, 2, ',', ' ') }} FCFA/mois</p>
                                                            <p>
                                                                Statut: 
                                                                <span class="property-status {{ $bien->status == 'Disponible' ? 'status-available' : 'status-occupied' }}">
                                                                    {{ $bien->status }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-circle"></i> Aucun bien enregistré pour ce propriétaire
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte du propriétaire -->
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="card owner-card">
                        <div class="owner-header">
                            <h5 class="mb-0">Propriétaire : {{ $proprietaire->prenom }} {{ $proprietaire->name }} </h5>
                        </div>
                        
                        <div class="owner-body text-center">
                           
                            <div style="margin-top:50px ">
                                 @if($proprietaire->profil_image)
                                    <img src="{{ asset('storage/'.$proprietaire->profil_image) }}" class="owner-avatar" alt="Photo du propriétaire">
                                @else
                                    <img src="{{ asset('assets/images/useriii.jpeg') }}" class="owner-avatar" alt="Avatar par défaut">
                                @endif
                            </div>
                            
                            <div class="owner-info">
                                <span class="owner-info-label">Code du proprietaire</span>
                                <p>{{ $proprietaire->code_id }}</p>
                            </div>
                            <div class="owner-info">
                                <span class="owner-info-label">Email:</span>
                                <p>{{ $proprietaire->email }}</p>
                            </div>
                            
                            <div class="owner-info">
                                <span class="owner-info-label">Téléphone:</span>
                                <p>{{ $proprietaire->contact }}</p>
                            </div>
                            
                            <div class="owner-info">
                                <span class="owner-info-label">Nombre de biens:</span>
                                <p class="property-count">{{ $proprietaire->biens->count() }} biens</p>
                            </div>
                            
                            <!-- Boutons d'action -->
                            <div class="owner-actions d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#ownerInfoModal{{ $proprietaire->id }}">
                                        <i class="fas fa-info-circle"></i> Infos
                                    </button>
                                    
                                    <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#ownerPropertiesModal{{ $proprietaire->id }}">
                                        <i class="fas fa-home"></i> Biens
                                    </button>
                                </div>
                                
                                <div>
                                    <a href="{{ route('owner.edit', $proprietaire->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    
                                    <form action="{{ route('owner.destroy', $proprietaire->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm ml-2" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce propriétaire ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($proprietaires->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($proprietaires->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">«</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $proprietaires->previousPageUrl() }}" rel="prev" aria-label="Previous">«</a>
                                </li>
                            @endif

                            @foreach ($proprietaires->getUrlRange(1, $proprietaires->lastPage()) as $page => $url)
                                @if ($page == $proprietaires->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($proprietaires->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $proprietaires->nextPageUrl() }}" rel="next" aria-label="Next">»</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">»</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
                @endif
    @endif
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Script pour gérer les modales -->
<script>
    $(document).ready(function() {
        // Initialisation des modales Bootstrap
        $('.modal').modal({
            show: false
        });
    });
</script>

<script>
$(document).ready(function() {
    // Système de recherche en temps réel
    $('#searchInput').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        let hasResults = false;
        
        // Masquer d'abord le message "Aucun propriétaire" s'il est visible
        $('.no-owners').hide();
        
        // Parcourir toutes les cartes de propriétaire
        $('.col-lg-4.col-md-6.col-sm-12').each(function() {
            const cardText = $(this).text().toLowerCase();
            if (cardText.includes(searchText)) {
                $(this).show();
                hasResults = true;
            } else {
                $(this).hide();
            }
        });
        
        // Gérer le message "Aucun résultat"
        const noResultsMessage = $('.no-results-message');
        if (!hasResults && searchText.length > 0) {
            if (noResultsMessage.length === 0) {
                $('.row').append(`
                    <div class="col-12 no-results-message">
                        <div class="card shadow border-0 text-center py-5">
                            <div class="card-body">
                                <i class="fas fa-search fa-4x text-muted mb-4"></i>
                                <h5>Aucun résultat trouvé</h5>
                                <p class="text-muted mb-4">Aucun propriétaire ne correspond à votre recherche.</p>
                            </div>
                        </div>
                    </div>
                `);
            }
        } else {
            noResultsMessage.remove();
            // Si la recherche est vide, on montre tout
            if (searchText.length === 0) {
                $('.col-lg-4.col-md-6.col-sm-12').show();
                // On réaffiche le message "Aucun propriétaire" si c'est le cas
                if ($('.col-lg-4.col-md-6.col-sm-12:visible').length === 0) {
                    $('.no-owners').show();
                }
            }
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