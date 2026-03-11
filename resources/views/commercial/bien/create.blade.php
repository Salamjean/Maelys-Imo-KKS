@extends('commercial.layouts.template')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajouter un bien pour {{ $type === 'agence' ? 'une Agence' : 'un Propriétaire' }}</h4>
            <p class="card-description text-center">Remplissez les informations ci-dessous pour enregistrer le bien</p>

            <form class="forms-sample" action="{{ route('commercial.biens.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="target_type" value="{{ $type }}">

                <!-- Section 1: Informations de base -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du bien</legend>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sélectionnez {{ $type === 'agence' ? 'l\'agence' : 'le propriétaire' }}<span style="color: red">*</span></label>
                                <select class="form-control select2" name="target_id" required style="border: 1px solid black; border-radius: 5px; width: 100%;">
                                    <option value="">Choisir...</option>
                                    @foreach($entites as $entite)
                                        <option value="{{ $entite->code_id }}">
                                            @if($type === 'agence')
                                                {{ $entite->name }} ({{ $entite->code_id }})
                                            @else
                                                {{ $entite->name }} {{ $entite->prenom }} ({{ $entite->code_id }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type de bien <span style="color: red">*</span></label>
                                <select class="form-control" name="type" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Appartement">Appartement</option>
                                    <option value="Maison">Maison</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Studio">Studio</option>
                                    <option value="Magasin">Magasin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Superficie (m²) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Superficie" name="superficie" style="border: 1px solid black; border-radius: 5px;" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Commune <span style="color: red">*</span></label>
                                <input type="text" class="form-control" placeholder="Commune" name="commune" style="border: 1px solid black; border-radius: 5px;" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Détails -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de pièces</label>
                                <input type="number" class="form-control" name="nombre_de_chambres" placeholder="Ex: 4" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de toilettes</label>
                                <input type="number" class="form-control" name="nombre_de_toilettes" placeholder="Ex: 2" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Garage</label>
                                <select class="form-control" name="garage" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Oui">Oui</option>
                                    <option value="Non" selected>Non</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type d'utilisation <span style="color: red">*</span></label>
                                <select class="form-control" name="utilisation" id="utilisation-select" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Habitation">Habitation</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Commercial">Commercial</option>
                                    <option value="Autre">Autre (à préciser)</option>
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
                                <label>Loyer (FCFA) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" name="prix" id="prix" style="border: 1px solid black; border-radius: 5px;" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Avance (Mois) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" name="avance" id="avance" value="1" min="1" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Caution (Mois) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" name="caution" id="caution" value="1" min="1" style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Frais (Mois)</label>
                                <input type="number" class="form-control" value="1" name="frais" id="frais" readonly style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Total à l'entrée</label>
                                <input type="text" class="form-control" name="montant_total" id="montant_total" readonly style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Jour de paiement</label>
                                <input type="number" class="form-control" name="disponibilite" value="5" min="1" max="31" style="border: 1px solid black; border-radius: 5px;">
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
                                <label>Photo principale <span style="color: red">*</span></label>
                                <input type="file" name="main_image" class="form-control" required style="border: 1px solid black; border-radius: 5px;">
                            </div>
                        </div>
                        @for($i=1; $i<=5; $i++)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Photo suppl. {{ $i }} {{ $i==1 ? '*' : '' }}</label>
                                    <input type="file" name="additional_images{{ $i }}" class="form-control" {{ $i==1 ? 'required' : '' }} style="border: 1px solid black; border-radius: 5px;">
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Vidéo 3D / Visite Virtuelle (Lien ou Iframe)</label>
                                <textarea name="video_3d" class="form-control" rows="3" placeholder="Lien YouTube, Matterport, etc." style="border: 1px solid black; border-radius: 5px;"></textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Section 5: Description -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description détaillée <span style="color: red">*</span></label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Description du bien" style="border: 1px solid black; border-radius: 5px;" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn text-white px-5" style="background-color: #02245b;">Enregistrer le bien</button>
                    <a href="{{ route('commercial.biens.choice') }}" class="btn btn-light ml-2">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ placeholder: "Rechercher...", allowClear: true });
        }

        function calculateTotal() {
            let prix = parseFloat($('#prix').val()) || 0;
            let avance = parseFloat($('#avance').val()) || 0;
            let caution = parseFloat($('#caution').val()) || 0;
            let frais = 1;
            $('#montant_total').val(prix * (avance + caution + frais));
        }
        $('#prix, #avance, #caution').on('input', calculateTotal);
        
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
