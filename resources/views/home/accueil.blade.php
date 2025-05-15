
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
    
    <!-- Footer Start -->
    <div class="container-fluid bg-dark footer mt-5 py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Our Office</h5>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>123 Street, New York, USA</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+012 345 67890</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>info@example.com</p>
                    <div class="d-flex pt-3">
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-twitter"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle me-2" href=""><i
                                class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <a class="btn btn-link" href="">About Us</a>
                    <a class="btn btn-link" href="">Contact Us</a>
                    <a class="btn btn-link" href="">Our Services</a>
                    <a class="btn btn-link" href="">Terms & Condition</a>
                    <a class="btn btn-link" href="">Support</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Business Hours</h5>
                    <p class="mb-1">Monday - Friday</p>
                    <h6 class="text-light">09:00 am - 07:00 pm</h6>
                    <p class="mb-1">Saturday</p>
                    <h6 class="text-light">09:00 am - 12:00 pm</h6>
                    <p class="mb-1">Sunday</p>
                    <h6 class="text-light">Closed</h6>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white mb-4">Newsletter</h5>
                    <p>Dolor amet sit justo amet elitr clita ipsum elitr est.</p>
                    <div class="position-relative w-100">
                        <input class="form-control bg-transparent w-100 py-3 ps-4 pe-5" type="text"
                            placeholder="Your email">
                        <button type="button"
                            class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">SignUp</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->


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

    <!-- Template Javascript -->
    <script src="{{ asset('home/js/main.js') }}"></script>
</body>

</html>