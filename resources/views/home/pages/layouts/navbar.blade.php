 <!-- Spinner Start -->
 <div id="spinner"
 class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
 <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
</div>
<!-- Spinner End -->


<!-- Topbar Start -->
<div class="container-fluid bg-dark px-0">
 <div class="row g-0 d-none d-lg-flex">
     <div class="col-lg-6 ps-5 text-start">
         <div class="h-100 d-inline-flex align-items-center text-white">
             <span>Suivez-nous :</span>
             <a class="btn btn-link text-light" target="_blank" href="https://www.facebook.com/profile.php?id=61577304887475"><i class="fab fa-facebook-f"></i></a>
             <a class="btn btn-link text-light" href=""><i class="fab fa-twitter"></i></a>
             <a class="btn btn-link text-light" href=""><i class="fab fa-linkedin-in"></i></a>
             <a class="btn btn-link text-light" href=""><i class="fab fa-instagram"></i></a>
         </div>
     </div>
     <div class="col-lg-6 text-end">
         <div class="h-100 topbar-right d-inline-flex align-items-center text-white py-2 px-5">
             <span class="fs-5 fw-bold me-2"><i class="fa fa-phone-alt me-2"></i>Appelez-nous :</span>
             <span class="fs-5 fw-bold">+225 27 22 36 50 27</span>
         </div>
     </div>
 </div>
</div>
<!-- Topbar End -->


<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top py-0 pe-5">
 <a href="/" class="navbar-brand ps-5 me-0">
     <h1 class="text-white m-0">Maelys-imo</h1>
 </a>
 <button type="button" class="navbar-toggler me-0" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
     <span class="navbar-toggler-icon"></span>
 </button>
 <div class="collapse navbar-collapse" id="navbarCollapse">
     <div class="navbar-nav ms-auto p-4 p-lg-0">
         <a href="/" class="nav-item nav-link active">Accueil</a>
         <a href="{{ route('maelys.about') }}" class="nav-item nav-link">A propos</a>
         <a href="{{ route('maelys.service') }}" class="nav-item nav-link">Services</a>
         <div class="nav-item dropdown">
             <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Biens</a>
             <div class="dropdown-menu bg-light m-0">
                 <a href="{{ route('bien.appartement') }}" class="dropdown-item">Appartement</a>
                 <a href="{{ route('bien.maison') }}" class="dropdown-item">Maison</a>
                 <a href="{{ route('bien.terrain') }}" class="dropdown-item">Bureau</a>
             </div>
         </div>
     </div>
    <div class="d-flex align-items-center">
        <!-- Menu déroulant -->
        <select class="btn btn-primary font-bold px-3 d-none d-lg-block text-white" style="width: auto;" onchange="window.location.href=this.value">
            <option value="" selected disabled style="font-weight: bold">Se connecter</option>
            <option value="{{ route('agence.login') }}">Agence</option>
            <option value="{{ route('owner.login') }}">Propriétaire</option>
            <option value="{{ route('locataire.login') }}">Locataire</option>
            <option value="{{ route('comptable.login') }}">Agent</option>
        </select>
    </div>
 </div>
</nav>