@extends('locataire.layouts.template')
@section('content')
<br>
<h2 class="text-center mb-4">Informations sur le bien loué</h2>

<!-- Section des cartes d'information -->
<div class="row">
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card bg-facebook d-flex align-items-center">
      <div class="card-body py-5">
        <div class="d-flex flex-row align-items-center flex-wrap justify-content-md-center justify-content-xl-start py-1">
          <i class="mdi mdi-home-account text-white icon-lg"></i>
          <div class="ml-3 ml-md-0 ml-xl-3">
            <h5 class="text-white font-weight-bold">Bien Loué</h5>
            <h3 class="text-white font-weight-bold">{{ $locataire->bien->type }} à {{ $locataire->bien->commune }}</h3>
            <p class="mt-2 text-white card-text">Pour l'agence : {{ $locataire->agence->name ?? 'Maelys-Imo' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card bg-google d-flex align-items-center">
      <div class="card-body py-5">
        <div class="d-flex flex-row align-items-center flex-wrap justify-content-md-center justify-content-xl-start py-1">
          <i class="mdi mdi-cards text-white icon-lg"></i>
          <div class="ml-3 ml-md-0 ml-xl-3">
            <h5 class="text-white font-weight-bold">Montant du bien loué</h5>
            <h3 class="text-white font-weight-bold">{{ number_format($locataire->bien->prix ?? 0, 0, ',', ' ') }} FCFA</h3>
            <p class="mt-2 text-white card-text">Pour l'agence : {{ $locataire->agence->name ?? 'Maelys-Imo' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card bg-twitter d-flex align-items-center">
      <div class="card-body py-5">
        <div class="d-flex flex-row align-items-center flex-wrap justify-content-md-center justify-content-xl-start py-1">
          <i class="mdi mdi-calendar-multiple text-white icon-lg"></i>
          <div class="ml-3 ml-md-0 ml-xl-3">
            <h5 class="text-white font-weight-bold">Date fixe du paiement de loyer</h5>
            <h3 class="text-white font-weight-bold">{{ $locataire->bien->date_fixe }} de chaque mois</h3>
            <p class="mt-2 text-white card-text">Pour l'agence : {{ $locataire->agence->name ?? 'Maelys-Imo' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Section Galerie d'images -->
<div class="row mt-5">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title text-center">Galerie du bien que vous avez loué</h4>
        <p class="card-description text-center">Visualisez toutes les photos de votre bien immobilier</p>

        @if($locataire->bien->hasImages())
        <div id="propertyCarousel" class="carousel slide" data-ride="carousel">
          <ol class="carousel-indicators">
            @foreach($locataire->bien->getImages() as $key => $image)
              <li data-target="#propertyCarousel" data-slide-to="{{ $key }}" class="{{ $key === 0 ? 'active' : '' }}"></li>
            @endforeach
          </ol>
          <div class="carousel-inner">
            @foreach($locataire->bien->getImages() as $key => $image)
              <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                <img src="{{ asset('storage/'.$image) }}" class="d-block w-100 rounded" alt="Image du bien">
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
      <div class="modal-body text-center">
        <img id="modalImage" src="" class="img-fluid" alt="Image agrandie">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<style>
  .carousel-inner {
    max-height: 500px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  .carousel-item img {
    object-fit: cover;
    width: 100%;
    height: 500px;
  }
  .img-thumbnail {
    transition: transform 0.3s ease;
    cursor: pointer;
  }
  .img-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }
</style>

<script>
$(document).ready(function(){
  // Initialisation du carousel
  $('.carousel').carousel();
  
  // Gestion du modal
  $('#imageModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var imageUrl = button.data('image');
    var modal = $(this);
    modal.find('#modalImage').attr('src', imageUrl);
  });
});
</script>
@endsection