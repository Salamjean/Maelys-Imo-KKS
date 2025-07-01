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
        <p>Partenariat</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-proprio" aria-expanded="false" aria-controls="ui-proprio">
          <i class="mdi mdi-account menu-icon"></i>
          <span class="menu-title">Propriétaire</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-proprio">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.create.admin') }}">Ajout d'un propriétaire</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.index.admin') }}">Liste des propriétaires</a></li>
          </ul>
        </div>
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
        <p>Comptabilité</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#compt" aria-expanded="false" aria-controls="compt">
          <i class="mdi mdi-account menu-icon"></i>
          <span class="menu-title">Agent</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="compt">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.create.admin') }}"> Ajout d'un agent </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.index.admin') }}"> Liste des agents </a></li>
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

      <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('payment.management.admin') }}">
          <i class="mdi mdi-cash menu-icon"></i>
          <span class="menu-title">Loyer</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#paie" aria-expanded="false" aria-controls="paie">
          <i class="mdi mdi-clipboard-account menu-icon"></i>
          <span class="menu-title">Reversement</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="paie">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.index.admin') }}">Reversement demandé </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.completed.admin') }}"> Reversement effectué </a></li>
          </ul>
        </div>
      </li>

       <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#abon" aria-expanded="false" aria-controls="abon">
          <i class="mdi mdi-clipboard-account menu-icon"></i>
          <span class="menu-title">Abonnement</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="abon">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('admin.abonnement.actif') }}">Abonnés actifs</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('admin.abonnement.inactif') }}">Abonnés inactifs</a></li>
          </ul>
        </div>
      </li>
    </ul>
  </nav>