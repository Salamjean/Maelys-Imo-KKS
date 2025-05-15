@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modification de l'agence partenaire</h4>
            <p class="card-description text-center">Modifiez les informations de l'agence ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('agence.update', $agence->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations de l'agence</legend>
                    
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                    </div>

                    <!-- Section 2: Détails -->
                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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