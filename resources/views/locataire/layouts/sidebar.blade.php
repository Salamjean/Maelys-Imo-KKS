<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
      <li class="nav-item sidebar-category">
        <p class="text-center">Locataire : {{ Auth::guard('locataire')->user()->name }} {{ Auth::guard('locataire')->user()->prenom }}</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('locataire.dashboard') }}">
          <i class="mdi mdi-view-quilt menu-icon"></i>
          <span class="menu-title">Tableau de bord</span>
        </a>
      </li>
      
      <li class="nav-item sidebar-category">
        <p>Paiement</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
          <i class="mdi mdi-home menu-icon"></i>
          <span class="menu-title">Loyer</span>
          <i class="menu-arrow"></i>
        </a>
        <div class="collapse" id="auth">
          <ul class="nav flex-column sub-menu">
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.paiements.create' , ['locataire' => Auth::guard('locataire')->user()->id]) }}"> Payer le loyer</a></li>
            <li class="nav-item"> <a class="nav-link" href="{{ route('locataire.paiements.index' , ['locataire' => Auth::guard('locataire')->user()->id]) }}"> Historique de loyer </a></li>
          </ul>
        </div>
      </li>
      <li class="nav-item sidebar-category">
        <p>Bien</p>
        <span></span>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('locataire.bien.show', Auth::guard('locataire')->user()->id) }}">
           <i class="mdi mdi-home menu-icon"></i>
          <span class="menu-title">Bien loué</span>
        </a>
      </li>
    </ul>
  </nav>