@extends('agence.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajout d'un propriétaire de bien</h4>
            <p class="card-description text-center">Pour l'ajout d'un propriétaire, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('owner.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du propriétaire</legend>
                <!-- Section 1: Informations de base -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nom du propriétaire</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Nom du comptable" name="name">
                            @error('name')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Prénom du propriétaire</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Prénom du comptable" name="prenom">
                            @error('prenom')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" style="border: 1px solid black; border-radius: 5px;"  class="form-control" placeholder="Email" name="email">
                            @error('email')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Détails du bien -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Lieu de résidence</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Commune" name="commune">
                            @error('commune')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Contact</label>
                            <input type="tel" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Contact du comptable" name="contact">
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
                                <option value="RIB">RIB</option>
                                <option value="Mobile money">Mobile money</option>
                            </select>
                            @error('choix_paiement')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>RIB</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" name="rib" id="rib">
                            @error('rib')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Pourcentage </label>
                            <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="pourcentage">
                                <option value="">Choissisez le pourcentage</option>
                                <option value="5">5%</option>
                                <option value="10">10%</option>
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
                                <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Choisir un fichier">
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
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</fieldset>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const choixPaiement = document.getElementById('choix_paiement');
    const ribField = document.getElementById('rib');
    const banqueField = document.getElementById('banque');

    // Fonction pour gérer l'état des champs
    function updateFields() {
        if (choixPaiement.value === 'Mobile money') {
            ribField.disabled = true;
            ribField.style.backgroundColor = '#e9ecef';
            banqueField.disabled = true;
            banqueField.style.backgroundColor = '#e9ecef';
        } else {
            ribField.disabled = false;
            ribField.style.backgroundColor = '';
            banqueField.disabled = false;
            banqueField.style.backgroundColor = '';
        }
    }

    // Écouteur d'événement pour le changement de sélection
    choixPaiement.addEventListener('change', updateFields);

    // Initialisation au chargement de la page
    updateFields();
});

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