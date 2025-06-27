@extends('home.pages.layouts.template')

@section('content')

<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn position-relative"
     data-wow-delay="0.1s"
     style="background-image: url('{{ asset('assets/images/visite.jpg') }}'); background-size: cover; background-position: center;">
    
    <!-- Overlay noir -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.6); z-index: 1;"></div>

    <!-- Contenu avec position relative pour passer au-dessus -->
    <div class="container py-5 position-relative" style="z-index: 2;">
        <h1 class="display-3 text-white animated slideInRight">Visite du bien</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb animated slideInRight mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-white">Accueil</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><a href="{{ route('bien.appartement') }}">Appartement</a></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container-xxl py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Section Informations du bien -->
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <img src="{{ $bien->image ? asset('storage/'.$bien->image) : asset('home/img/default-property.jpg') }}" 
                                 class="img-fluid h-100" 
                                 style="object-fit: cover; min-height: 300px;" 
                                 alt="{{ $bien->type }}">
                        </div>
                        <div class="col-md-6">
                            <div class="card-body">
                                <h3 class="text-primary">{{ $bien->type }} à {{ $bien->commune }}</h3>
                                <p class="mb-3">{{ $bien->description }}</p>
                                
                                <div class="d-flex justify-content-between border-top pt-3">
                                    <div>
                                        <p><i class="fa fa-ruler-combined text-primary me-2"></i> {{ $bien->superficie }} m²</p>
                                        <p><i class="fa fa-bed text-primary me-2"></i> {{ $bien->nombre_de_chambres }} Chambres</p>
                                    </div>
                                    <div>
                                        <p><i class="fa fa-bath text-primary me-2"></i> {{ $bien->nombre_de_toilettes }} Toilettes</p>
                                        <p><i class="fa fa-car text-primary me-2"></i> {{ $bien->garage ? 'Oui' : 'Non' }}</p>
                                    </div>
                                </div>
                                
                                <div class="bg-light p-3 mt-3 rounded">
                                    <h5 class="text-primary">Conditions</h5>
                                    <p class="mb-1"><strong>Loyer mensuel:</strong> {{ number_format($bien->prix, 0, ',', ' ') }} FCFA</p>
                                    @if($bien->avance)
                                        <p class="mb-1"><strong>Avance:</strong> {{ number_format($bien->avance, 0, ',', ' ') }} Mois</p>
                                    @endif
                                    @if($bien->caution)
                                        <p class="mb-1"><strong>Caution:</strong> {{ number_format($bien->caution, 0, ',', ' ') }} Mois</p>
                                    @endif
                                    <p class="mb-0"><strong> Date fixe de loyer:</strong> {{$bien->date_fixe }} des mois</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Formulaire client -->
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                    <div class="card-header bg-primary text-white py-3">
                        <h4 class="mb-0">Prendre rendez-vous pour une visite</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('visite.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="bien_id" value="{{ $bien->id }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" required>
                                        <label for="nom">Nom complet</label>
                                    </div>
                                    @error('nom')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
                                        <label for="email">Email</label>
                                    </div>
                                    @error('email')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="Votre téléphone" required>
                                        <label for="telephone">Téléphone</label>
                                    </div>
                                    @error('telephone')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}  
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Message supplémentaire" id="message" name="message" style="height: 100px"></textarea>
                                        <label for="message">Message (optionnel)</label>
                                    </div>
                                    @error('message')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" class="form-control" id="date_visite" name="date_visite" min="{{ date('Y-m-d') }}" required>
                                        <label for="date_visite">Date souhaitée</label>
                                    </div>
                                    @error('date_visite')
                                    <div class="alert alert-danger mt-2">
                                        {{ $message }}
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="time" class="form-control" id="heure_visite" name="heure_visite" min="08:00" max="18:00" required>
                                        <label for="heure_visite">Heure souhaitée</label>
                                        @error('heure_visite')
                                        <div class="alert alert-danger mt-2">
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="col-12">
                                        <button class="btn py-3 px-5" style="background-color: #02245b; color:white; " type="submit" id="submitBtn">
                                            Confirmer la visite
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    </script>
@endsection