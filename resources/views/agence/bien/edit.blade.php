@extends('agence.layouts.template')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<!-- Mêmes styles que create.blade.php -->
<style>
    .form-section { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; border-left: 5px solid #02245b; }
    .section-title { color: #02245b; font-size: 1.2rem; font-weight: 600; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .btn-brand { background-color: #02245b; color: white; }
    .img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; margin-top: 5px; border: 1px solid #ddd; }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="font-weight-bold text-dark">Modifier le bien #{{ $bien->numero_bien }}</h3>
                <a href="{{ route('bien.index.agence') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
            </div>

            <form class="forms-sample" action="{{ route('bien.update.agence', $bien->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Section 1 -->
                <div class="form-section">
                    <h4 class="section-title">Informations générales</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Propriétaire</label>
                                <select class="form-control" name="proprietaire_id">
                                    <option value="">{{ Auth::guard('agence')->user()->name }}</option>
                                    @foreach($proprietaires as $proprietaire)
                                        <option value="{{ $proprietaire->code_id }}" {{ $bien->proprietaire_id == $proprietaire->code_id ? 'selected' : '' }}>
                                            {{ $proprietaire->name }} {{ $proprietaire->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type de bien</label>
                                <select class="form-control" name="type" id="typeSelect">
                                    <option value="Appartement" {{ $bien->type == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                                    <option value="Maison" {{ $bien->type == 'Maison' ? 'selected' : '' }}>Maison</option>
                                    <option value="Bureau" {{ $bien->type == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                    <option value="Terrain" {{ $bien->type == 'Terrain' ? 'selected' : '' }}>Terrain</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Superficie (m²)</label>
                                <input type="number" class="form-control" name="superficie" value="{{ $bien->superficie }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Commune</label>
                                <input type="text" class="form-control" name="commune" value="{{ $bien->commune }}">
                            </div>
                        </div>
                    </div>
                     <div class="row mt-3">
                        <div class="col-md-3">
                             <div class="form-group">
                                <label>Pièces</label>
                                <input type="number" class="form-control" name="nombre_de_chambres" value="{{ $bien->nombre_de_chambres }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                             <div class="form-group">
                                <label>Toilettes</label>
                                <input type="number" class="form-control" name="nombre_de_toilettes" value="{{ $bien->nombre_de_toilettes }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Utilisation</label>
                                <select class="form-control" name="utilisation">
                                    <option value="Habitation" {{ $bien->utilisation == 'Habitation' ? 'selected' : '' }}>Habitation</option>
                                    <option value="Bureau" {{ $bien->utilisation == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                                    <option value="Autre" {{ !in_array($bien->utilisation, ['Habitation', 'Bureau']) ? 'selected' : '' }}>
                                        {{ !in_array($bien->utilisation, ['Habitation', 'Bureau']) ? 'Autre ('.$bien->utilisation.')' : 'Autre' }}
                                    </option>
                                </select>
                                @if(!in_array($bien->utilisation, ['Habitation', 'Bureau']))
                                    <input type="hidden" name="autre_utilisation" value="{{ $bien->utilisation }}">
                                @endif
                            </div>
                        </div>
                     </div>
                </div>

                <!-- Section 2: Finances -->
                <div class="form-section">
                    <h4 class="section-title">Conditions Financières</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Loyer (FCFA)</label>
                                <input type="number" class="form-control" name="prix" id="prix" value="{{ $bien->prix }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Avance</label>
                                <input type="number" 
               class="form-control" 
               name="avance" 
               id="avance" 
               value="{{ $bien->avance }}" 
               min="1" 
               max="12"
               oninput="if(this.value > 12) this.value = 12;">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Caution</label>
                                <input type="number" 
               class="form-control" 
               name="caution" 
               id="caution" 
               value="{{ $bien->caution }}" 
               min="1" 
               max="12"
               oninput="if(this.value > 12) this.value = 12;">
                            </div>
                        </div>
                         <div class="col-md-3">
                             <label>Date Paiement</label>
                             <input type="number" class="form-control" name="disponibilite" value="{{ $bien->date_fixe }}">
                         </div>
                    </div>
                </div>

                <!-- Section 3: Images -->
                <div class="form-section">
                    <h4 class="section-title">Gestion des images</h4>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Image Principale</label>
                            <input type="file" name="main_image" class="form-control-file mb-1">
                            @if($bien->image)
                                <img src="{{ asset('storage/'.$bien->image) }}" class="img-preview">
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Image 1</label>
                            <input type="file" name="additional_images1" class="form-control-file mb-1">
                            @if($bien->image1) <img src="{{ asset('storage/'.$bien->image1) }}" class="img-preview"> @endif
                        </div>
                         <div class="col-md-4 mb-3">
                            <label>Image 2</label>
                            <input type="file" name="additional_images2" class="form-control-file mb-1">
                            @if($bien->image2) <img src="{{ asset('storage/'.$bien->image2) }}" class="img-preview"> @endif
                        </div>
                    </div>
                </div>

                <!-- Section 4 -->
                <div class="form-section">
                    <h4 class="section-title">Description</h4>
                    <textarea class="form-control" rows="5" name="description">{{ $bien->description }}</textarea>
                </div>

                <div class="text-center mb-5">
                    <button type="submit" class="btn btn-brand btn-lg px-5 shadow">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Note: Scripts de calcul identiques à create.blade.php à inclure ici si nécessaire --}}

{{-- POPUP UPDATE --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Mise à jour réussie !',
        text: "{{ session('success') }}",
        confirmButtonColor: '#02245b'
    });
</script>
@endif

@endsection