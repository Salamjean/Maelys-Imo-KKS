@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajouter un bien</h4>
            <p class="card-description text-center">Pour l'ajout d'un bien, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('bien.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <!-- Section 1: Informations de base -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du bien</legend>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Sélectionnez le propriétaire<span style="color: red">*</span></label>
                                <select class="form-control" name="proprietaire_id" required style="border: 1px solid black; border-radius: 5px;">
                                    <option value="">Choisir un propriétaire</option>
                                    <option value="">Maelys-Imo</option>
                                    @foreach($proprietaires as $proprietaire)
                                        <option value="{{ $proprietaire->code_id }}">
                                            {{ $proprietaire->name }} {{ $proprietaire->prenom }} - {{ $proprietaire->contact }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('proprietaire_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type de bien <span style="color: red">*</span></label>
                                <select class="form-control" name="type" style="border: 1px solid black; border-radius: 5px;">
                                    <option value="Appartement">Appartement</option>
                                    <option value="Maison">Maison</option>
                                    <option value="Bureau">Bureau</option>
                                </select>
                            </div>
                            @error('type')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Superficie (m²) <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Superficie" value="{{ old('superficie') }}" name="superficie" style="border: 1px solid black; border-radius: 5px;">
                                @error('superficie')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Commune <span style="color: red">*</span></label>
                                <input type="text" class="form-control" placeholder="Commune" value="{{ old('commune') }}" name="commune" style="border: 1px solid black; border-radius: 5px;">
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
                                <label>Nombre de pièces <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Nombre de chambres" value="{{ old('nombre_de_chambres') }}" name="nombre_de_chambres" style="border: 1px solid black; border-radius: 5px;">
                                @error('nombre_de_chambres')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Nombre de toilettes <span style="color: red">*</span></label>
                                <input type="number" class="form-control" placeholder="Nombre de toilettes" value="{{ old('nombre_de_toilettes') }}" name="nombre_de_toilettes" style="border: 1px solid black; border-radius: 5px;">
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
                                    <option value="Oui">Oui</option>
                                    <option value="Non">Non</option>
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
                                    <option value="Habitation">Habitation</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Autre">Autre (à préciser)</option>
                                </select>
                            </div>
                            @error('utilisation')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Section 3: Conditions -->
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Conditions</legend>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Loyer mensuel</label><span style="color: red">*</span>
                                <div class="input-group">
                                    <input type="number" class="form-control" placeholder="Montant total" value="{{ old('prix') }}" name="prix" style="border: 1px solid black; border-radius: 5px;">
                                    
                                </div>
                                @error('prix')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Avance  <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" 
                                           placeholder="Entrez le nombre de mois d'avance" 
                                           name="avance" 
                                           min="1" 
                                           max="99" 
                                           value="{{ old('avance') }}"
                                           maxlength="2"
                                           oninput="this.value=this.value.slice(0,2)"
                                           style="border: 1px solid black; border-radius: 5px;">
                                </div>
                            </div>
                            @error('avance')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Caution <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" 
                                           placeholder="Entrez le nombre de mois de caution" 
                                           name="caution" 
                                           min="1" 
                                           max="99" 
                                           value="{{ old('caution') }}"
                                           maxlength="2"
                                           oninput="this.value=this.value.slice(0,2)"
                                           style="border: 1px solid black; border-radius: 5px;">
                                </div>
                            </div>
                            @error('caution')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Frais d'agence<span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" value="1" name="frais" style="border: 1px solid black; border-radius: 5px;" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Montant total</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="montant_total" value="{{ old('montant_total') }}" style="border: 1px solid black; border-radius: 5px;" readonly>
                                    
                                </div>
                            </div>
                            @error('montant_total')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

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
                                    value="{{ old('disponibilite') }}" 
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
                    <legend style="font-size: 1.5em; font-weight: bold;">Documents à fournir</legend>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo principale du bien <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="file" name="main_image" class="file-upload-default" required style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @error('main_image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo 1 supplémentaires  <span style="color: red">*</span></label>
                                <div class="input-group">
                                    <input type="file" name="additional_images1" class="file-upload-default" required style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @error('additional_images1')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo 2 supplémentaires </label>
                                <div class="input-group">
                                    <input type="file" name="additional_images2" class="file-upload-default" style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @error('additional_images2')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo 3 supplémentaires </label>
                                <div class="input-group">
                                    <input type="file" name="additional_images3" class="file-upload-default" style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                                @error('additional_images3')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo 4 supplémentaires </label>
                                <div class="input-group">
                                    <input type="file" name="additional_images4" class="file-upload-default" style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                            </div>
                            @error('additional_images4')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Photo 5 supplémentaires </label>
                                <div class="input-group">
                                    <input type="file" name="additional_images5" class="file-upload-default" style="border: 1px solid black; border-radius: 5px;">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image" style="border: 1px solid black; border-radius: 5px;">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn text-white" style="background-color:#02245b" type="button">Télécharger</button>
                                    </span>
                                </div>
                            </div>
                            @error('additional_images5')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Section 5: Description -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description détaillée <span style="color: red">*</span></label>
                            <textarea class="form-control" rows="4" name="description" value="{{ old('description') }}" placeholder="Entrez une description complete du bien" style="border: 1px solid black; border-radius: 5px;"></textarea>
                        </div>
                        @error('description')
                                <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn text-white mr-2" style="background-color: #02245b; color:white">Enregistrer</button>
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
    // Gestion du champ "Type d'utilisation"
    $('select[name="utilisation"]').on('change', function() {
        if ($(this).val() === 'Autre') {
            // Afficher un popup demandant de spécifier le type d'utilisation
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
                    // Créer un champ caché pour stocker la valeur
                    $('input[name="autre_utilisation"]').remove();
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'autre_utilisation',
                        value: result.value
                    }).appendTo('form');
                    
                    // Mettre à jour le select pour afficher "Autre (valeur spécifiée)"
                    $(this).find('option[value="Autre"]').text('Autre (' + result.value + ')');
                } else {
                    // Revenir à la valeur par défaut si l'utilisateur annule
                    $(this).val('Habitation').trigger('change');
                }
            });
        }
    });

    // Modifier le formulaire pour prendre en compte la valeur "autre_utilisation" lors de la soumission
    $('form').on('submit', function(e) {
        const utilisation = $('select[name="utilisation"]').val();
        const autreUtilisation = $('input[name="autre_utilisation"]').val();
        
        if (utilisation === 'Autre' && !autreUtilisation) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Veuillez spécifier le type d\'utilisation',
                confirmButtonColor: '#02245b'
            });
        }
    });
});
</script>
<script>
    $(document).ready(function() {
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
        const prixLabel = $('label:contains("Loyer mensuel (FCFA)")');
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
                prixLabel.text('Loyer mensuel (FCFA)');
                prixInput.attr('placeholder', 'Loyer mensuel');
            }
        }
        
        // Écouter les changements sur le select
        typeBienSelect.on('change', toggleTerrainFields);
        
        // Appeler la fonction au chargement de la page
        toggleTerrainFields();
        
        // =============================================
        // CALCUL DU MONTANT TOTAL (Loyer * (avance + caution + frais))
        // =============================================
        
        function calculerMontantTotal() {
            const loyer = parseFloat(prixInput.val()) || 0;
            const avance = parseFloat(avanceInput.val()) || 0;
            const caution = parseFloat(cautionInput.val()) || 0;
            const frais = 1; // Frais d'agence fixés à 1 mois
            
            const montantTotal = loyer * (avance + caution + frais);
            
            $('input[name="montant_total"]').val(montantTotal.toFixed(0));
        }
        
        // Écouter les changements sur les champs concernés
        prixInput.on('input', calculerMontantTotal);
        avanceInput.on('input', calculerMontantTotal);
        cautionInput.on('input', calculerMontantTotal);
        
        // =============================================
        // AMÉLIORATIONS OPTIONNELLES
        // =============================================
        
        // Empêcher la soumission du formulaire si les champs requis ne sont pas remplis
        $('form').on('submit', function(e) {
            let isValid = true;
            
            // Vérifier les images obligatoires
            if ($('input[name="main_image"]').val() === '') {
                alert('Veuillez sélectionner une image principale');
                isValid = false;
            }
            
            if ($('input[name="additional_images1"]').val() === '') {
                alert('Veuillez sélectionner au moins une image supplémentaire');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Réinitialiser le champ fichier
        $('.file-upload-reset').on('click', function() {
            $(this).closest('.input-group').find('.file-upload-default').val('');
            $(this).closest('.input-group').find('.file-upload-info').val('');
            $(this).closest('.input-group').find('.img-preview').hide();
        });
    });


    // Dans votre section script
    $(document).ready(function() {
        // Initialiser Select2 pour une meilleure recherche
        $('select[name="proprietaire_id"]').select2({
            placeholder: "Rechercher un propriétaire",
            allowClear: true
        });

        // Optionnel: Ajouter un bouton pour créer un nouveau propriétaire
        $('select[name="proprietaire_id"]').after(
            '<a href="{{ route("owner.create") }}" class="btn btn-sm btn-outline-primary mt-2">' +
            '<i class="fas fa-plus"></i> Ajouter un nouveau propriétaire' +
            '</a>'
        );
    });
    </script>

    
    
@endsection