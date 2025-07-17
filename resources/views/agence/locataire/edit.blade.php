@extends('agence.layouts.template')
@section('content') 
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modification du locataire</h4>
            <p class="card-description text-center">Modifiez les informations du locataire ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('locataire.update', $locataire->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Section 1: Informations personnelles -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations personnelles</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Nom du locataire" name="name" value="{{ old('name', $locataire->name) }}">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prénom <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Prénom du locataire" name="prenom" value="{{ old('prenom', $locataire->prenom) }}">
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
                                <input type="email" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Email" name="email" value="{{ old('email', $locataire->email) }}">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Numéro de téléphone" name="contact" value="{{ old('contact', $locataire->contact) }}">
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
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Profession" name="profession" value="{{ old('profession', $locataire->profession) }}">
                                @error('profession')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Adresse complète <span style="color: red">*</span></label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Adresse du locataire" name="adresse" value="{{ old('adresse', $locataire->adresse) }}">
                                @error('adresse')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Statut et motif -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Statut</label>
                                <select class="form-control" name="status" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Actif" {{ old('status', $locataire->status) == 'Actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="Inactif" {{ old('status', $locataire->status) == 'Inactif' ? 'selected' : '' }}>Inactif</option>
                                    <option value="Pas sérieux" {{ old('status', $locataire->status) == 'Pas sérieux' ? 'selected' : '' }}>Pas sérieux</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Motif (si Inactif ou Pas sérieux)</label>
                                <input type="text" class="form-control" style="border: 1px solid black; border-radius: 5px;" 
                                       placeholder="Motif" name="motif" value="{{ old('motif', $locataire->motif) }}">
                                @error('motif')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Documents -->
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Pièce d'identité</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="piece" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->piece ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->piece)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->piece) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('piece')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Attestation de travail</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="attestation" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->attestation ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->attestation)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->attestation) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('attestation')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Bulletin de salaire</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="image1" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->image1 ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->image1)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->image1) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('image1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Image 1</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="image2" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->image2 ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->image2)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->image2) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('image2')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Image 2</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="image3" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->image3 ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->image3)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->image3) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('image3')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Image 3</label> <br>
                                <small class="text-muted">Format acceptés: jpeg, png, jpg, gif (max 2MB)</small>
                                <div class="input-group">
                                    <input type="file" name="image4" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                           disabled placeholder="{{ $locataire->image4 ? 'Fichier existant' : 'Choisir une image' }}">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($locataire->image4)
                                    <div class="mt-2">
                                        <a href="{{ asset('storage/'.$locataire->image4) }}" 
                                           class="btn btn-sm btn-info" target="_blank">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </a>
                                    </div>
                                @endif
                                @error('image4')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 6: Sélection du bien -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Sélectionner un bien <span style="color: red">*</span></label>
                            <select class="form-control" name="bien_id" style="border: 1px solid black; border-radius: 5px;" id="bien-select">
                                <option value="">-- Sélectionnez un bien --</option>
                                @foreach($biens as $bien)
                                    <option value="{{ $bien->id }}" 
                                            {{ old('bien_id', $locataire->bien_id) == $bien->id ? 'selected' : '' }}
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
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contrat de bail</label> 
                            <div class="input-group">
                                <input type="file" name="contrat" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" 
                                       disabled placeholder="{{ $locataire->contrat ? 'Fichier existant' : 'Choisir un fichier' }}">
                                <span class="input-group-append">
                                    <button class="file-upload-browse btn btn-primary" style="background-color: #02245b" type="button">Changer</button>
                                </span>
                            </div>
                            @if($locataire->contrat)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/'.$locataire->contrat) }}" 
                                       class="btn btn-sm btn-info" target="_blank">
                                        <i class="mdi mdi-eye"></i> Voir
                                    </a>
                                    <a href="{{ route('locataires.downloadContrat', $locataire->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="mdi mdi-download"></i> Télécharger
                                    </a>
                                </div>
                            @endif
                            @error('contrat')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="background-color: #02245b" class="btn btn-primary mr-2 w-full">Mettre à jour</button>
                        <a href="{{ route('locataire.index') }}" class="btn btn-light">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Afficher l'alerte SweetAlert2 si erreur de doublon
    @if ($errors->has('duplicate'))
        Swal.fire({
            icon: 'error',
            title: 'Doublon détecté',
            text: '{{ $errors->first('duplicate') }}',
            confirmButtonColor: '#02245b',
            confirmButtonText: 'OK'
        });
    @endif

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

    // Gestion de la sélection du bien
    $('#bien-select').change(function() {
        const selectedOption = $(this).find('option:selected');
        const prix = selectedOption.data('prix');
        const caution = selectedOption.data('caution');
        const avance = selectedOption.data('avance');
        
        if (prix) {
            $('#loyer-mensuel').val(prix);
            $('#avance-contrat').val(avance);
            $('#caution-contrat').val(caution || (prix * 2));
        }
    });
});
</script>
@endsection