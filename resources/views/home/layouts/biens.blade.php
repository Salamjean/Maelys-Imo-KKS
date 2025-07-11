<div class="position-relative w-75 mx-auto animated fadeInUp">
    <form action="/" method="GET">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select border-2 rounded-pill py-3 ps-4 border-blue-900" name="type">
                    <option value="">Tous les types</option>
                    <option value="Appartement" {{ request('type') == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                    <option value="Maison" {{ request('type') == 'Maison' ? 'selected' : '' }}>Maison</option>
                    <option value="Bureau" {{ request('type') == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control border-2 rounded-pill py-3 ps-4 border-blue-900" 
                       placeholder="Commune..." name="commune" value="{{ request('commune') }}">
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="number" class="form-control border-2 rounded-pill py-3 ps-4 border-blue-900" 
                           placeholder="Prix max (FCFA)" name="prix_max" value="{{ request('prix_max') }}">
                    <button type="submit" class="btn btn-primary rounded-pill py-3 px-4">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>
    @if(request()->hasAny(['type', 'commune', 'prix_max']))
    <div class="text-center mt-3">
        <a href="/" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-times me-1"></i> Réinitialiser les filtres
        </a>
    </div>
    @endif
</div>
<style>
    .form-select, .form-control {
        height: auto;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
    }
</style>

<!-- Property Listing Section -->
<div class="container-xxl py-5">
    <div class="container px-lg-5">
        <div class="section-title position-relative text-center mb-5 pb-2 wow fadeInUp" data-wow-delay="0.1s">
            <h6 class="position-relative d-inline text-primary ps-4">Nos Biens Disponibles</h6>
            <h2 class="mt-2">Découvrez Notre Sélection</h2>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($biens as $bien)
            <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s" >
                <div class="property-item rounded overflow-hidden" style="background-color: #f8f9fae4;">
                    <div class="position-relative overflow-hidden">
                        @if($bien->image)
                        <img class="img-fluid" src="{{ asset('storage/'.$bien->image) }}" alt="{{ $bien->type }}" style="height: 250px; width: 100%; object-fit: cover;">
                        @else
                        <img class="img-fluid" src="{{ asset('home/img/default-property.jpg') }}" alt="Image par défaut" style="height: 250px; width: 100%; object-fit: cover;">
                        @endif
                        <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-4 py-1 px-3">
                            {{ $bien->type }}
                        </div>
                        <div class="bg-white rounded-top position-absolute start-0 bottom-0 mx-4 pt-1 px-3" style="color: #02245b; font-size: 20px;">
                            {{ number_format($bien->prix, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                    <div class="p-4 pb-0">
                        <h5 class="text-primary mb-3">{{ $bien->commune }}</h5>
                        <p class="d-block h5 mb-2">{{ $bien->type }}</p>
                        <p><i class="fa fa-map-marker-alt text-primary me-2"></i>{{ $bien->commune }}</p>
                      <p>
                        @if($bien->agence_id)
                            <i class="fa fa-home text-primary me-2"></i> Agence : {{ $bien->agence->name ?? 'Maelys-Imo' }}
                        @elseif($bien->proprietaire_id && $bien->proprietaire)
                            @if(optional($bien->proprietaire)->gestion == 'agence')
                                <i class="fa fa-home text-primary me-2"></i>Agence : Maelys-imo 
                            @else
                                <i class="fa fa-user text-primary me-2"></i>Propriétaire : {{ optional($bien->proprietaire)->name.' '.optional($bien->proprietaire)->prenom ?? 'Maelys-imo' }}
                            @endif
                        @else
                            <i class="fa fa-home text-primary me-2"></i>Agence : Maelys-imo
                        @endif
                    </p>
                    </div>
                    <div class="d-flex border-top">
                        <small class="flex-fill text-center border-end border-start py-2">
                            <i class="fa fa-ruler-combined text-primary me-2"></i>{{ $bien->superficie }} m²
                        </small>
                        <small class="flex-fill text-center border-end py-2">
                            <i class="fa fa-bed text-primary me-2"></i>{{ $bien->nombre_de_chambres }} Chambres
                        </small>
                        <small class="flex-fill text-center border-end py-2">
                            <i class="fa fa-bath text-primary me-2"></i>{{ $bien->nombre_de_toilettes }} Toilettes
                        </small>
                    </div>
                     <div class="d-flex justify-content-center p-4">
                            <button class="btn btn-primary px-4 view-details-btn" 
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>
// Initialisation des animations
new WOW().init();
// Gestion du zoom des images
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
    // Gestion du clic sur "Voir détails"
    $(document).on('click', '.view-details-btn', function() {
        // Récupération des données
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

        // Formatage du prix
        const formatPrix = (prix) => {
            return new Intl.NumberFormat('fr-FR').format(prix) + ' FCFA';
        };

        // Construction du contenu HTML
        const htmlContent = `
            <div class="text-start">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img src="${bien.image}" class="img-fluid rounded mb-2" style="max-height: 200px; width: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <img src="${bien.image1}" class="img-fluid rounded mb-2" style="max-height: 200px; width: 100%; object-fit: cover;">
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
                        <p><i class="fa fa-calendar-alt text-primary me-2"></i> Date fixe de loyer: <strong>${bien.date} de chaque mois</strong> </p>
                        <p><i class="fa fa-money-bill-wave text-primary me-2"></i> Loyer mensuel: <strong>${formatPrix(bien.prix)}</strong></p>
                        <p><i class="fa fa-home text-primary me-2"></i> Agence/Propriétaire: <strong>${bien.agence}</strong></p>
                        <p><i class="fa fa-phone text-primary me-2"></i> Contact: <strong>${bien.contact}</strong></p>
                    </div>
                </div>
                
                <div class="row">
                    ${bien.avance || bien.caution || bien.frais ? `
                    <div class="alert alert-light mt-3 col-6">
                        <h6 class="text-primary">Conditions:</h6>
                        ${bien.avance ? `<p><i class="fa fa-hand-holding-usd text-primary me-2"></i> Avance: <strong>${(bien.avance)} Mois</strong></p>` : ''}
                        ${bien.caution ? `<p><i class="fa fa-lock text-primary me-2"></i> Caution: <strong>${(bien.caution)} Mois</strong></p>` : ''}
                        ${bien.frais ? `<p><i class="fa fa-lock text-primary me-2"></i> frais d'agence: <strong>${(bien.frais)} Mois</strong></p>` : ''}
                    </div>
                    <div class="alert alert-light mt-3 col-6">
                        <h6 class="text-primary">Description:</h6>
                        <p>${(bien.description)}</p>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        // Affichage de la popup
        Swal.fire({
            title: 'Descriptions du bien',
            html: htmlContent,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Fermer',
            confirmButtonColor: '#02245b',
            showDenyButton: true,
            denyButtonText: 'Visiter',
            denyButtonColor: '#28a745',
            customClass: {
                popup: 'rounded-lg',
                actions: 'my-actions'
            },
            buttonsStyling: true
        }).then((result) => {
            if (result.isDenied) {
                // Redirection vers la route Laravel pour la visite
                window.location.href = `/visiter-bien/${bien.id}`;
            }
        });
    });
});
</script>