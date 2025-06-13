@extends('agence.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modifier les informations du propriétaire {{ $proprietaire->name.' '.$proprietaire->prenom }}</h4>
            <p class="card-description text-center">Pour modifier le propriétaire, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('owner.update.owner', $proprietaire->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du propriétaire</legend>
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->name }}" placeholder="Nom du propriétaire" name="name">
                                @error('name')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Prénom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->prenom }}" placeholder="Prénom du propriétaire" name="prenom">
                                @error('prenom')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" style="border: 1px solid black; border-radius: 5px;" readonly class="form-control" value="{{ $proprietaire->email }}" placeholder="Email" name="email">
                                @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Lieu de résidence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->commune }}" placeholder="Commune" name="commune">
                                @error('commune')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Détails du bien -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact </label>
                                <input type="number" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->contact }}" placeholder="Contact du propriétaire" name="contact">
                                @error('contact')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Choix de paiement</label>
                                <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="choix_paiement" id="choix_paiement">
                                    <option value="">Faites un choix</option>
                                    <option value="RIB" {{ $proprietaire->choix_paiement == 'RIB' ? 'selected' : '' }}>RIB</option>
                                    <option value="Mobile money" {{ $proprietaire->choix_paiement == 'Mobile money' ? 'selected' : '' }}>Mobile money</option>
                                </select>
                                @error('choix_paiement')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3" id="rib-field" style="display: {{ $proprietaire->choix_paiement == 'RIB' ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label>RIB</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('rib', $proprietaire->rib) }}" name="rib">
                                @error('rib')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Pourcentage</label>
                                <select class="form-control" name="pourcentage" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="">Choisissez le pourcentage</option>
                                    <option value="5" {{ $proprietaire->pourcentage == '5' ? 'selected' : '' }}>5%</option>
                                    <option value="10" {{ $proprietaire->pourcentage == '10' ? 'selected' : '' }}>10%</option>
                                </select>
                                @error('pourcentage')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion affichage champ RIB
    const choixPaiement = document.getElementById('choix_paiement');
    const ribField = document.getElementById('rib-field');
    
    choixPaiement.addEventListener('change', function() {
        ribField.style.display = this.value === 'RIB' ? 'block' : 'none';
    });

    // Gestion upload fichier
    $('.file-upload-browse').on('click', function() {
        const file = $(this).parent().parent().find('.file-upload-default');
        file.trigger('click');
    });
    
    $('.file-upload-default').on('change', function() {
        $(this).parent().find('.file-upload-info').val(
            this.files.length ? this.files[0].name : ''
        );
    });
});
</script>
@endsection
@endsection