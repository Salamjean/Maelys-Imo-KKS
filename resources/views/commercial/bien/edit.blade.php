@extends('commercial.layouts.template')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modifier le bien</h4>
            <p class="card-description text-center">Modifiez les informations du bien ci-dessous</p>

            <form class="forms-sample" action="{{ route('commercial.biens.update', $bien->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Section 1: Informations de base -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du bien</legend>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Cible (Agence / Propriétaire)</label>
                                <input type="text" class="form-control" value="{{ $bien->agence ? $bien->agence->nom_agence : ($bien->proprietaire ? $bien->proprietaire->name . ' ' . $bien->proprietaire->prenom : 'N/A') }}" readonly style="border: 1px solid black; border-radius: 5px; background-color: #f8f9fa;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type de bien <span style="color: red">*</span></label>
                                <select class="form-control" name="type" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Appartement" {{ old('type', $bien->type) == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                                    <option value="Maison" {{ old('type', $bien->type) == 'Maison' ? 'selected' : '' }}>Maison</option>
                                    <option value="Bureau" {{ old('type', $bien->type) == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                    <option value="Studio" {{ old('type', $bien->type) == 'Studio' ? 'selected' : '' }}>Studio</option>
                                    <option value="Magasin" {{ old('type', $bien->type) == 'Magasin' ? 'selected' : '' }}>Magasin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Superficie (m²) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Superficie" name="superficie" value="{{ old('superficie', $bien->superficie) }}" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Commune <span style="color: red">*</span></label>
                                <input type="text" class="form-control" placeholder="Commune" name="commune" value="{{ old('commune', $bien->commune) }}" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Détails du bien -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de pièce</label>
                                <input type="number" class="form-control" placeholder="Nombre de chambres" name="nombre_de_chambres" value="{{ old('nombre_de_chambres', $bien->nombre_de_chambres) }}" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de toilette</label>
                                <input type="number" class="form-control" placeholder="Nombre de toilettes" name="nombre_de_toilettes" value="{{ old('nombre_de_toilettes', $bien->nombre_de_toilettes) }}" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Garage</label>
                                <select class="form-control" name="garage" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="">Veuillez selectionner</option>
                                    <option value="Oui" {{ old('garage', $bien->garage) == 'Oui' ? 'selected' : '' }}>Oui</option>
                                    <option value="Non" {{ old('garage', $bien->garage) == 'Non' ? 'selected' : '' }}>Non</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type d'utilisation <span style="color: red">*</span></label>
                                <select class="form-control" name="utilisation" id="utilisation-select" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Habitation" {{ $bien->utilisation == 'Habitation' ? 'selected' : '' }}>Habitation</option>
                                    <option value="Bureau" {{ $bien->utilisation == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                    <option value="Commercial" {{ $bien->utilisation == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                                    <option value="Autre" {{ !in_array($bien->utilisation, ['Habitation', 'Bureau', 'Commercial']) ? 'selected' : '' }}>Autre (à préciser)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 3: Conditions -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Conditions Financières</legend>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Loyer mensuel (FCFA) <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" placeholder="Prix" name="prix" id="prix" value="{{ old('prix', $bien->prix) }}" style="border: 1px solid black; border-radius: 5px;">
                                    <div class="input-group-append">
                                        <span class="input-group-text text-white" style="background-color: #02245b;">FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Avance (Mois) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" name="avance" id="avance" value="{{ old('avance', $bien->avance) }}" min="1" max="99" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Caution (Mois) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" name="caution" id="caution" value="{{ old('caution', $bien->caution) }}" min="1" max="99" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Frais (Mois)</label>
                                <input type="number" class="form-control" value="1" name="frais" id="frais" style="border: 1px solid black; border-radius: 5px;" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total à l'entrée</label>
                                <input type="number" class="form-control" name="montant_total" id="montant_total" value="{{ old('montant_total', $bien->montant_total) }}" style="border: 1px solid black; border-radius: 5px;" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Date paiement <span style="color: red">*</span></label>
                                <input type="text" class="form-control" name="disponibilite" value="{{ old('disponibilite', $bien->date_fixe ?? 5) }}" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 4: Photos -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Photos du bien</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo principale</label>
                                <div class="input-group">
                                    <input type="file" name="main_image" class="file-upload-default" style="display: none;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="{{ $bien->image ? 'Image existante' : 'Choisir une image' }}" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Changer</button>
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <img id="preview-main" src="{{ asset('storage/' . $bien->image) }}" style="height: 100px; border-radius: 5px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                        @for($i = 1; $i <= 5; $i++)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Photo suppl. {{ $i }}</label>
                                    <div class="input-group">
                                        <input type="file" name="additional_images{{ $i }}" class="file-upload-default" style="display: none;">
                                        <input type="text" class="form-control file-upload-info" disabled placeholder="{{ $bien->{'image' . $i} ? 'Image existante' : 'Choisir une image' }}" style="border: 1px solid black; border-radius: 5px;">
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Changer</button>
                                        </span>
                                    </div>
                                    @if($bien->{'image' . $i})
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $bien->{'image' . $i}) }}" style="height: 100px; border-radius: 5px; border: 1px solid #ddd;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Vidéo 3D / Visite Virtuelle (Lien ou Iframe)</label>
                                <textarea name="video_3d" class="form-control" rows="3" placeholder="Lien YouTube, Matterport, etc." style="border: 1px solid black; border-radius: 5px;">{{ old('video_3d', $bien->video_3d) }}</textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 5: Description -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description détaillée <span style="color: red">*</span></label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Description du bien" style="border: 1px solid black; border-radius: 5px;">{{ old('description', $bien->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn text-white px-5" style="background-color: #02245b;">Mettre à jour le bien</button>
                    <a href="{{ route('commercial.biens.index') }}" class="btn btn-light ml-2">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // File upload trigger
        $(document).on('click', '.file-upload-browse', function() {
            $(this).closest('.form-group').find('.file-upload-default').trigger('click');
        });

        $(document).on('change', '.file-upload-default', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).closest('.input-group').find('.file-upload-info').val(fileName);
        });

        // Calculation
        function calculateTotal() {
            let prix = parseFloat($('#prix').val()) || 0;
            let avance = parseFloat($('#avance').val()) || 0;
            let caution = parseFloat($('#caution').val()) || 0;
            let frais = 1;
            $('#montant_total').val(prix * (avance + caution + frais));
        }
        $('#prix, #avance, #caution').on('input', calculateTotal);
        calculateTotal();

        // Autre utilisation
        $('#utilisation-select').on('change', function() {
            if ($(this).val() === 'Autre') {
                Swal.fire({
                    title: 'Spécifiez l\'utilisation',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonColor: '#02245b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('<input>').attr({type: 'hidden', name: 'autre_utilisation', value: result.value}).appendTo('form');
                        $(this).find('option[value="Autre"]').text('Autre (' + result.value + ')');
                    } else {
                        $(this).val('Habitation');
                    }
                });
            }
        });
    });
</script>
@endsection
