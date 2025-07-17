@extends('locataire.layouts.template')
@section('content')
<br>
<h2 class="text-center mb-4">Informations sur le bien loué</h2>

<!-- Section des cartes d'information -->
<div class="row">
  <!-- Carte Bien Loué -->
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-primary">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="icon-box-primary icon-box-lg">
            <i class="mdi mdi-home-account text-white"></i>
          </div>
          <div class="ml-3 text-right">
            <h4 class="mb-1 mt-4 text-white">Bien Loué</h4>
            <h3 class="mb-0 text-white">{{ $locataire->bien->type }} à {{ $locataire->bien->commune }}</h3>
            <p class="mb-0 text-white font-weight-light">Agence: {{ $locataire->agence->name ?? 'Maelys-Imo' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Carte Prix du Loyer -->
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-success">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="icon-box-success icon-box-lg">
            <i class="mdi mdi-cash-multiple text-white"></i>
          </div>
          <div class="ml-3 text-right">
            <h4 class="mb-1 mt-4 text-white">Loyer Mensuel</h4>
            <h3 class="mb-0 text-white">{{ number_format($locataire->bien->prix ?? 0, 0, ',', ' ') }} FCFA</h3>
            <p class="mb-0 text-white font-weight-light">Date limite: {{ $locataire->bien->date_fixe }} du mois</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Carte Date de Paiement -->
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-info">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="icon-box-info icon-box-lg">
            <i class="mdi mdi-calendar-clock text-white"></i>
          </div>
          <div class="ml-3 text-right">
            <h4 class="mb-1 mt-4 text-white">Date de Paiement</h4>
            <h3 class="mb-0 text-white">{{ $locataire->bien->date_fixe }} du mois</h3>
            <p class="mb-0 text-white font-weight-light">Prochain paiement dans 15 jours</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Carte QR Code -->
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card" style="background-color: #ff9408">
      <div class="card-body">
        <div class="d-flex flex-column align-items-center text-center">
          <h4 class="mb-3 text-white">Paiement par QR Code</h4>
          
          @if($qrCode && $qrCode->qr_code_path)
            <button type="button" class="btn btn-link p-0 border-0 bg-transparent" data-toggle="modal" data-target="#qrCodeModal">
              <img src="{{ asset('storage/'.$qrCode->qr_code_path) }}" class="img-fluid rounded" style="max-height: 100px;" alt="QR Code">
            </button>
            <p class="mt-2 mb-0 text-white small">
              Valide jusqu'au: {{ $qrCode->expires_at->format('d/m/Y') }}
            </p>
          @else
            <div class="py-2">
              <i class="mdi mdi-alert-circle-outline text-white icon-lg"></i>
              <p class="mt-2 mb-0 text-white">Aucun QR code disponible</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Section Galerie d'images -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title text-center">Galerie du bien</h4>
        <p class="card-description text-center mb-4">Visualisez toutes les photos de votre bien immobilier</p>

        @if($locataire->bien->hasImages())
        <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
          <ol class="carousel-indicators">
            @foreach($locataire->bien->getImages() as $key => $image)
              <li data-target="#propertyCarousel" data-slide-to="{{ $key }}" class="{{ $key === 0 ? 'active' : '' }}"></li>
            @endforeach
          </ol>
          <div class="carousel-inner rounded-lg">
            @foreach($locataire->bien->getImages() as $key => $image)
              <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                <img src="{{ asset('storage/'.$image) }}" class="d-block w-100" alt="Image du bien">
                <div class="carousel-caption d-none d-md-block">
                  <button class="btn btn-primary btn-sm" onclick="openImageModal('{{ asset('storage/'.$image) }}')">
                    <i class="mdi mdi-magnify"></i> Agrandir
                  </button>
                </div>
              </div>
            @endforeach
          </div>
          <a class="carousel-control-prev" href="#propertyCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Précédent</span>
          </a>
          <a class="carousel-control-next" href="#propertyCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Suivant</span>
          </a>
        </div>
        @else
        <div class="alert alert-info text-center">
          <i class="mdi mdi-information-outline"></i> Aucune image disponible pour ce bien
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Modal pour l'affichage en grand -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Photo du bien</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center p-0">
        <img id="modalImage" src="" class="img-fluid w-100" alt="Image agrandie">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">QR Code de paiement</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        @if($qrCode && $qrCode->qr_code_path)
          <img src="{{ asset('storage/'.$qrCode->qr_code_path) }}" class="img-fluid mb-3" alt="QR Code">
          <div class="alert alert-info text-left">
            <p class="mb-1"><strong>Montant:</strong> {{ number_format($qrCode->montant_total, 0, ',', ' ') }} FCFA</p>
            <p class="mb-1"><strong>Période:</strong> {{ $qrCode->mois_couverts ?? '1' }} mois</p>
            <p class="mb-0"><strong>Expiration:</strong> {{ $qrCode->expires_at->format('d/m/Y à H:i') }}</p>
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
        @if($qrCode && $qrCode->qr_code_path)
          <a href="{{ asset('storage/'.$qrCode->qr_code_path) }}" download="QRCode-Paiement-{{ date('Ymd') }}" class="btn btn-primary">
            <i class="mdi mdi-download"></i> Télécharger
          </a>
        @endif
      </div>
    </div>
  </div>
</div>

<style>
  .icon-box-primary, .icon-box-success, .icon-box-info {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .icon-box-primary { background: rgba(255,255,255,0.2); }
  .icon-box-success { background: rgba(255,255,255,0.2); }
  .icon-box-info { background: rgba(255,255,255,0.2); }
  
  .card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 20px 0 rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
  }
  .card:hover {
    transform: translateY(-5px);
  }
  
  .carousel-inner {
    max-height: 500px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }
  .carousel-item img {
    object-fit: cover;
    width: 100%;
    height: 500px;
  }
  .carousel-caption {
    background: rgba(0,0,0,0.5);
    border-radius: 20px;
    padding: 5px 10px !important;
    right: 20px;
    left: auto;
    bottom: 20px;
    width: auto;
  }
</style>

<script>
function openImageModal(imageUrl) {
  $('#imageModal #modalImage').attr('src', imageUrl);
  $('#imageModal').modal('show');
}

$(document).ready(function(){
  // Initialisation du carousel
  $('.carousel').carousel({
    interval: 5000,
    pause: "hover"
  });
  
  // Gestion du clic sur les indicateurs du carousel
  $('.carousel-indicators li').click(function() {
    $(this).addClass('active').siblings().removeClass('active');
  });
});
</script>
@endsection