<nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #02245b">
    <ul class="nav">
        <li class="nav-item sidebar-category">
            <p>Commercial : {{ Auth::guard('commercial')->user()->name }}</p>
            <span></span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('commercial.dashboard') }}">
                <i class="mdi mdi-view-quilt menu-icon"></i>
                <span class="menu-title">Tableau de bord</span>
            </a>
        </li>

        <!-- <li class="nav-item sidebar-category">
            <p>Gestion</p>
            <span></span>
        </li> -->
        {{-- On pourra ajouter ici les liens spécifiques aux commerciaux --}}
        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#agences" aria-expanded="false" aria-controls="agences">
                <i class="mdi mdi-domain menu-icon"></i>
                <span class="menu-title">Agences</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="agences">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.agences.create') }}">Ajouter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.agences.index') }}">Listes</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#proprietaires" aria-expanded="false" aria-controls="proprietaires">
                <i class="mdi mdi-account menu-icon"></i>
                <span class="menu-title">Propriétaires</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="proprietaires">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.proprietaires.create') }}">Ajouter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.proprietaires.index') }}">Listes</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-toggle="collapse" href="#biens" aria-expanded="false" aria-controls="biens">
                <i class="mdi mdi-home-modern menu-icon"></i>
                <span class="menu-title">Biens</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="biens">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.biens.choice') }}">Ajouter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('commercial.biens.index') }}">Listes</a>
                    </li>
                </ul>
            </div>
        </li>

        
        <li class="nav-item">
            <a class="nav-link" href="{{ route('commercial.statistics') }}">
                <i class="mdi mdi-chart-areaspline menu-icon"></i>
                <span class="menu-title">Statistiques</span>
            </a>
        </li>

        <!-- <li class="nav-item">
            <a class="nav-link" href="{{ route('commercial.logout') }}">
                <i class="mdi mdi-logout menu-icon"></i>
                <span class="menu-title">Déconnexion</span>
            </a>
        </li> -->
    </ul>
</nav>