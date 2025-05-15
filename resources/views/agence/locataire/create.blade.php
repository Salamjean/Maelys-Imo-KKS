@extends('agence.layouts.template')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Ajout de SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajout d'un nouveau locataire</h4>
            <p class="card-description text-center">Pour l'ajout d'un locataire, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('locataire.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                
                <!-- Section 1: Informations personnelles -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations personnelles</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom <span style="color: red">*</span> </label>
                                <input  type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Nom du locataire" name="name" value="{{ old('name') }}">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prénom <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Prénom du locataire" name="prenom" value="{{ old('prenom') }}">
                                @error('prenom')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
    
                    <!-- Section 2: Contact et profession -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span style="color: red">*</span></label>
                                <input type="email" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Numéro de téléphone" name="contact" value="{{ old('contact') }}">
                                @error('contact')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
               </fieldset>

                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations supplementaires</legend>
                    <!-- Section 3: Adresse -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Profession <span style="color: red">*</span></label>
                            <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Profession" name="profession" value="{{ old('profession') }}">
                            @error('profession')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Adresse complète <span style="color: red">*</span> </label>
                            <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" placeholder="Adresse du locataire" name="adresse" value="{{ old('adresse') }}">
                            @error('adresse')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Documents -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Pièce d'identité <span style="color: red">*</span></label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="piece" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir une image">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                            @error('piece')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Attestation de travail <span style="color: red">*</span></label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="attestation" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('attestation')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Bulletin de salaire</label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="image1" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('image1')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Image 1</label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="image2" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('image2')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Image 2</label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="image3" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('image3')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Image 3</label> <br>
                            <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                            <div class="input-group">
                                <input type="file" name="image4" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('image4')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                </fieldset>

                <!-- Section 5: Sélection du bien -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sélectionner un bien <span style="color: red">*</span></label>
                            <select class="form-control" name="bien_id" style="border: 1px solid black; border-radius: 5px;" id="bien-select-1">
                                <option value="">-- Sélectionnez un bien --</option>
                                @foreach($biens as $bien)
                                    <option value="{{ $bien->id }}" 
                                            data-prix="{{ $bien->prix }}" 
                                            data-caution="{{ $bien->caution }}"
                                            data-avance="{{ $bien->avance }}">
                                        {{ $bien->type }} - {{ $bien->description }} ({{ $bien->prix }} FCFA/mois)
                                    </option>
                                @endforeach
                            </select>
                            @error('bien_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div> <!-- fermeture manquante ajoutée ici -->

                                    <!-- Upload image/PDF -->
                   <div class="col-md-6">
                        <div class="form-group">
                            <label>Contrat de bail <span style="color: red">*</span></label>
                            <div class="input-group">
                                <input type="file" name="contrat" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir des images">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('contrat')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="background-color: #02245b" class="btn btn-primary mr-2 w-full">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Ajout de SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Afficher l'alerte SweetAlert2 si erreur de doublon
    @if ($errors->has('duplicate'))
        Swal.fire({
            icon: 'error',
            title: 'Attention',
            text: '{{ $errors->first('duplicate') }}',
            confirmButtonColor: '#02245b',
            confirmButtonText: 'OK'
        });
    @endif

    // =============================================
    // GESTION DU TÉLÉCHARGEMENT DE FICHIERS
    // =============================================
    
    // Déclenche le click sur l'input file lorsque le bouton est cliqué
    $(document).on('click', '.file-upload-browse', function(e) {
        e.preventDefault();
        let fileInput = $(this).closest('.input-group').find('.file-upload-default');
        fileInput.trigger('click');
    });
    
    // Affiche le nom du fichier sélectionné
    $(document).on('change', '.file-upload-default', function() {
        let fileName = $(this).val().split('\\').pop(); // Meilleure gestion des chemins
        $(this).closest('.input-group').find('.file-upload-info').val(fileName);
        
        // Optionnel: Prévisualisation pour les images
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $(this).closest('.input-group').find('.img-preview').attr('src', e.target.result).show();
            }.bind(this);
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // =============================================
    // GESTION DU TYPE DE BIEN (FORMULAIRE)
    // =============================================
    
    const typeBienSelect = $('select[name="type"]');
    const chambresInput = $('input[name="nombre_de_chambres"]');
    const toilettesInput = $('input[name="nombre_de_toilettes"]');
    const garageSelect = $('select[name="garage"]');
    const avanceInput = $('input[name="avance"]');
    const cautionInput = $('input[name="caution"]');
    const prixLabel = $('label:contains("Montant total (FCFA)")');
    const prixInput = $('input[name="prix"]');
    
    function toggleTerrainFields() {
        const isTerrain = typeBienSelect.val() === 'Terrain';
        
        // Activer/désactiver les champs
        chambresInput.prop('readonly', isTerrain).val(isTerrain ? '' : chambresInput.val());
        toilettesInput.prop('readonly', isTerrain).val(isTerrain ? '' : toilettesInput.val());
        garageSelect.prop('disabled', isTerrain).val(isTerrain ? '' : garageSelect.val());
        avanceInput.prop('readonly', isTerrain).val(isTerrain ? '' : avanceInput.val());
        cautionInput.prop('readonly', isTerrain).val(isTerrain ? '' : cautionInput.val());
        
        // Changer le libellé du prix
        if (isTerrain) {
            prixLabel.text('Prix du terrain (FCFA)');
            prixInput.attr('placeholder', 'Prix du terrain');
        } else {
            prixLabel.text('Montant total (FCFA)');
            prixInput.attr('placeholder', 'Montant total');
        }
    }
    
    // Écouter les changements sur le select
    typeBienSelect.on('change', toggleTerrainFields);
    
    // Appeler la fonction au chargement de la page
    toggleTerrainFields();
    
    // =============================================
    // GESTION DE LA SÉLECTION DU BIEN
    // =============================================
    
    $('#bien-select').change(function() {
        const selectedOption = $(this).find('option:selected');
        const prix = selectedOption.data('prix');
        const caution = selectedOption.data('caution');
        const avance = selectedOption.data('avance');
        
        if (prix) {
            $('#loyer-mensuel').val(prix);
            $('#avance-contrat').val(avance);
            $('#caution-contrat').val(caution || (prix * 2)); // Caution par défaut = 2 mois de loyer
            $('#contrat-details').show();
        } else {
            $('#contrat-details').hide();
        }
    });
});
</script>
@endsection