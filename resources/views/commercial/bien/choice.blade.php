@extends('commercial.layouts.template')

@section('content')
<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body text-center">
                <h4 class="card-title">Ajouter un nouveau bien</h4>
                <p class="card-description">Veuillez choisir pour qui vous souhaitez enregistrer ce bien</p>
                
                <div class="row mt-5 justify-content-center">
                    <!-- Option Agence -->
                    <div class="col-md-5 mb-4">
                        <div class="card border-primary h-100 hover-shadow" style="cursor: pointer; transition: 0.3s; border: 2px solid #02245b !important;">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5" onclick="window.location='{{ route('commercial.biens.create', ['type' => 'agence']) }}'">
                                <div class="icon-box mb-4" style="background: rgba(2, 36, 91, 0.1); padding: 20px; border-radius: 50%;">
                                    <i class="mdi mdi-office-building text-primary" style="font-size: 4rem; color: #02245b !important;"></i>
                                </div>
                                <h3 class="font-weight-bold" style="color: #02245b;">Pour une Agence</h3>
                                <p class="text-muted text-center px-3">Enregistrer un bien qui sera géré par l'une de vos agences partenaires.</p>
                                <a href="{{ route('commercial.biens.create', ['type' => 'agence']) }}" class="btn btn-primary mt-3" style="background-color: #02245b; border-color: #02245b;">Continuer</a>
                            </div>
                        </div>
                    </div>

                    <!-- Option Propriétaire -->
                    <div class="col-md-5 mb-4">
                        <div class="card border-success h-100 hover-shadow" style="cursor: pointer; transition: 0.3s; border: 2px solid #28a745 !important;">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5" onclick="window.location='{{ route('commercial.biens.create', ['type' => 'proprietaire']) }}'">
                                <div class="icon-box mb-4" style="background: rgba(40, 167, 69, 0.1); padding: 20px; border-radius: 50%;">
                                    <i class="mdi mdi-account text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3 class="font-weight-bold text-success">Pour un Propriétaire</h3>
                                <p class="text-muted text-center px-3">Enregistrer un bien qui appartient directement à un propriétaire.</p>
                                <a href="{{ route('commercial.biens.create', ['type' => 'proprietaire']) }}" class="btn btn-success mt-3">Continuer</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
</style>
@endsection
