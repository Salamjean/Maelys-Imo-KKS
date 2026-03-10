@extends('home.pages.layouts.template')

@section('content')

    <!-- Page Header Start -->
    <div class="container-fluid page-header py-5 mb-5 wow fadeIn position-relative" data-wow-delay="0.1s"
        style="background-image: url('{{ asset('assets/images/visite.jpg') }}'); background-size: cover; background-position: center;">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.6); z-index: 1;">
        </div>
        <div class="container py-5 position-relative" style="z-index: 2;">
            <h1 class="display-3 text-white animated slideInRight">Détails du bien</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb animated slideInRight mb-0">
                    <li class="breadcrumb-item"><a href="/" class="text-white">Accueil</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">{{ $bien->type }} à
                        {{ $bien->commune }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Section Visuelle (3D ou Image Principale) -->
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                        @if($bien->video_3d)
                            <div class="video-3d-wrapper" style="position: relative; height: 500px; background: #000; overflow: hidden;">
                                @php $embedUrl = $bien->getVideo3dEmbedUrl(); @endphp
                                @if(str_contains($embedUrl, '<iframe'))
                                    <div class="raw-iframe-container">
                                        {!! $embedUrl !!}
                                    </div>
                                @else
                                    <iframe src="{{ $embedUrl }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                                        allowfullscreen></iframe>
                                @endif
                            </div>
                        @else
                            <img src="{{ $bien->image ? asset('storage/' . $bien->image) : asset('home/img/default-property.jpg') }}"
                                class="img-fluid w-100" style="object-fit: cover; max-height: 500px;" alt="{{ $bien->type }}">
                        @endif
                    </div>

                    <!-- Galerie d'images -->
                    <div class="row g-3">
                        @php
                            $galerie = array_filter([$bien->image, $bien->image1, $bien->image2, $bien->image3, $bien->image4, $bien->image5]);
                        @endphp
                        @foreach($galerie as $img)
                            <div class="col-md-4 col-6">
                                <div class="gallery-item rounded overflow-hidden shadow-sm" style="cursor: pointer;">
                                    <img src="{{ asset('storage/' . $img) }}" class="img-fluid w-100 preview-image"
                                        style="height: 150px; object-fit: cover;" data-image="{{ asset('storage/' . $img) }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Section Informations -->
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                        <div class="card-header bg-primary text-white py-3">
                            <h4 class="mb-0 text-white">{{ $bien->type }} à {{ $bien->commune }}</h4>
                        </div>
                        <div class="card-body">
                            <h5 class="text-primary mb-3">Prix: {{ number_format($bien->prix, 0, ',', ' ') }} FCFA</h5>
                            <p class="mb-4">{{ $bien->description }}</p>

                            <div class="border-top pt-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <p class="mb-2"><i
                                                class="fa fa-ruler-combined text-primary me-2"></i><strong>Superficie:</strong>
                                            {{ $bien->superficie }} m²</p>
                                        <p class="mb-2"><i
                                                class="fa fa-bed text-primary me-2"></i><strong>Chambres:</strong>
                                            {{ $bien->nombre_de_chambres }}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-2"><i
                                                class="fa fa-bath text-primary me-2"></i><strong>Toilettes:</strong>
                                            {{ $bien->nombre_de_toilettes }}</p>
                                        <p class="mb-2"><i class="fa fa-car text-primary me-2"></i><strong>Garage:</strong>
                                            {{ $bien->garage ?: 'Non' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-light p-3 mt-4 rounded">
                                <h6 class="text-primary mb-3">Détails Financiers</h6>
                                @if($bien->avance)
                                    <p class="mb-1"><strong>Avance:</strong> {{ number_format($bien->avance, 0, ',', ' ') }}
                                        Mois</p>
                                @endif
                                @if($bien->caution)
                                    <p class="mb-1"><strong>Caution:</strong> {{ number_format($bien->caution, 0, ',', ' ') }}
                                        Mois</p>
                                @endif
                                <p class="mb-0"><strong>Frais d'agence:</strong>
                                    {{ number_format($bien->frais, 0, ',', ' ') }} Mois</p>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('bien.visiter', $bien->id) }}" class="btn btn-primary w-100 py-3 mb-2">
                                    <i class="fa fa-calendar-check me-2"></i>Prendre rendez-vous
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Informations sur l'agence/propriétaire -->
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">Contact Immobilier</h6>
                            @if($bien->agence_id)
                                <p class="mb-1"><strong>Agence:</strong> {{ $bien->agence->name ?? 'Maelys-Imo' }}</p>
                                <p class="mb-0"><strong>Téléphone:</strong>
                                    {{ $bien->agence->contact ?? '+225 27 22 36 50 27' }}</p>
                            @elseif($bien->proprietaire_id)
                                <p class="mb-1"><strong>Propriétaire:</strong> {{ $bien->proprietaire->name }}
                                    {{ $bien->proprietaire->prenom }}</p>
                                <p class="mb-0"><strong>Téléphone:</strong> {{ $bien->proprietaire->contact }}</p>
                            @else
                                <p class="mb-1"><strong>Gestion:</strong> Maelys-Imo</p>
                                <p class="mb-0"><strong>Téléphone:</strong> +225 27 22 36 50 27</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les images -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center p-0">
                    <img id="galleryModalImage" src="" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </div>

    <style>
        .gallery-item:hover {
            opacity: 0.8;
            transition: 0.3s;
        }

        .video-3d-wrapper iframe, .video-3d-wrapper .raw-iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100% !important;
        }
    </style>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.preview-image').on('click', function () {
                const imgUrl = $(this).data('image');
                $('#galleryModalImage').attr('src', imgUrl);
                $('#galleryModal').modal('show');
            });
        });
    </script>
@endpush