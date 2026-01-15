<div class="container-fluid px-0 mb-5">
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @forelse($biens->take(4) as $index => $bien)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" style="height: 700px;">
                    @if($bien->image)
                        <img class="w-100 h-100" src="{{ asset('storage/' . $bien->image) }}" alt="{{ $bien->type }}" style="object-fit: cover;">
                    @else
                        <img class="w-100 h-100" src="{{ asset('assets/images/appart.jpg') }}" alt="Bien immobilier" style="object-fit: cover;">
                    @endif
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="row justify-content-start">
                                <div class="col-lg-7 text-start">
                                    <p class="fs-4 text-primary animated slideInRight">{{ $bien->type }}</p>
                                    <h1 class="display-4 text-white mb-5 animated slideInRight">{{ Str::limit($bien->description, 25) ?? 'Bienvenue' }}</h1>
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
            @empty
                <div class="carousel-item active" style="height: 700px;">
                    <img class="w-100 h-100" src="{{ asset('assets/images/appart.jpg') }}" alt="Bien immobilier par défaut" style="object-fit: cover;">
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-lg-8 text-center">
                                    <p class="fs-4 text-primary animated slideInRight">Bienvenue</p>
                                    <h1 class="display-1 text-white mb-5 animated slideInRight">Maelys-Imo, votre partenaire immobilier</h1>
                                    <a class="btn btn-primary py-3 px-5 animated slideInRight" href="/contact">
                                        Contactez-nous
                                    </a>
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