<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p>Agence : {{ Auth::guard('agence')->user()->name }}</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('agence.dashboard') }}">
          <i class="mdi mdi-view-quilt menu-icon"></i>
          <span class="menu-title">Tableau de bord</span>
        </a>
      </li>
      <li class="nav-item sidebar-category">
        <p>Proprietaire - Bien</p>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.create') }}">Ajout d'un propriétaire</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('owner.index') }}">Liste des propriétaires</a></li>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.create.agence') }}">Ajout d'un bien</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.index.agence') }}">Liste des biens</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('bien.rented') }}">Liste des biens loués</a></li>
          </ul>
        </div>
      </li>
      {{-- <li class="nav-item">
        <a class="nav-link" href="{{ route('visite.index.agence') }}">
          <i class="mdi mdi-view-headline menu-icon"></i>
          <span class="menu-title">Liste des visites</span>
        </a>
      </li> --}}
      <li class="nav-item sidebar-category">
        <p>Visite</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
          <i class="mdi mdi-clipboard-account menu-icon"></i>
          <span class="menu-title">Visites</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="auth">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.index.agence') }}">Visite demandée </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.done.agence') }}"> Visite effectuée </a></li>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.create') }}"> Ajout d'un agent </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.index') }}"> Liste des agents </a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item sidebar-category">
        <p>Locataire</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#loca" aria-expanded="false" aria-controls="loca">
          <i class="mdi mdi-account-key menu-icon"></i>
          <span class="menu-title">Locataire</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="loca">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.create') }}"> Ajout d'un locataire </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.index') }}"> Liste des locatiares </a></li>
          </ul>
        </div>
      </li>
       <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('payment.management.agence') }}">
          <i class="mdi mdi-cash menu-icon"></i>
          <span class="menu-title">Loyer</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-rev" aria-expanded="false" aria-controls="ui-rev">
          <i class="mdi mdi-animation menu-icon"></i>
          <span class="menu-title">Reversement</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-rev">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.create.agence') }}">Faire un reversement</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.index.agence') }}">Historique reversement</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('rib.create.agence') }}">R.I.B</a></li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#ui-par" aria-expanded="false" aria-controls="ui-par">
          <i class="mdi mdi-account-multiple menu-icon"></i>
          <span class="menu-title">Partenaire</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="ui-par">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href=" {{ route('partner.payment.create') }} ">Paiement</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{route('partner.payment.index')}}">Historique paiement</a></li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('agence.abonnement.show') }}">
           <i class="mdi mdi-account-multiple-minus menu-icon"></i>
          <span class="menu-title">Mon abonnement</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('agence.tenant.move') }}">
          <i class="mdi mdi-account menu-icon"></i>
          <span class="menu-title">Locataire - déménager</span>
        </a>
      </li>
    </ul>
  </nav>
