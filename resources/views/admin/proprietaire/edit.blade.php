@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modifier les informations du propriétaire {{ $proprietaire->name.' '.$proprietaire->prenom }}</h4>
            <p class="card-description text-center">Pour modifier le propriétaire, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('owner.update.admin', $proprietaire->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du propriétaire</legend>
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" readonly class="form-control" value="{{ $proprietaire->name }}" placeholder="Nom du propriétaire" name="name">
                                @error('name')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prénom du propriétaire</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" readonly class="form-control" value="{{ $proprietaire->prenom }}" placeholder="Prénom du propriétaire" name="prenom">
                                @error('prenom')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->email }}" readonly name="email">
                                @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Détails du bien -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Lieu de résidence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->commune }}" placeholder="Commune" name="commune">
                                @error('commune')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Contact du propriétaire</label>
                                <input type="number" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $proprietaire->contact }}" placeholder="Contact du propriétaire" name="contact">
                                @error('contact')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label>RIB</label>
                                <div class="input-group">
                                    <input type="file" name="rib" style="border: 1px solid black; border-radius: 5px;" class="file-upload-default" hidden>
                                    <input type="text" class="form-control file-upload-info" style="border: 1px solid black; border-radius: 5px;" disabled placeholder="Télécharger le RIB"  value="{{ $proprietaire->rib }}">
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

                    <!-- Section 3: Gestion des biens -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" name="diaspora" value="1" {{ $proprietaire->diaspora === 'Oui' ? 'checked' : '' }}>
                                       Le propriétaire à t-il des agents de gestions ? (si oui coché la case)
                                    </label>
                                </div>
                                @error('diaspora')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
                
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" style="border: 1px solid black; border-radius: 5px;" 
                                    class="btn btn-primary mr-2">Mettre à jour</button>
                            <a href="{{ route('owner.index.admin') }}" class="btn btn-light">Annuler</a>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Ajout de SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

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