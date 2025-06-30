<nav class="navbar col-lg-12 col-12 px-0 py-0 py-lg-4 d-flex flex-row">
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
      <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
        <span class="mdi mdi-menu"></span>
      </button>
      <div class="navbar-brand-wrapper">
        <a class="navbar-brand brand-logo" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/images/mae-imo.png') }}" style="width: 65px; background-color:white" alt="logo"/></a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/images/mae-imo.png') }}" style="width: 30px ; background-color:white" alt="logo"/></a>
      </div>
      <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1">Bienvenu chez {{ Auth::guard('admin')->user()->name }}</h4>
      <ul class="navbar-nav navbar-nav-right">
        <li class="nav-item">
          <h4 class="mb-0 font-weight-bold d-none d-xl-block">
              <span id="live-clock"></span>
          </h4>
        </li>
        <!-- Notification Dropdown -->
            <li class="nav-item dropdown mx-2">
                <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
                    <i class="mdi mdi-bell-outline"></i>
                    @if($pendingVisits > 0)
                    <span class="count bg-danger">{{ $pendingVisits }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                    <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                    @if($pendingVisits > 0)
                    <a class="dropdown-item preview-item" href="{{ route('visite.index') }}">
                        <div class="preview-thumbnail">
                            <div class="preview-icon bg-warning">
                                <i class="mdi mdi-calendar-clock"></i>
                            </div>
                        </div>
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-normal">{{ $pendingVisits }} demande(s) de visite</h6>
                            <p class="font-weight-light small-text mb-0 text-muted">
                                En attente de traitement
                            </p>
                        </div>
                    </a>
                    @else
                    <a class="dropdown-item preview-item">
                        <div class="preview-item-content">
                            <h6 class="preview-subject font-weight-normal">Aucune notification</h6>
                        </div>
                    </a>
                    @endif
                </div>
            </li>
      </ul>
      
      <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
        <span class="mdi mdi-menu"></span>
      </button>
    </div>
    <div class="navbar-menu-wrapper navbar-search-wrapper d-none d-lg-flex align-items-center" style="background-color: #ff5e14">
  
      <ul class="navbar-nav navbar-nav-right">
        <li class="nav-item nav-profile dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
            <img src="{{ asset('assets/images/mae-imo.png') }}" style="background-color:white" alt="profile"/>
            <span class="nav-profile-name">{{ Auth::guard('admin')->user()->name }}</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
            <a href="{{ route('admin.logout') }}" class="dropdown-item">
              <i class="mdi mdi-logout text-primary"></i>
              Déconnexion
            </a>
          </div>
        </li>
      </ul>
    </div>
  </nav>
  <script>
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
            hour: '2-digit', minute: '2-digit', second: '2-digit'
        };
        const formattedDate = now.toLocaleDateString('fr-FR', options);
        document.getElementById('live-clock').innerText = formattedDate;
    }

    // Mettre à jour immédiatement et toutes les secondes
    updateClock();
    setInterval(updateClock, 1000);
</script>
<style>
    /* Style pour la notification */
    .count-indicator {
        position: relative;
        color: #fff;
        padding: 0.5rem;
        border-radius: 50%;
        transition: all 0.3s;
    }
    
    .count-indicator:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    .count {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
    }
    
    /* Style pour le dropdown des notifications */
    .preview-list {
        width: 300px;
        padding: 0;
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    
    .dropdown-header {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        font-size: 0.875rem;
    }
    
    .preview-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.3s;
    }
    
    .preview-item:hover {
        background-color: #f9f9f9;
    }
    
    .preview-thumbnail {
        margin-right: 1rem;
    }
    
    .preview-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .preview-item-content {
        flex: 1;
    }
    
    .preview-subject {
        margin-bottom: 0.25rem;
        font-size: 0.875rem;
    }
    
    .small-text {
        font-size: 0.75rem;
    }
</style>







