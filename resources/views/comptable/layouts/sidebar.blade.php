<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p>Comptable : {{ Auth::guard('comptable')->user()->name }} {{ Auth::guard('comptable')->user()->prenom }}</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('accounting.dashboard') }}">
          <i class="mdi mdi-view-quilt menu-icon"></i>
          <span class="menu-title">Tableau de bord</span>
        </a>
      </li>
      <li class="nav-item sidebar-category">
        <p>Locataire</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('accounting.tenant') }}">
          <i class="mdi mdi-view-headline menu-icon"></i>
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
      <li class="nav-item">
        <a class="nav-link" href="{{ route('accounting.payment') }}">
          <i class="mdi mdi-view-headline menu-icon"></i>
          <span class="menu-title">Rappel de paiement</span>
        </a>
      </li>
    </ul>
  </nav>