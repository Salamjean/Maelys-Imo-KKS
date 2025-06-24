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
                    <p>
                         <form action="{{ route('bien.republier.owner', $bien->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success" title="Republier ce bien">
                                        <i class="mdi mdi-replay"></i> Republier
                                    </button>
                            </form>
                    </p>
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
<!-- Scripts nécessaires -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Notification SweetAlert2 modifiée pour ressembler à la confirmation
        @if(session('success'))
        Swal.fire({
            title: 'Succès !',
            text: '{{ session('success') }}',
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK',
            allowOutsideClick: false
        });
        @endif
    
        // Gestion des images
        $('.preview-image').on('click', function() {
            const imgUrl = $(this).data('image');
            $('#modalImage').attr('src', imgUrl);
            $('#imageModal').modal('show');
        });
    
        // Confirmation de suppression (inchangé)
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Confirmer la suppression',
                text: "Êtes-vous sûr de vouloir supprimer ce bien ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer!',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

   $('form[action*="republier"]').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    
    Swal.fire({
        title: 'Confirmer la republication',
        text: "Êtes-vous sûr de vouloir rendre ce bien disponible à nouveau ?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, republier!',
        cancelButtonText: 'Annuler',
        html: `
            <div class="form-group mt-3">
                <label for="locataireStatus">Statut du locataire :</label>
                <select class="form-control" id="locataireStatus" required>
                    <option value="">Sélectionnez un statut</option>
                    <option value="Inactif">Déménagement</option>
                    <option value="Pas sérieux">Pas sérieux</option>
                </select>
            </div>
            <div class="form-group mt-2" id="motifGroup" style="display: none;">
                <label for="motif">Motif :</label>
                <input type="text" class="form-control" id="motif" placeholder="Raison du changement de statut">
            </div>
        `,
        preConfirm: () => {
            const status = document.getElementById('locataireStatus').value;
            const motif = document.getElementById('motif')?.value;
            
            if (!status) {
                Swal.showValidationMessage('Veuillez sélectionner un statut');
                return false;
            }
            
            if ((status === 'Pas sérieux') && !motif) {
                Swal.showValidationMessage('Veuillez indiquer un motif');
                return false;
            }
            
            return { status, motif: motif || '' };
        },
        didOpen: () => {
            const statusSelect = document.getElementById('locataireStatus');
            const motifGroup = document.getElementById('motifGroup');
            
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Pas sérieux') {
                    motifGroup.style.display = 'block';
                } else {
                    motifGroup.style.display = 'none';
                }
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Ajouter les données au formulaire
            const hiddenStatus = document.createElement('input');
            hiddenStatus.type = 'hidden';
            hiddenStatus.name = 'locataire_status';
            hiddenStatus.value = result.value.status;
            form.append(hiddenStatus);
            
            if (result.value.motif) {
                const hiddenMotif = document.createElement('input');
                hiddenMotif.type = 'hidden';
                hiddenMotif.name = 'locataire_motif';
                hiddenMotif.value = result.value.motif;
                form.append(hiddenMotif);
            }
            
            form.unbind('submit').submit();
        }
    });
});
    </script>
@endsection