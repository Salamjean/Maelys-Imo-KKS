@extends('agence.layouts.template')

@section('content')
<!-- CSS SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .form-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 25px;
        margin-bottom: 25px;
        border-left: 5px solid #02245b;
    }
    .section-title {
        color: #02245b;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .form-control:focus {
        border-color: #02245b;
        box-shadow: 0 0 0 0.2rem rgba(2, 36, 91, 0.25);
    }
    .required-star { color: red; }
    .btn-brand { background-color: #02245b; color: white; }
    .btn-brand:hover { background-color: #011a42; color: white; }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="font-weight-bold text-dark">Ajouter un nouveau bien</h3>
                <a href="{{ route('bien.index.agence') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <form class="forms-sample" action="{{ route('bien.store.agence') }}" method="POST" enctype="multipart/form-data" id="createBienForm">
                @csrf
                
                <!-- Section 1: Informations de base -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-info-circle mr-2"></i>Informations générales</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Propriétaire <span class="required-star">*</span></label>
                                <select class="form-control" name="proprietaire_id" required>
                                    <option value="">Choisir un propriétaire</option>
                                    <option value="">{{ Auth::guard('agence')->user()->name }} (Moi-même)</option>
                                    @foreach($proprietaires as $proprietaire)
                                        <option value="{{ $proprietaire->code_id }}">
                                            {{ $proprietaire->name }} {{ $proprietaire->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proprietaire_id') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type de bien <span class="required-star">*</span></label>
                                <select class="form-control" name="type" id="typeSelect">
                                    <option value="Appartement">Appartement</option>
                                    <option value="Maison">Maison</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Terrain">Terrain</option>
                                </select>
                                @error('type') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Superficie (m²) <span class="required-star">*</span></label>
                                <input type="number" class="form-control" placeholder="Ex: 150" name="superficie">
                                @error('superficie') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Commune <span class="required-star">*</span></label>
                                <input type="text" class="form-control" placeholder="Ex: Cocody" name="commune">
                                @error('commune') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-3 field-detail">
                            <div class="form-group">
                                <label>Nombre de pièces</label>
                                <input type="number" class="form-control" name="nombre_de_chambres">
                            </div>
                        </div>
                        <div class="col-md-3 field-detail">
                            <div class="form-group">
                                <label>Nombre de toilettes</label>
                                <input type="number" class="form-control" name="nombre_de_toilettes">
                            </div>
                        </div>
                        <div class="col-md-3 field-detail">
                            <div class="form-group">
                                <label>Garage</label>
                                <select class="form-control" name="garage">
                                    <option value="">Sélectionner</option>
                                    <option value="Oui">Oui</option>
                                    <option value="Non">Non</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type d'utilisation <span class="required-star">*</span></label>
                                <select class="form-control" name="utilisation">
                                    <option value="Habitation">Habitation</option>
                                    <option value="Bureau">Bureau</option>
                                    <option value="Autre">Autre (à préciser)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Conditions Financières -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-money-bill-wave mr-2"></i>Conditions Financières</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label id="label-prix">Loyer Mensuel (FCFA) <span class="required-star">*</span></label>
                                <input type="number" class="form-control font-weight-bold" name="prix" id="prix">
                                @error('prix') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Avance (Mois) <span class="required-star">*</span></label>
                                <input type="number" 
               class="form-control" 
               name="avance" 
               id="avance" 
               min="1" 
               max="12" 
               oninput="if(this.value > 12) this.value = 12;"
               placeholder="Max 12">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Caution (Mois) <span class="required-star">*</span></label>
                               <input type="number" 
               class="form-control" 
               name="caution" 
               id="caution" 
               min="1" 
               max="12" 
               oninput="if(this.value > 12) this.value = 12;"
               placeholder="Max 12">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Frais Agence (Mois)</label>
                                <input type="number" class="form-control bg-light" name="frais" value="1" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Total à payer (Estimation)</label>
                                <input type="text" class="form-control bg-success text-white font-weight-bold" name="montant_total" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Jour de paiement mensuel (1-31) <span class="required-star">*</span></label>
                                <input type="number" class="form-control" name="disponibilite" min="1" max="31" placeholder="Ex: 5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Images -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-images mr-2"></i>Photos du bien</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo Principale (Façade) <span class="required-star">*</span></label>
                                <input type="file" name="main_image" class="form-control-file" required>
                                <small class="text-muted">Format: JPG, PNG. Max 2Mo</small>
                                @error('main_image') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo supplémentaire 1 <span class="required-star">*</span></label>
                                <input type="file" name="additional_images1" class="form-control-file" required>
                                @error('additional_images1') <span class="text-danger text-small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo supplémentaire 2</label>
                                <input type="file" name="additional_images2" class="form-control-file">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo supplémentaire 3</label>
                                <input type="file" name="additional_images3" class="form-control-file">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo supplémentaire 4</label>
                                <input type="file" name="additional_images4" class="form-control-file">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Photo supplémentaire 5</label>
                                <input type="file" name="additional_images5" class="form-control-file">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Description -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-pen mr-2"></i>Description</h4>
                    <div class="form-group">
                        <textarea class="form-control" rows="5" name="description" placeholder="Décrivez les atouts du bien, le quartier, les commodités à proximité..."></textarea>
                        @error('description') <span class="text-danger text-small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row mt-4 mb-5">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-brand btn-lg px-5 shadow"><i class="fas fa-save mr-2"></i> Enregistrer le bien</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        
        // --- 1. Gestion du Type de bien ---
        function toggleFields() {
            let type = $('#typeSelect').val();
            if(type === 'Terrain') {
                $('.field-detail input, .field-detail select').prop('disabled', true);
                $('.field-detail').fadeOut();
                $('#label-prix').text('Prix de vente (FCFA)');
                $('#avance, #caution').prop('readonly', true).val(0);
            } else {
                $('.field-detail input, .field-detail select').prop('disabled', false);
                $('.field-detail').fadeIn();
                $('#label-prix').text('Loyer Mensuel (FCFA)');
                $('#avance, #caution').prop('readonly', false);
            }
        }
        $('#typeSelect').on('change', toggleFields);
        toggleFields(); // Init

        // --- 2. Calcul Automatique ---
        function calculateTotal() {
            let prix = parseFloat($('#prix').val()) || 0;
            let avance = parseFloat($('#avance').val()) || 0;
            let caution = parseFloat($('#caution').val()) || 0;
            let frais = 1; // 1 mois de frais
            
            // Si c'est un terrain (pas de caution/avance au sens location)
            if($('#typeSelect').val() === 'Terrain') {
                $('input[name="montant_total"]').val(prix);
            } else {
                let total = prix * (avance + caution + frais);
                $('input[name="montant_total"]').val(total.toLocaleString('fr-FR') + ' FCFA');
            }
        }
        $('#prix, #avance, #caution, #typeSelect').on('input change', calculateTotal);

        // --- 3. Gestion "Autre" Utilisation ---
        $('select[name="utilisation"]').on('change', function() {
            if ($(this).val() === 'Autre') {
                Swal.fire({
                    title: 'Précisez l\'utilisation',
                    input: 'text',
                    inputPlaceholder: 'Ex: Entrepôt, Commerce...',
                    showCancelButton: true,
                    confirmButtonColor: '#02245b'
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        // Crée un champ caché
                        $('<input>').attr({type: 'hidden', name: 'autre_utilisation', value: result.value}).appendTo('form');
                        // Change le texte de l'option
                        $(this).find('option[value="Autre"]').text('Autre (' + result.value + ')');
                    } else {
                        $(this).val('Habitation'); // Reset
                    }
                });
            }
        });
    });
</script>

{{-- AFFICHER LE POPUP DE SESSION (SUCCESS/ERROR) --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Félicitations !',
        text: "{{ session('success') }}",
        confirmButtonColor: '#02245b',
        confirmButtonText: 'D\'accord'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Oups...',
        text: "{{ session('error') }}",
        confirmButtonColor: '#d33'
    });
</script>
@endif

@endsection