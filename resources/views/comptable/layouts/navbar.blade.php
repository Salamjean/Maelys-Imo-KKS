<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
        <span class="mdi mdi-menu"></span>
      </button>
      <div class="navbar-brand-wrapper">
        @php
            $user = Auth::guard('comptable')->user();
            $route = route('accounting.dashboard'); // valeur par défaut

            if ($user->user_type === 'Agent de recouvrement') {
                $route = route('accounting.agent.dashboard');
            } elseif ($user->user_type === 'Comptable') {
                $route = route('accounting.dashboard');
            }
        @endphp

        <a class="navbar-brand brand-logo" href="{{ $route }}">
            @if($user && $user->agence)
                <img src="{{ asset('storage/' . $user->agence->profile_image) }}" 
                    style="width: 65px" 
                    onerror="this.onerror=null; this.src='{{ asset('assets/images/kkstevhno.jpeg') }}';" 
                    alt="logo" />
            @else
                <img src="{{ asset('assets/images/kkstevhno.jpeg') }}" style="width: 65px" alt="logo" />
            @endif
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ $route }}">
            @php
                $user = Auth::guard('comptable')->user();
                $profileImage = ($user && $user->agence && $user->agence->profile_image)
                    ? asset('storage/' . $user->agence->profile_image)
                    : asset('assets/images/kkstevhno.jpeg');
            @endphp

            <img src="{{ $profileImage }}" 
                style="width: 10px" 
                alt="logo"
                onerror="this.onerror=null; this.src='{{ asset('assets/images/kkstevhno.jpeg') }}'" />
        </a>
      </div>
      <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1">Bienvenu M./Mme {{ Auth::guard('comptable')->user()->name }} {{ Auth::guard('comptable')->user()->prenom }}</h4>
      <ul class="navbar-nav navbar-nav-right">
        <li class="nav-item">
          <h4 class="mb-0 font-weight-bold d-none d-xl-block">
              <span id="live-clock"></span>
          </h4>
        </li>
      </ul>
      <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
        <span class="mdi mdi-menu"></span>
      </button>
    </div>
    <div class="navbar-menu-wrapper navbar-search-wrapper d-none d-lg-flex align-items-center" style="background-color: #ff5e14">
      <ul class="navbar-nav navbar-nav-right">
        <li class="nav-item nav-profile dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
             @if($user && $user->agence)
                <img src="{{ asset('storage/' . $user->agence->profile_image) }}" 
                    style="width: 65px" 
                    onerror="this.onerror=null; this.src='{{ asset('assets/images/kkstevhno.jpeg') }}';" 
                    alt="logo" />
            @else
                <img src="{{ asset('assets/images/kkstevhno.jpeg') }}" style="width: 40px" alt="logo" />
            @endif
            <span class="nav-profile-name">{{ Auth::guard('comptable')->user()->prenom }} </span>
          </a>
          <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
            <a href="{{ route('accounting.logout') }}" class="dropdown-item">
              <i class="mdi mdi-logout text-primary"></i>
              Déconnexion
            </a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
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
