@extends('proprietaire.layouts.template')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modifier le bien</h4>
            <p class="card-description text-center">Modifiez les informations du bien ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('bien.update.owner', $bien->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Section 1: Informations de base -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du bien</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type de bien <span style="color: red">*</span></label>
                                <select class="form-control" name="type" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Appartement" {{ old('type', $bien->type) == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                                    <option value="Maison" {{ old('type', $bien->type) == 'Maison' ? 'selected' : '' }}>Maison</option>
                                    <option value="Bureau" {{ old('type', $bien->type) == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                </select>
                                @error('type')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Superficie (m²) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Superficie" name="superficie" 
                                       value="{{ old('superficie', $bien->superficie) }}" style="border: 1px solid black; border-radius: 5px;">
                                @error('superficie')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
    
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Commune <span style="color: red">*</span></label>
                                <input type="text" class="form-control" placeholder="Commune" name="commune" 
                                       value="{{ old('commune', $bien->commune) }}" style="border: 1px solid black; border-radius: 5px;">
                                @error('commune')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
    
                    <!-- Section 2: Détails du bien -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de pièce <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Nombre de chambres" 
                                       name="nombre_de_chambres" value="{{ old('nombre_de_chambres', $bien->nombre_de_chambres) }}" 
                                       style="border: 1px solid black; border-radius: 5px;">
                                @error('nombre_de_chambres')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de toilette <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Nombre de toilettes" 
                                       name="nombre_de_toilettes" value="{{ old('nombre_de_toilettes', $bien->nombre_de_toilettes) }}" 
                                       style="border: 1px solid black; border-radius: 5px;">
                                @error('nombre_de_toilettes')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Garage <span style="color: red">*</span></label>
                                <select class="form-control" name="garage" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="">Veuillez selectionner</option>
                                    <option value="Oui" {{ old('garage', $bien->garage) == 'Oui' ? 'selected' : '' }}>Oui</option>
                                    <option value="Non" {{ old('garage', $bien->garage) == 'Non' ? 'selected' : '' }}>Non</option>
                                </select>
                                @error('garage')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type d'utilisation <span style="color: red">*</span></label>
                                <select class="form-control" name="utilisation" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Habitation" {{ old('utilisation', $bien->utilisation) == 'Habitation' ? 'selected' : '' }}>Habitation</option>
                                    <option value="Bureau" {{ old('utilisation', $bien->utilisation) == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                    <option value="Autre" {{ old('utilisation', $bien->utilisation) == 'Autre' ? 'selected' : '' }}>Autre (à préciser)</option>
                                </select>
                                @error('utilisation')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 3: Conditions -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Conditions</legend>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Loyer mensuel <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" placeholder="Montant total" 
                                           name="prix" value="{{ old('prix', $bien->prix) }}" 
                                           style="border: 1px solid black; border-radius: 5px;">
                                </div>
                                @error('prix')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Avance <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" 
                                           placeholder="Entrez le nombre de mois d'avance" 
                                           name="avance" value="{{ old('avance', $bien->avance) }}"
                                           min="1" max="12" maxlength="2"
                                           oninput="this.value=this.value.slice(0,2)"
                                           style="border: 1px solid black; border-radius: 5px;">
                                </div>
                                @error('avance')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Caution <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" 
                                           placeholder="Entrez le nombre de mois de caution" 
                                           name="caution" value="{{ old('caution', $bien->caution) }}"
                                           min="1" max="12" maxlength="2"
                                           oninput="this.value=this.value.slice(0,2)"
                                           style="border: 1px solid black; border-radius: 5px;">
                                </div>
                                @error('caution')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Frais d'agence</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" value="1" name="frais" 
                                           style="border: 1px solid black; border-radius: 5px;" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Montant total</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="montant_total" 
                                           value="{{ old('montant_total', $bien->montant_total) }}"
                                           style="border: 1px solid black; border-radius: 5px;" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date de paiement <span style="color: red">*</span></label>
                                <input type="number" 
                                    class="form-control" 
                                    name="disponibilite" 
                                    min="1" 
                                    max="31" 
                                    placeholder="Jour (1-31)" 
                                    value="{{ old('disponibilite', $bien->date_fixe) }}" 
                                    style="border: 1px solid black; border-radius: 5px;"
                                    required>
                                @error('disponibilite')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

               <!-- Section 4: Documents -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Documents</legend>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo principale du bien</label>
                                <div class="input-group">
                                    <input type="file" name="main_image" class="file-upload-default" 
                                           style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled 
                                           placeholder="{{ $bien->image ? 'Fichier existant' : 'Choisir une image' }}" 
                                           style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                @if($bien->image)
                                    <small class="text-success">Fichier actuel: <a href="{{ asset('storage/'.$bien->image) }}" target="_blank">Voir</a></small>
                                @endif
                                @error('main_image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @for($i = 1; $i <= 5; $i++)
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Photo {{ $i }} supplémentaire</label>
                                    <div class="input-group">
                                        <input type="file" name="additional_images{{ $i }}" class="file-upload-default" 
                                               style="border: 1px solid black; border-radius: 5px;">
                                        <input type="text" class="form-control file-upload-info" disabled 
                                               placeholder="{{ $bien->{'image'.$i} ? 'Fichier existant' : 'Choisir une image' }}" 
                                               style="border: 1px solid black; border-radius: 5px;">
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Changer</button>
                                        </span>
                                    </div>
                                    @if($bien->{'image'.$i})
                                        <small class="text-success">Fichier actuel: <a href="{{ asset('storage/'.$bien->{'image'.$i}) }}" target="_blank">Voir</a></small>
                                    @endif
                                    @error('additional_images'.$i)
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endfor
                    </div>
                </fieldset>

                <!-- Section 5: Description -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description détaillée <span style="color: red">*</span></label>
                            <textarea class="form-control" rows="4" name="description" 
                                      placeholder="Entrez une description complete du bien" 
                                      style="border: 1px solid black; border-radius: 5px;">{{ old('description', $bien->description) }}</textarea>
                            @error('description')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn text-white mr-2" style="background-color: #02245b;">Mettre à jour</button>
                        <a href="{{ route('owner.bienList') }}" class="btn btn-light">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    // Gestion du champ "Type d'utilisation"
    $('select[name="utilisation"]').on('change', function() {
        if ($(this).val() === 'Autre') {
            Swal.fire({
                title: 'Spécifiez le type d\'utilisation',
                input: 'text',
                inputPlaceholder: 'Entrez le type d\'utilisation',
                showCancelButton: true,
                confirmButtonText: 'Valider',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#02245b',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Vous devez entrer un type d\'utilisation!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('input[name="autre_utilisation"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'autre_utilisation',
                        value: result.value
                    }).appendTo('form');
                    
                    $(this).find('option[value="Autre"]').text('Autre (' + result.value + ')');
                } else {
                    $(this).val('{{ old("utilisation", $bien->utilisation) }}').trigger('change');
                }
            });
        }
    });

    // Calcul du montant total
    function calculerMontantTotal() {
        const loyer = parseFloat($('input[name="prix"]').val()) || 0;
        const avance = parseFloat($('input[name="avance"]').val()) || 0;
        const caution = parseFloat($('input[name="caution"]').val()) || 0;
        const frais = 1;
        
        const montantTotal = loyer * (avance + caution + frais);
        
        $('input[name="montant_total"]').val(montantTotal.toFixed(0));
    }
    
    $('input[name="prix"]').on('input', calculerMontantTotal);
    $('input[name="avance"]').on('input', calculerMontantTotal);
    $('input[name="caution"]').on('input', calculerMontantTotal);
    
    calculerMontantTotal();
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Succès!',
        text: '{{ session('success') }}',
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#02245b'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Erreur!',
        text: '{{ session('error') }}',
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#02245b'
    });
</script>
@endif
@endsection