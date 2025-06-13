<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p> @php
            $user = Auth::guard('comptable')->user();
        @endphp

        @if($user->user_type === 'Comptable')
          Comptable : {{ Auth::guard('comptable')->user()->name }} {{ Auth::guard('comptable')->user()->prenom }}</p>
        @elseif($user->user_type === 'Agent de recouvrement')
          Agent : {{ Auth::guard('comptable')->user()->name }} {{ Auth::guard('comptable')->user()->prenom }}</p>
        @endif
        <span></span>
      </li>
     
      @php
          $user = Auth::guard('comptable')->user();
          $dashboardRoute = route('accounting.dashboard'); // par défaut

          if ($user->user_type === 'Agent de recouvrement') {
              $dashboardRoute = route('accounting.agent.dashboard');
          } elseif ($user->user_type === 'Comptable') {
              $dashboardRoute = route('accounting.dashboard');
          }
      @endphp

      <li class="nav-item">
          <a class="nav-link" href="{{ $dashboardRoute }}">
              <i class="mdi mdi-view-quilt menu-icon"></i>
              <span class="menu-title">Tableau de bord</span>
          </a>
      </li>
      <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      @if(auth()->user()->user_type == 'Agent de recouvrement')
        <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#compt" aria-expanded="false" aria-controls="compt">
          <i class="mdi mdi-cash-multiple menu-icon"></i>
          <span class="menu-title">Valider un paiement</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="compt">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.agent.paid') }}"> Valider un paiement </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.agent.history') }}"> Historiques </a></li>
          </ul>
        </div>
      </li>
        @endif

         @php
            $user = Auth::guard('comptable')->user();
        @endphp

        @if($user->user_type === 'Comptable')
            <li class="nav-item">
              <a class="nav-link" data-toggle="collapse" href="#compt" aria-expanded="false" aria-controls="compt">
                <i class="mdi mdi-cash-multiple menu-icon"></i>
                <span class="menu-title">Versement</span>
                <i class="menu-arrow"></i>
              </a>
              <div class="collapse" id="compt">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.paid') }}">Faire un versement</a></li>
                  <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.versement.history') }}"> Historiques </a></li>
                </ul>
              </div>
            </li>
        @endif


      @php
            $user = Auth::guard('comptable')->user();
        @endphp

        @if($user->user_type === 'Comptable')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('accounting.payment') }}">
                    <i class="mdi mdi-cash-multiple menu-icon"></i>
                    <span class="menu-title">Rappel de paiement</span>
                </a>
            </li>
        @endif

      
      <li class="nav-item sidebar-category">
        <p>Locataire</p>
        <span></span>
      </li>
      @php
          $user = Auth::guard('comptable')->user();
          $tenantRoute = route('accounting.tenant'); // route par défaut

          if ($user->user_type === 'Agent de recouvrement') {
              $tenantRoute = route('accounting.agent.tenant');
          }
      @endphp

      <li class="nav-item">
          <a class="nav-link" href="{{ $tenantRoute }}">
              <i class="mdi mdi-account-card-details menu-icon"></i>
              <span class="menu-title">Listes de locataires</span>
          </a>
      </li>

      {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('visite.index.agence') }}">
          <i class="mdi mdi-view-headline menu-icon"></i>
          <span class="menu-title">Liste des visites</span>
        </a>
      </li> --}}
      
    </ul>
  </nav>