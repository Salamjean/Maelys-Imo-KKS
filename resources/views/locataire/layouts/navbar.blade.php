<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
    <!-- Partie gauche - Logo et menu burger -->
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-start" >
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
        <div class="navbar-brand-wrapper">
            <!-- Logo principal (version desktop) -->
            <a class="navbar-brand brand-logo" href="{{ route('locataire.dashboard') }}">
                <img src="{{ asset('storage/' . Auth::guard('locataire')->user()->profile_image) }}"
                    onerror="this.onerror=null; this.src='{{ asset('assets/images/mae-imo.png') }}';" 
                    style="width: 65px; height: 65px; object-fit: cover; border-radius: 50%;" alt="logo"/>
            </a>
            
            <!-- Logo mini (version mobile) avec dropdown -->
            <div class="dropdown d-lg-none">
                <a class="navbar-brand brand-logo-mini dropdown-toggle p-0" href="#" role="button" id="mobileLogoDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="{{ asset('storage/' . Auth::guard('locataire')->user()->profile_image) }}"
                        onerror="this.onerror=null; this.src='{{ asset('assets/images/mae-imo.png') }}';" 
                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; cursor: pointer;" alt="logo"/>
                </a>
                <div class="dropdown-menu dropdown-menu-left" aria-labelledby="mobileLogoDropdown">
                    <div class="dropdown-header text-center">
                        <h6 class="mb-0">{{ Auth::guard('locataire')->user()->name }}</h6>
                        <small class="text-muted">{{ Auth::guard('locataire')->user()->email }}</small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('locataire.dashboard') }}">
                        <i class="mdi mdi-home mr-2 text-primary"></i> Tableau de bord
                    </a>
                    <a class="dropdown-item" href="{{ route('locataire.edit.profile') }}">
                        <i class="mdi mdi-account mr-2 text-primary"></i> Mon profil
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('locataire.logout') }}">
                        <i class="mdi mdi-logout mr-2 text-primary"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
        <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1">Bienvenue chez {{ Auth::guard('locataire')->user()->name }}</h4>
    </div>

    <!-- Partie droite - Contrôles -->
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end"  style=" background-color:#ff5e14;">
        <!-- Horloge -->
        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item">
                <h4 class="mb-0 font-weight-bold d-none d-xl-block">
                    <span id="live-clock"></span>
                </h4>
            </li>


            <!-- Profil - Version Desktop -->
            <li class="nav-item nav-profile dropdown d-none d-lg-block">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                    <img src="{{ asset('storage/' . Auth::guard('locataire')->user()->profile_image) }}"
                        onerror="this.onerror=null; this.src='{{ asset('assets/images/mae-imo.png') }}';" 
                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" alt="profile"/>
                    <span class="nav-profile-name">{{ Auth::guard('locataire')->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <a href="{{ route('locataire.edit.profile') }}" class="dropdown-item">
                        <i class="mdi mdi-account text-primary"></i>
                        Profil
                    </a>
                    <a href="{{ route('locataire.logout') }}" class="dropdown-item">
                        <i class="mdi mdi-logout text-primary"></i>
                        Déconnexion
                    </a>
                </div>
            </li>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

<!-- Script pour l'horloge -->
<script>
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        };
        const formattedDate = now.toLocaleDateString('fr-FR', options);
        document.getElementById('live-clock').innerText = formattedDate;
    }

    // Mettre à jour immédiatement et toutes les secondes
    updateClock();
    setInterval(updateClock, 1000);
</script>

<!-- Styles CSS spécifiques -->
<style>
  /* Assurez-vous que la navbar reste au-dessus de la sidebar */
.navbar {
    position: relative;
    z-index: 1030; /* Bootstrap utilise 1030 pour les navbars fixes */
}

/* Style spécifique pour le bouton burger */
.navbar-toggler.align-self-center {
    position: relative;
    z-index: 1031; /* Plus élevé que la navbar */
    margin-right: 15px;
}

/* Correction pour la sidebar */
.sidebar {
    z-index: 1020; /* Doit être inférieur à la navbar */
}
    /* Style pour le dropdown du logo mobile */
    .navbar-brand-wrapper .dropdown .brand-logo-mini {
        display: inline-block;
        padding: 5px;
    }
    
    /* Supprime la flèche du dropdown */
    .navbar-brand-wrapper .dropdown-toggle::after {
        display: none;
    }
    
    /* Amélioration du menu dropdown */
    .navbar-brand-wrapper .dropdown-menu {
        min-width: 250px;
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .navbar-brand-wrapper .dropdown-header {
        padding: 10px 15px;
    }
    
    .navbar-brand-wrapper .dropdown-item {
        padding: 10px 15px;
        transition: all 0.3s;
    }
    
    .navbar-brand-wrapper .dropdown-item:hover {
        background-color: #f8f9fa;
        padding-left: 20px;
    }
    
    /* Ajustement pour mobile */
    @media (max-width: 991px) {
        .navbar-nav .dropdown-menu {
            position: absolute !important;
            right: 0 !important;
            left: auto !important;
            margin-top: 0.5rem;
        }
        
        .navbar-brand-wrapper {
            margin-right: auto;
        }
    }
</style>