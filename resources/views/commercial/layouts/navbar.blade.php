<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-start">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
        </button>
        <div class="navbar-brand-wrapper">
            <a class="navbar-brand brand-logo" href="{{ route('commercial.dashboard') }}">
                <img src="{{ asset('assets/images/mae-imo.png') }}"
                    style="width: 65px; height: 65px; object-fit: cover; border-radius: 50%;" alt="logo" />
            </a>
        </div>
        <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1">Bienvenue,
            {{ Auth::guard('commercial')->user()->name }}</h4>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end" style="background-color:#ff5e14;">
        <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item">
                <h4 class="mb-0 font-weight-bold d-none d-xl-block">
                    <span id="live-clock"></span>
                </h4>
            </li>

            <li class="nav-item nav-profile dropdown d-none d-lg-block">
                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                    <img src="{{ asset('assets/images/mae-imo.png') }}"
                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;" alt="profile" />
                    <span class="nav-profile-name">{{ Auth::guard('commercial')->user()->name }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <a href="{{ route('commercial.logout') }}" class="dropdown-item">
                        <i class="mdi mdi-logout text-primary"></i>
                        Déconnexion
                    </a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>

<script>
    function updateClock() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('live-clock').innerText = now.toLocaleDateString('fr-FR', options);
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>