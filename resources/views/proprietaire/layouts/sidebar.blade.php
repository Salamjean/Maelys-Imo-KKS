<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p>Propriétaire : {{ Auth::guard('owner')->user()->name }} {{ Auth::guard('owner')->user()->prenom }}</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('owner.dashboard') }}">
          <i class="mdi mdi-view-quilt menu-icon"></i>
          <span class="menu-title">Tableau de bord</span>
        </a>
      </li>
      <li class="nav-item sidebar-category">
        <p>Bien - immobilier</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
          <i class="mdi mdi-animation menu-icon"></i>
          <span class="menu-title">Biens</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-basic">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.create.owner') }}">Ajout d'un bien</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.bienList') }}">Liste des biens</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.bienList.loue') }}">Liste des biens loués</a></li>
          </ul>
        </div>
      </li>
      {{-- <li class="nav-item sidebar-category">
        <p>Biens</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('owner.bienList') }}">
          <i class="mdi mdi-account-card-details menu-icon"></i>
          <span class="menu-title">Listes de mes biens</span>
        </a>
      </li> --}}
      {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('visite.index.agence') }}">
          <i class="mdi mdi-view-headline menu-icon"></i>
          <span class="menu-title">Liste des visites</span>
        </a>
      </li> --}}
      {{-- <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="mdi mdi-cash-multiple menu-icon"></i>
          <span class="menu-title">Rappel de paiement</span>
        </a>
      </li> --}}
    </ul>
  </nav>