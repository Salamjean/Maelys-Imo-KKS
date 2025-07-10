@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modification de l'agence partenaire</h4>
            <p class="card-description text-center" style="color:red">Modifiez les informations de l'agence ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('agence.update', $agence->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations de l'agence</legend>
                    
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Nom de l'agence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Nom de l'agence" 
                                       name="name" value="{{ old('name', $agence->name) }}">
                                @error('name')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Email" 
                                       name="email" value="{{ old('email', $agence->email) }}">
                                @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Commune</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Commune" 
                                       name="commune" value="{{ old('commune', $agence->commune) }}">
                                @error('commune')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Contact de l'agence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Contact de l'agence" 
                                       name="contact" value="{{ old('contact', $agence->contact) }}">
                                @error('contact')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Adresse complète</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Adresse" 
                                       name="adresse" value="{{ old('adresse', $agence->adresse) }}">
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
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le RIB">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @if($agence->rib)
                                    <small class="text-muted">Fichier actuel: {{ basename($agence->rib) }}</small>
                                @endif
                                @error('rib')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Documents officiels -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>N° RCCM</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Numéro du registre de commerce" 
                                       name="rccm" value="{{ old('rccm', $agence->rccm) }}">
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
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le fichier du registre du commerce">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02235b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @if($agence->rccm_file)
                                    <small class="text-muted">Fichier actuel: {{ basename($agence->rccm_file) }}</small>
                                @endif
                                @error('rccm_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>N° DFE</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" 
                                       class="form-control" placeholder="Numéro de déclaration fiscale d'existance" 
                                       name="dfe" value="{{ old('dfe', $agence->dfe) }}">
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
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le fichier DFE">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @if($agence->dfe_file)
                                    <small class="text-muted">Fichier actuel: {{ basename($agence->dfe_file) }}</small>
                                @endif
                                @error('dfe_file')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Boutons de soumission -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" style="border: 1px solid black; border-radius: 5px;" 
                                    class="btn btn-primary mr-2">Mettre à jour</button>
                            <a href="{{ route('agence.index') }}" class="btn btn-light">Annuler</a>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Gestion du téléchargement de fichiers
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