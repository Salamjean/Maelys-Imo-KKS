<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p>Agent : {{ Auth::guard('comptable')->user()->name }} {{ Auth::guard('comptable')->user()->prenom }}</p>
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
      <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      @php
          $user = Auth::guard('comptable')->user();
          $paymentRoute = route('accounting.payment'); // route par défaut

          if ($user->user_type === 'Agent de recouvrement') {
              $paymentRoute = route('accounting.agent.payment');
          }
      @endphp

      <li class="nav-item">
          <a class="nav-link" href="{{ $paymentRoute }}">
              <i class="mdi mdi-cash-multiple menu-icon"></i>
              <span class="menu-title">Rappel de paiement</span>
          </a>
      </li>
    </ul>
  </nav>