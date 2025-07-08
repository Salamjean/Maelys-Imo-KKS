@extends('agence.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modification du propriétaire {{ $proprietaire->name.' '.$proprietaire->prenom }}</h4>
            <p class="card-description text-center">Pour modifier un propriétaire, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('owner.update.owner', $proprietaire->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du propriétaire</legend>
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('name', $proprietaire->name) }}" placeholder="Nom du propriétaire" name="name">
                                @error('name')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prénom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('prenom', $proprietaire->prenom) }}" placeholder="Prénom du propriétaire" name="prenom">
                                @error('prenom')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('email', $proprietaire->email) }}" placeholder="Email" name="email" readonly>
                                @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Détails supplémentaires -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Lieu de résidence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('commune', $proprietaire->commune) }}" placeholder="Commune" name="commune">
                                @error('commune')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contact</label>
                                <input type="tel" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('contact', $proprietaire->contact) }}" placeholder="Contact" name="contact">
                                @error('contact')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Choix de paiement</label>
                                <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="choix_paiement" id="choix_paiement">
                                    <option value="">Faites un choix</option>
                                    <option value="Virement Bancaire" {{ old('choix_paiement', $proprietaire->choix_paiement) == 'Virement Bancaire' ? 'selected' : '' }}>Virement Bancaire</option>
                                    <option value="Chèques" {{ old('choix_paiement', $proprietaire->choix_paiement) == 'Chèques' ? 'selected' : '' }}>Chèques</option>
                                </select>
                                @error('choix_paiement')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2" id="rib-field">
                            <div class="form-group">
                                <label>RIB</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ old('rib', $proprietaire->rib) }}" name="rib" id="rib">
                                @error('rib')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Pourcentage de l'entreprise</label>
                                <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="pourcentage">
                                    <option value="">Choisissez le pourcentage</option>
                                    @for($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ old('pourcentage', $proprietaire->pourcentage) == $i ? 'selected' : '' }}>{{ $i }}%</option>
                                    @endfor
                                </select>
                                @error('pourcentage')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contrat <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="file" name="contrat" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled 
                                           placeholder="{{ $proprietaire->contrat ? basename($proprietaire->contrat) : 'Choisir un fichier' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @if($proprietaire->contrat)
                                    <small class="text-muted">Fichier actuel: {{ basename($proprietaire->contrat) }}</small>
                                @endif
                                @error('contrat')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Mettre à jour</button>
                        <a href="{{ route('owner.index') }}" class="btn btn-light" style="border: 1px solid black; border-radius: 5px;">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const choixPaiement = document.getElementById('choix_paiement');
    const ribField = document.getElementById('rib');
    const ribContainer = document.getElementById('rib-field');

    // Fonction pour gérer l'état des champs
    function updateFields() {
        if (choixPaiement.value === 'Chèques') {
            ribField.disabled = true;
            ribField.style.backgroundColor = '#e9ecef';
            ribContainer.style.display = 'none';
        } else {
            ribField.disabled = false;
            ribField.style.backgroundColor = '';
            ribContainer.style.display = 'block';
        }
    }

    // Écouteur d'événement pour le changement de sélection
    choixPaiement.addEventListener('change', updateFields);

    // Initialisation au chargement de la page
    updateFields();

    // Gestion de l'upload de fichier
    $(document).on('click', '.file-upload-browse', function(e) {
        e.preventDefault();
        let fileInput = $(this).closest('.input-group').find('.file-upload-default');
        fileInput.trigger('click');
    });
    
    $(document).on('change', '.file-upload-default', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).closest('.input-group').find('.file-upload-info').val(fileName);
    });
});
</script>
@endsection