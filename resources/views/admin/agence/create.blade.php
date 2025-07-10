@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajout d'une agence partenaire</h4>
            <p class="card-description text-center" style="color:red">Pour l'ajout de l'agence, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('agence.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations de l'agence</legend>
                <!-- Section 1: Informations de base -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Nom de l'agence</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" value="{{ old('name') }}" class="form-control" placeholder="Nom de l'agence" name="name">
                            @error('name')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" style="border: 1px solid black; border-radius: 5px;" value="{{ old('email') }}"  class="form-control" placeholder="Email" name="email">
                            @error('email')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                     <div class="col-md-2">
                        <div class="form-group">
                            <label>Commune</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;"  value="{{ old('commune') }}"  class="form-control" placeholder="Commune" name="commune">
                            @error('commune')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                     <div class="col-md-2">
                        <div class="form-group">
                            <label>Contact de l'agence</label>
                            <input type="number" style="border: 1px solid black; border-radius: 5px;"  value="{{ old('contact') }}"  class="form-control" placeholder="Contact de l'agence" name="contact">
                            @error('contact')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Adresse de l'agence</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;"  value="{{ old('adresse') }}"  class="form-control" placeholder="Adresse" name="adresse">
                            @error('adresse')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>RIB</label>
                            <div class="input-group">
                                <input type="file" name="rib" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le RIB" value="{{ old('rib') }}">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('rib')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Section 2: Détails du bien -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>N° RCCM</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;"  value="{{ old('rccm') }}"  class="form-control" placeholder="Numéro du registre de commerce" name="rccm">
                            @error('rccm')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fiche RCCM</label>
                            <div class="input-group">
                                <input type="file" name="rccm_file" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le fichier du registre du commerce" value="{{ old('rccm_file') }}">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02235b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('rccm_file')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>N° DFE</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;"  value="{{ old('dfe') }}"  class="form-control" placeholder="Numéro de déclaration fiscale d'existance" name="dfe">
                            @error('dfe')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fiche du DFE</label>
                            <div class="input-group">
                                <input type="file" name="dfe_file" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden multiple>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le fichier DFE" value="{{ old('dfe_file') }}">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                </span>
                            </div>
                        </div>
                        @error('dfe_file')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</fieldset>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Ajout de SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
</script>
@endsection