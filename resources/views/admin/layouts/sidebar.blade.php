<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p>Administrateur</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.create') }}">Ajout d'un bien</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.index') }}">Liste des biens</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.rentedAdmin') }}">Liste des biens loués</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item sidebar-category">
        <p>Visite</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#visi" aria-expanded="false" aria-controls="visi">
          <i class="mdi mdi-clipboard-account menu-icon"></i>
          <span class="menu-title">Visites</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="visi">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.index') }}">Visite demandée </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.done') }}"> Visite effectuée </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('visit.list') }}">Historique des visites</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item sidebar-category">
        <p>Partenariat</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
          <i class="mdi mdi-home menu-icon"></i>
          <span class="menu-title">Agence</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="auth">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('agence.create') }}"> Ajout d'une agence </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('agence.index') }}"> Liste des agences </a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item sidebar-category">
        <p>Locataire</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#local" aria-expanded="false" aria-controls="local">
          <i class="mdi mdi-account-key menu-icon"></i>
          <span class="menu-title">Locataire</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="local">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.admin.create') }}"> Ajout d'un locataire </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.admin.index') }}"> Liste des locatiares </a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('locataire.admin.indexSerieux') }}">
           <i class="mdi mdi-account-multiple-minus menu-icon"></i>
          <span class="menu-title">Locataire pas sérieux</span>
        </a>
      </li>
    </ul>
  </nav>