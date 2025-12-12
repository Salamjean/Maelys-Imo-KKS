@extends('agence.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajout d'un propriétaire de bien</h4>
            <p class="card-description text-center">Veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            {{-- Affichage global des erreurs --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="forms-sample" action="{{ route('owner.store') }}" method="POST" enctype="multipart/form-data" id="ownerForm">
                @csrf
                
                <fieldset style="border: 2px solid #02245b; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.2em; font-weight: bold; color: #02245b; padding: 0 10px;">Informations Personnelles</legend>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="prenom" value="{{ old('prenom') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Contact <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="contact" value="{{ old('contact') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Lieu de résidence <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="commune" value="{{ old('commune') }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo de profil</label>
                                <input type="file" name="profil_image" class="file-upload-default" accept="image/*" hidden>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir une image">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="background-color: #02245b">Upload</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset style="border: 2px solid #02245b; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.2em; font-weight: bold; color: #02245b; padding: 0 10px;">Détails Financiers & Administratifs</legend>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Pourcentage Agence (%) <span class="text-danger">*</span></label>
                                <select class="form-control" name="pourcentage" required>
                                    <option value="">Sélectionner</option>
                                    @for($i = 1; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ old('pourcentage') == $i ? 'selected' : '' }}>{{ $i }}%</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Mode de paiement <span class="text-danger">*</span></label>
                                <select class="form-control" name="choix_paiement" id="choix_paiement" required>
                                    <option value="">Sélectionner</option>
                                    <option value="Virement Bancaire" {{ old('choix_paiement') == 'Virement Bancaire' ? 'selected' : '' }}>Virement Bancaire</option>
                                    <option value="Chèques" {{ old('choix_paiement') == 'Chèques' ? 'selected' : '' }}>Chèques</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>RIB Bancaire <span class="text-danger" id="rib-star">*</span></label>
                                <input type="text" class="form-control" name="rib" id="rib" value="{{ old('rib') }}" placeholder="Entrez le RIB">
                                <small class="text-muted" id="rib-help" style="display:none;">Non requis pour le paiement par chèque</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contrat de gestion (PDF/Image) <span class="text-danger">*</span></label>
                                <input type="file" name="contrat" class="file-upload-default" accept=".pdf,.jpg,.jpeg,.png" hidden>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir le contrat">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="background-color: #02245b">Upload</button>
                                    </span>
                                </div>
                                <small class="text-muted">Max 5Mo</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pièce d'identité (CNI) (PDF/Image) <span class="text-danger">*</span></label>
                                <input type="file" name="cni" class="file-upload-default" accept=".pdf,.jpg,.jpeg,.png" hidden>
                                <div class="input-group col-xs-12">
                                    <input type="text" class="form-control file-upload-info" disabled placeholder="Choisir la CNI">
                                    <span class="input-group-append">
                                        <button class="file-upload-browse btn btn-primary" type="button" style="background-color: #02245b">Upload</button>
                                    </span>
                                </div>
                                <small class="text-muted">Max 5Mo</small>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary btn-lg" style="background-color: #02245b; min-width: 200px;">
                            <i class="mdi mdi-content-save"></i> Enregistrer le Propriétaire
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // 1. Gestion des fichiers (Upload Customisé)
    // Au clic sur le bouton "Upload", on clique sur l'input file caché
    $('.file-upload-browse').on('click', function() {
        var fileInput = $(this).parents('.form-group').find('.file-upload-default');
        fileInput.trigger('click');
    });

    // Quand un fichier est choisi, on met son nom dans le champ texte
    $('.file-upload-default').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if(fileName){
             $(this).parent().find('.file-upload-info').val(fileName);
        } else {
             // Si l'utilisateur annule, on garde potentiellement l'ancien texte ou on vide
        }
    });

    // 2. Gestion Paiement (RIB)
    const choixPaiement = $('#choix_paiement');
    const ribField = $('#rib');
    const ribStar = $('#rib-star');
    const ribHelp = $('#rib-help');

    function updatePaiementState() {
        if (choixPaiement.val() === 'Chèques') {
            ribField.prop('disabled', true);
            ribField.prop('required', false); // Important pour la validation HTML5
            ribField.val(''); // On vide le champ visuellement
            ribField.addClass('bg-secondary text-white'); // Style grisé
            ribStar.hide();
            ribHelp.show();
        } else {
            ribField.prop('disabled', false);
            // Si c'est vide ou Virement, on le rend requis
            if(choixPaiement.val() === 'Virement Bancaire') {
                ribField.prop('required', true);
                ribStar.show();
            } else {
                 ribField.prop('required', false);
                 ribStar.hide();
            }
            ribField.removeClass('bg-secondary text-white');
            ribHelp.hide();
        }
    }

    // Écouteur et initialisation
    choixPaiement.on('change', updatePaiementState);
    updatePaiementState(); // Lancer au chargement

    // 3. Prévention soumission double (optionnel mais recommandé)
    $('#ownerForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('Enregistrement en cours...');
    });
});
</script>

<style>
    /* Style pour améliorer les inputs */
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 4px;
        height: 45px; /* Inputs plus hauts */
    }
    .form-control:focus {
        border-color: #02245b;
        box-shadow: 0 0 0 0.2rem rgba(2, 36, 91, 0.25);
    }
    label {
        font-weight: 500;
        margin-bottom: 8px;
    }
</style>
@endsection