<div class="container-fluid px-0 mb-5" style="height: 900px;">
    <div id="header-carousel" class="carousel slide h-100" data-bs-ride="carousel">
        <div class="carousel-inner h-100">
            @forelse($biens->take(2) as $index => $bien)
                <div class="carousel-item h-100 {{ $index === 0 ? 'active' : '' }}">
                    @if($bien->image)
                        <img class="w-100 h-100 object-fit-cover" src="{{ asset('storage/' . $bien->image) }}" alt="{{ $bien->type }}">
                    @else
                        <div class="w-100 h-100 bg-dark position-relative">
                            <div class="h-100 d-flex align-items-center justify-content-center">
                                <div class="text-center text-white p-5" style="background-color: rgba(0,0,0,0.5);">
                                    <h1 class="display-4 mb-3">Bien Immobilier de Prestige</h1>
                                    <p class="fs-5">Découvrez notre sélection exclusive</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-10 text-start">
                                    <p class="fs-5 fw-medium text-primary text-uppercase animated slideInRight">{{ $bien->type }}</p>
                                    <h1 class="display-4 text-white mb-3 animated slideInRight">{{ Str::limit($bien->description, 25) }}</h1>
                                    @php
                                        $route = '#';
                                        if ($bien->type === 'Bureau') {
                                            $route = route('bien.terrain', $bien->id);
                                        } elseif ($bien->type === 'Appartement') {
                                            $route = route('bien.appartement', $bien->id);
                                        } elseif ($bien->type === 'Maison') {
                                            $route = route('bien.maison', $bien->id);
                                        }
                                    @endphp
                                    <a class="btn btn-primary px-4" href="{{ $route }}">
                                        Voir plus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="carousel-item h-100 active">
                    <div class="w-100 h-100 position-relative">
                        <!-- Image de fond par défaut -->
                        <img class="w-100 h-100 object-fit-cover position-absolute" 
                            src="{{ asset('assets/images/appart.jpg') }}" 
                            alt="Bien immobilier par défaut"
                            style="z-index: 1;">
                        
                        <!-- Overlay sombre pour améliorer la lisibilité du texte -->
                        <div class="position-absolute w-100 h-100" 
                            style="background-color: rgba(0,0,0,0.3); z-index: 2;"></div>
                        
                        <!-- Contenu texte centré -->
                        <div class="h-100 d-flex align-items-center justify-content-center position-relative" style="z-index: 3;">
                            <div class="text-center text-white p-5">
                                <h1 class="display-4 mb-3">Aucun bien disponible</h1>
                                <p class="fs-5 mb-4">Nous n'avons pas encore de biens à afficher</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        @if($biens->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
</div>