<div class="container-fluid px-0 mb-5">
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @forelse($biens->take(4) as $index => $bien)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <div class="carousel-slide">
                        @if($bien->image)
                            <img class="carousel-img" src="{{ asset('storage/' . $bien->image) }}" alt="{{ $bien->type }}">
                        @else
                            <img class="carousel-img" src="{{ asset('assets/images/appart.jpg') }}" alt="Bien immobilier">
                        @endif
                        <div class="carousel-overlay"></div>
                        <div class="carousel-caption">
                            <div class="container">
                                <div class="row justify-content-start">
                                    <div class="col-lg-7">
                                        <p class="fs-5 fw-medium text-primary text-uppercase animated slideInRight">{{ $bien->type }}</p>
                                        <h1 class="display-4 text-white mb-4 animated slideInRight">{{ Str::limit($bien->description, 40) }}</h1>
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
                                        <a class="btn btn-primary py-3 px-5 animated slideInRight" href="{{ $route }}">
                                            Voir plus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="carousel-item active">
                    <div class="carousel-slide">
                        <img class="carousel-img" src="{{ asset('assets/images/appart.jpg') }}" alt="Bien immobilier par défaut">
                        <div class="carousel-overlay"></div>
                        <div class="carousel-caption">
                            <div class="container">
                                <div class="row justify-content-center">
                                    <div class="col-lg-8 text-center">
                                        <h1 class="display-4 text-white mb-4">Bienvenue chez Maelys-Imo</h1>
                                        <p class="fs-5 text-white mb-4">Votre partenaire immobilier de confiance</p>
                                    </div>
                                </div>
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

<style>
.carousel-slide {
    position: relative;
    height: 620px;
}

.carousel-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.carousel-overlay {
    display: none;
}

.carousel-caption {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    text-align: left;
    background: transparent;
}

.carousel-caption .container {
    background: rgba(2, 36, 91, 0.25);
    padding: 30px;
    border-radius: 10px;
    max-width: 500px;
}

@media (max-width: 768px) {
    .carousel-slide {
        height: 350px;
    }
    
    .carousel-caption h1 {
        font-size: 1.5rem;
    }
    
    .carousel-caption .container {
        padding: 20px;
        margin: 15px;
    }
}
</style>