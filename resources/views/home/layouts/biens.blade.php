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
                        @elseif($bien->proprietaire_id)
                            @if($bien->proprietaire->gestion == 'agence')
                                <i class="fa fa-home text-primary me-2"></i>Agence : Maelys-imo 
                            @else
                                <i class="fa fa-user text-primary me-2"></i>Propriétaire : {{ $bien->proprietaire->name.' '.$bien->proprietaire->prenom ?? 'Maelys-imo' }}
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
                        @php
                            $route = '#'; // Valeur par défaut
                            if ($bien->type === 'Bureau') {
                                $route = route('bien.terrain', $bien->id);
                            } elseif ($bien->type === 'Appartement') {
                                $route = route('bien.appartement', $bien->id);
                            }elseif ($bien->type === 'Maison') {
                                $route = route('bien.maison', $bien->id);
                            }
                        @endphp
                    
                        <a class="btn btn-primary px-4" href="{{ $route }}">
                            Voir plus
                        </a>
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