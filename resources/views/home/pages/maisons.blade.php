@extends('home.pages.layouts.template')

@section('content')
    <!-- Page Header Start -->
    <div class="container-fluid page-header py-5 mb-5 wow fadeIn position-relative" data-wow-delay="0.1s"
        style="background-image: url('{{ asset('assets/images/maison.jpg') }}'); background-size: cover; background-position: center;">

        <!-- Overlay noir -->
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.6); z-index: 1;">
        </div>

        <!-- Contenu avec position relative pour passer au-dessus -->
        <div class="container py-5 position-relative" style="z-index: 2;">
            <h1 class="display-3 text-white animated slideInRight">Maisons</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb animated slideInRight mb-0">
                    <li class="breadcrumb-item"><a href="/" class="text-white">Accueil</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Maison</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="position-relative w-75 mx-auto animated fadeInUp">
        <form action="{{ route('bien.maison') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control border-2 rounded-pill py-3 ps-4 border-blue-900"
                        placeholder="Commune..." name="commune" value="{{ request('commune') }}">
                </div>
                <div class="col-md-6">
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
                <a href="{{ route('bien.terrain') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-times me-1"></i> Réinitialiser les filtres
                </a>
            </div>
        @endif
    </div>
    <style>
        .form-select,
        .form-control {
            height: auto;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }
    </style>

    <div class="container-xxl py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h6 class="text-primary text-uppercase">Maisons Disponibles</h6>
                <h2>Toutes les maisons disponibles</h2>
            </div>

            <div class="row g-4">
                @forelse($biens as $bien)
                    <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="property-item rounded overflow-hidden shadow-sm bg-white h-100 d-flex flex-column">
                            <div class="position-relative overflow-hidden">
                                @if($bien->video_3d)
                                    <div class="video-3d-thumbnail" style="height: 220px; background: #000;">
                                        @php $embedUrl = $bien->getVideo3dEmbedUrl(); @endphp
                                        @if(str_contains($embedUrl, '<iframe'))
                                            {!! $embedUrl !!}
                                        @else
                                            <iframe src="{{ $embedUrl }}" style="width: 100%; height: 100%; border: 0;"
                                                allowfullscreen></iframe>
                                        @endif
                                    </div>
                                @else
                                    <a href="{{ route('bien.details', $bien->id) }}">
                                        <img class="img-fluid"
                                            src="{{ $bien->image ? asset('storage/' . $bien->image) : asset('home/img/default-property.jpg') }}"
                                            alt="{{ $bien->type }}" style="height: 220px; width: 100%; object-fit: cover;">
                                    </a>
                                @endif
                                <div class="bg-primary rounded text-white position-absolute start-0 top-0 m-3 py-1 px-3">
                                    {{ $bien->type }}
                                </div>
                                <div
                                    class="bg-white rounded-top position-absolute start-0 bottom-0 mx-3 pt-1 px-3 text-primary fw-bold">
                                    {{ number_format($bien->prix, 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            <div class="p-4 pb-0 flex-grow-1">
                                <h5 class="text-primary mb-2">{{ Str::limit($bien->commune, 20) }}</h5>
                                <p class="text-muted small mb-2">
                                    <i class="fa fa-map-marker-alt text-primary me-2"></i>{{ $bien->commune }}
                                </p>
                                <p class="text-muted small mb-3">
                                    <i class="fa fa-user text-primary me-2"></i>
                                    @if($bien->agence_id)
                                        {{ $bien->agence->name ?? 'Maelys-Imo' }}
                                    @else
                                        {{ $bien->proprietaire->name ?? 'Maelys-Imo' }}
                                    @endif
                                </p>
                            </div>
                            <div class="d-flex border-top mt-auto">
                                <small class="flex-fill text-center border-end py-2">
                                    <i class="fa fa-ruler-combined text-primary me-2"></i>{{ $bien->superficie }} m²
                                </small>
                                <small class="flex-fill text-center border-end py-2">
                                    <i class="fa fa-bed text-primary me-2"></i>{{ $bien->nombre_de_chambres }}
                                </small>
                                <small class="flex-fill text-center py-2">
                                    <i class="fa fa-bath text-primary me-2"></i>{{ $bien->nombre_de_toilettes }}
                                </small>
                            </div>
                            <div class="p-3">
                                <a href="{{ route('bien.details', $bien->id) }}" class="btn btn-primary w-100 py-2">Voir
                                    plus</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            Aucune maison disponible pour le moment.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .property-item {
            transition: .5s;
        }

        .property-item:hover {
            margin-top: -10px;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, .1) !important;
        }

        .video-3d-thumbnail iframe {
            width: 100%;
            height: 100% !important;
        }
    </style>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            new WOW().init();
        });
    </script>
@endpush