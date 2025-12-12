<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <p> @php
            $user = Auth::guard('owner')->user();
        @endphp
      <li class="nav-item sidebar-category">
        <p>Propriétaire : {{ Auth::guard('owner')->user()->name }} </p>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.index.owner') }}">Visite demandée </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('visite.done.owner') }}"> Visite effectuée </a></li>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.create.owner') }}"> Ajout d'un locataire </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.index.owner') }}"> Liste des locatiares </a></li>
          </ul>
        </div>
      </li>
     
      @if(auth()->user()->diaspora == 'Oui')
      <li class="nav-item sidebar-category">
        <p>Agent</p>
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.create.owner') }}"> Ajouter un agent </a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('accounting.index.owner') }}"> Listes des agents  </a></li>
          </ul>
        </div>
      </li>
        @endif
       <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('payment.management.owner') }}">
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
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.create') }}">Faire un reversement</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('reversement.index') }}">Historique reversement</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('rib.create') }}">IBAN</a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('owner.abonnement.show') }}">
           <i class="mdi mdi-account-multiple-minus menu-icon"></i>
          <span class="menu-title">Mon abonnement</span>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="{{ route('owner.tenant.move') }}">
          <i class="mdi mdi-account menu-icon"></i>
          <span class="menu-title">Locataire - déménager</span>
        </a>
      </li>
    </ul>
  </nav>