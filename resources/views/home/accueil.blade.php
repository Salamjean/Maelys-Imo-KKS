
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Maelys-imo</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{asset('assets/vendors/mdi/css/materialdesignicons.min.css')}}">
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Rubik:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('home/lib/animate/animate.min.css') }}" rel="stylesheet">
    <link href="{{ asset('home/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('home/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('home/css/style.css') }}" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    @include('home.layouts.navbar')
    <!-- Navbar End -->


    <!-- Carousel Start -->
    @include('home.layouts.carousel')
    <!-- Carousel End -->


    <!-- Affichage des biens -->
    @include('home.layouts.biens')
    <!-- About End -->


    <!-- Facts Start -->
    <div class="container-fluid facts my-5 p-5">
        <div class="row g-5">
            <div class="col-md-6 col-xl-4 wow fadeIn" data-wow-delay="0.1s">
                <div class="text-center border p-5">
                    <i class="mdi mdi-home-account text-white" style="font-size: 60px"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">{{ $appartements }}</h1>
                    <span class="fs-5 fw-semi-bold text-white">Total d'appartement disponible</span>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 wow fadeIn" data-wow-delay="0.3s">
                <div class="text-center border p-5">
                    <i class="mdi mdi-home-variant text-white icon-lg" style="font-size: 60px"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">{{ $maisons }}</h1>
                    <span class="fs-5 fw-semi-bold text-white">Total de maison disponible</span>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 wow fadeIn" data-wow-delay="0.5s">
                <div class="text-center border p-5">
                    <i class="mdi mdi-home text-white icon-lg" style="font-size: 60px"></i>
                    <h1 class="display-2 text-primary mb-0" data-toggle="counter-up">{{ $terrains }}</h1>
                    <span class="fs-5 fw-semi-bold text-white">Total de bureau disponible</span>
                </div>
            </div>
            
        </div>
    </div>
    <!-- Facts End -->
    
<!-- Partners Section -->
<!-- Bannière Partenaires Unifiée -->
<div class="container-fluid py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 wow fadeIn" data-wow-delay="0.1s">
            <h2 class="display-5">NOS PARTENAIRES</h2>
            <p class="text-muted">Découvrez nos propriétaires et agences récemment inscrits</p>
        </div>

        @if($derniersPartenaires->count() > 0)
        <div class="row">
            @foreach($derniersPartenaires as $partenaire)
            <div class="col-6 col-md-4 col-lg-3 col-xl-2 mb-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body text-center p-3">
                        <!-- Avatar -->
                        @if(($partenaire->type === 'Propriétaire' && $partenaire->profil_image) || 
                            ($partenaire->type === 'Agence' && $partenaire->profile_image))
                            <img src="{{ asset('storage/'.($partenaire->type === 'Propriétaire' ? $partenaire->profil_image : $partenaire->profile_image)) }}" 
                                 class="img-fluid rounded-circle mb-3" 
                                 style="width: 80px; height: 80px; object-fit: cover;" 
                                 alt="{{ $partenaire->name }}"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/80'">
                        @else
                            <div class="mx-auto rounded-circle {{ $partenaire->type === 'Propriétaire' ? 'bg-primary' : 'bg-success' }} d-flex align-items-center justify-content-center mb-3" 
                                 style="width: 80px; height: 80px;">
                                <span class="text-white h4">{{ substr($partenaire->name, 0, 1) }}</span>
                            </div>
                        @endif

                        <!-- Nom -->
                        <h6 class="card-title mb-1">
                            {{ $partenaire->name }}
                            @if($partenaire->type === 'Propriétaire') {{ $partenaire->prenom }} @endif
                        </h6>

                        <!-- Type avec badge coloré -->
                        <span class="badge {{ $partenaire->type === 'Propriétaire' ? 'bg-primary' : 'bg-success' }} mb-2">
                            {{ $partenaire->type }}
                        </span>

                        <!-- Infos supplémentaires -->
                        <p class="small text-muted mb-1">
                            <i class="fas fa-map-marker-alt"></i> {{ $partenaire->commune }}
                        </p>
                        
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="alert alert-info text-center">
            Aucun partenaire récent à afficher pour le moment.
        </div>
        @endif
    </div>
</div>


    <!-- Footer Start -->
    <div class="container-fluid bg-dark footer mt-5 py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-4 col-md-12">
                    <h5 class="text-white mb-4">Localisation</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Cocody - Angré, Abidjan</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+225 27 22 36 50 27</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>info@example.com</p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-twitter"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" target="_blank" href="https://www.facebook.com/profile.php?id=61577304887475"><i
                                class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5 class="text-white mb-4">Liens rapides</h5>
                    <a class="btn btn-link" href="{{ route('bien.appartement') }}">Appartements</a>
                    <a class="btn btn-link" href="{{ route('bien.maison') }}">Maisons</a>
                    <a class="btn btn-link" href="{{ route('bien.terrain') }}">Bureaux</a>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5 class="text-white mb-4">Heures de travail</h5>
                    <p class="mb-1">Lundi - Vendredi</p>
                    <h6 class="text-light">08:00 - 17:00</h6>
                    <p class="mb-1">Samedi</p>
                    <h6 class="text-light">09:00 - 12:00 </h6>
                    <p class="mb-1">Dimanche</p>
                    <h6 class="text-light">Fermé</h6>
                </div>
                
            </div>
        </div>
    </div>
    <!-- Footer End -->
 <style>
    .partner-item {
    padding: 20px;
    transition: all 0.3s;
}

.partner-item:hover {
    transform: translateY(-5px);
}

.partner-carousel .owl-item {
    display: flex;
    justify-content: center;
}

.owl-carousel .owl-stage {
    display: flex;
    align-items: center;
}
 </style>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i
            class="bi bi-arrow-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('home/wow/wow.min.js') }}"></script>
    <script src="{{ asset('home/easing/easing.min.js') }}"></script>
    <script src="{{ asset('home/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('home/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('home/counterup/counterup.min.js') }}"></script>
<script>
    // Initialiser les carrousels
    $(document).ready(function(){
        $(".partner-carousel").owlCarousel({
            loop: true,
            margin: 20,
            autoplay: true,
            smartSpeed: 1000,
            responsive: {
                0: {
                    items: 2
                },
                576: {
                    items: 3
                },
                768: {
                    items: 4
                },
                992: {
                    items: 5
                }
            }
        });
    });
</script>
    <!-- Template Javascript -->
    <script src="{{ asset('home/js/main.js') }}"></script>
</body>

</html>