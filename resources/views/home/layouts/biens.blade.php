<div class="container-xxl pt-3 pb-5">
    <div class="container">
        <!-- Barre de recherche simple -->
        <div class="bg-light p-4 rounded-pill mb-5">
            <form action="/" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <select class="form-select py-3 rounded-pill border-0 shadow-sm" name="type">
                            <option value="">Tous les types</option>
                            <option value="Appartement" {{ request('type') == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                            <option value="Maison" {{ request('type') == 'Maison' ? 'selected' : '' }}>Maison</option>
                            <option value="Bureau" {{ request('type') == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control py-3 rounded-pill border-0 shadow-sm" placeholder="Commune..." name="commune" value="{{ request('commune') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control py-3 rounded-pill border-0 shadow-sm" placeholder="Prix max (FCFA)" name="prix_max" value="{{ request('prix_max') }}">
                    </div>
                    <div class="col-md-1 d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary rounded-circle" style="width: 50px; height: 50px;">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            @if(request()->hasAny(['type', 'commune', 'prix_max']))
            <div class="text-center mt-3">
                <a href="/" class="btn btn-outline-primary btn-sm rounded-pill">
                    <i class="fa fa-times me-1"></i> Réinitialiser les filtres
                </a>
            </div>
            @endif
        </div>

        <!-- Titre de section -->
        <div class="text-center mb-5">
            <h6 class="text-primary text-uppercase">Nos Biens Disponibles</h6>
            <h2>Découvrez Notre Sélection</h2>
        </div>

        <!-- Liste des biens -->
        <div class="row g-4">
            @forelse($biens as $bien)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="property-item rounded overflow-hidden shadow-sm">
                    <div class="position-relative">
                        @if($bien->image)
                        <img class="img-fluid w-100" src="{{ asset('storage/'.$bien->image) }}" alt="{{ $bien->type }}" style="height: 220px; object-fit: cover;">
                        @else
                        <img class="img-fluid w-100" src="{{ asset('home/img/default-property.jpg') }}" alt="Image par défaut" style="height: 220px; object-fit: cover;">
                        @endif
                        <div class="bg-primary text-white position-absolute start-0 top-0 m-3 py-1 px-3 rounded">
                            {{ $bien->type }}
                        </div>
                        <div class="bg-white position-absolute start-0 bottom-0 mx-3 mb-3 py-1 px-3 rounded" style="color: #02245b; font-weight: bold;">
                            {{ number_format($bien->prix, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="p-4 bg-white">
                        <h5 class="text-primary mb-2">{{ $bien->commune }}</h5>
                        <p class="mb-2">{{ $bien->type }}</p>
                        <p class="text-muted small mb-3">
                            @if($bien->agence_id)
                                <i class="fa fa-building me-1"></i> {{ $bien->agence->name ?? 'Maelys-Imo' }}
                            @elseif($bien->proprietaire_id && $bien->proprietaire)
                                @if(optional($bien->proprietaire)->gestion == 'agence')
                                    <i class="fa fa-building me-1"></i> Maelys-imo
                                @else
                                    <i class="fa fa-user me-1"></i> {{ optional($bien->proprietaire)->name.' '.optional($bien->proprietaire)->prenom ?? 'Maelys-imo' }}
                                @endif
                            @else
                                <i class="fa fa-building me-1"></i> Maelys-imo
                            @endif
                        </p>
                        <div class="d-flex justify-content-between border-top pt-3">
                            <small><i class="fa fa-ruler-combined text-primary me-1"></i>{{ $bien->superficie }} m²</small>
                            <small><i class="fa fa-bed text-primary me-1"></i>{{ $bien->nombre_de_chambres }} Ch.</small>
                            <small><i class="fa fa-bath text-primary me-1"></i>{{ $bien->nombre_de_toilettes }} SdB</small>
                        </div>
                        <button class="btn btn-primary w-100 mt-3 view-details-btn"
                                data-bien-id="{{ $bien->id }}"
                                data-bien-type="{{ $bien->type }}"
                                data-bien-commune="{{ $bien->commune }}"
                                data-bien-description="{{ $bien->description }}"
                                data-bien-superficie="{{ $bien->superficie }}"
                                data-bien-chambres="{{ $bien->nombre_de_chambres }}"
                                data-bien-toilettes="{{ $bien->nombre_de_toilettes }}"
                                data-bien-garage="{{ $bien->garage }}"
                                data-bien-prix="{{ $bien->prix }}"
                                data-bien-avance="{{ $bien->avance }}"
                                data-bien-caution="{{ $bien->caution }}"
                                data-bien-frais="{{ $bien->frais }}"
                                data-bien-agence="{{ 
                                    $bien->agence_id 
                                        ? ($bien->agence->name ?? 'Maelys-imo') 
                                        : ($bien->proprietaire_id 
                                            ? ($bien->proprietaire->gestion == 'agence' 
                                                ? 'Maelys-imo' 
                                                : ($bien->proprietaire->name.' '.$bien->proprietaire->prenom ?? 'Maelys-imo'))
                                            : 'Maelys-imo')
                                }}"
                                data-bien-contact="{{ $bien->agence->contact ?? '+225 0798278981' }}"
                                data-bien-date="{{ $bien->date_fixe }}"
                                data-bien-image="{{ $bien->image ? asset('storage/'.$bien->image) : asset('home/img/default-property.jpg') }}"
                                data-bien-image1="{{ $bien->image1 ? asset('storage/'.$bien->image1) : asset('home/img/default-property-2.jpg') }}">
                            Voir détails
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <div class="alert alert-info">
                    Aucun bien immobilier disponible pour le moment.
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal pour les images -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visualisation de l'image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<style>
.property-item {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.property-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
}
</style>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
new WOW().init();
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.property-item img');
    images.forEach(img => {
        img.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('imageModal'));
            document.getElementById('modalImage').src = this.src;
            modal.show();
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $(document).on('click', '.view-details-btn', function() {
        const bien = {
            id: $(this).data('bien-id'),
            type: $(this).data('bien-type'),
            commune: $(this).data('bien-commune'),
            description: $(this).data('bien-description'),
            superficie: $(this).data('bien-superficie'),
            chambres: $(this).data('bien-chambres'),
            toilettes: $(this).data('bien-toilettes'),
            garage: $(this).data('bien-garage'),
            prix: $(this).data('bien-prix'),
            avance: $(this).data('bien-avance'),
            caution: $(this).data('bien-caution'),
            frais: $(this).data('bien-frais'),
            agence: $(this).data('bien-agence'),
            contact: $(this).data('bien-contact'),
            date: $(this).data('bien-date'),
            image: $(this).data('bien-image'),
            image1: $(this).data('bien-image1')
        };

        const formatPrix = (prix) => {
            return new Intl.NumberFormat('fr-FR').format(prix) + ' FCFA';
        };

        const htmlContent = `
            <div class="text-start">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img src="${bien.image}" class="img-fluid rounded" style="height: 180px; width: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <img src="${bien.image1}" class="img-fluid rounded" style="height: 180px; width: 100%; object-fit: cover;">
                    </div>
                </div>
                
                <h4 class="text-primary mb-3">${bien.type} à ${bien.commune}</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><i class="fa fa-ruler-combined text-primary me-2"></i> Superficie: <strong>${bien.superficie} m²</strong></p>
                        <p><i class="fa fa-bed text-primary me-2"></i> Chambre: <strong>${bien.chambres}</strong></p>
                        <p><i class="fa fa-bath text-primary me-2"></i> Toilette: <strong>${bien.toilettes}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <p><i class="fa fa-car text-primary me-2"></i> Garage: <strong>${bien.garage ?? 'Non'}</strong></p>
                        <p><i class="fa fa-calendar-alt text-primary me-2"></i> Date fixe: <strong>${bien.date} du mois</strong></p>
                        <p><i class="fa fa-money-bill-wave text-primary me-2"></i> Loyer: <strong>${formatPrix(bien.prix)}</strong></p>
                        <p><i class="fa fa-home text-primary me-2"></i> Agence: <strong>${bien.agence}</strong></p>
                        <p><i class="fa fa-phone text-primary me-2"></i> Contact: <strong>${bien.contact}</strong></p>
                    </div>
                </div>
                
                ${bien.avance || bien.caution || bien.frais ? `
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-primary">Conditions:</h6>
                            ${bien.avance ? `<p class="mb-1"><small>Avance: <strong>${bien.avance} Mois</strong></small></p>` : ''}
                            ${bien.caution ? `<p class="mb-1"><small>Caution: <strong>${bien.caution} Mois</strong></small></p>` : ''}
                            ${bien.frais ? `<p class="mb-0"><small>Frais d'agence: <strong>${bien.frais} Mois</strong></small></p>` : ''}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <h6 class="text-primary">Description:</h6>
                            <p class="mb-0"><small>${bien.description}</small></p>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        Swal.fire({
            title: 'Détails du bien',
            html: htmlContent,
            width: '750px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Fermer',
            confirmButtonColor: '#02245b',
            showDenyButton: true,
            denyButtonText: 'Visiter',
            denyButtonColor: '#28a745',
            buttonsStyling: true
        }).then((result) => {
            if (result.isDenied) {
                window.location.href = `/visiter-bien/${bien.id}`;
            }
        });
    });
});
</script>