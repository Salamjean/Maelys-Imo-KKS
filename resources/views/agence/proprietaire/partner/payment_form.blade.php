@extends('agence.layouts.template')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%); border-bottom: none;">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Paiement Propriétaire - {{ $proprietaire->name }} {{ $proprietaire->prenom }}</h4>
                </div>

                <div class="card-body bg-light">
                    <form method="POST" action="{{ route('partner.payment.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="proprietaire_id" value="{{ $proprietaire->code_id }}">

                        <div class="mb-4">
                            <label for="mode_paiement" class="form-label fw-bold text-dark">Mode de paiement</label>
                            <select class="form-select p-3 border-2 border-primary" id="mode_paiement" name="mode_paiement" required>
                                <option value="Chèques" {{ $proprietaire->choix_paiement == 'Chèques' ? 'selected' : '' }}>Chèque</option>
                                <option value="Virement Bancaire" {{ $proprietaire->choix_paiement == 'Virement Bancaire' ? 'selected' : '' }}>Virement Bancaire</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="montant" class="form-label fw-bold text-dark">Montant</label>
                            <input type="number" class="form-control p-3 bg-white border-2 border-primary" id="montant" value="{{ $montantTotal }}" name="montant" required readonly>
                        </div>

                        <!-- Section pour Chèque -->
                        <div id="cheque-section" class="p-4 rounded-3 mb-4" style="display: {{ $proprietaire->choix_paiement == 'Chèques' ? 'block' : 'none' }}; background-color: #f8f9fa; border-left: 4px solid #02245b;">
                           

                            <fieldset class="border rounded-4 p-4 shadow-sm" style="border-color: #02245b !important;">
                                <legend class="float-none w-auto px-3 fs-5 fw-bold" style="color: #02245b;">
                                    <i class="fas fa-user-circle me-2"></i>Informations concernant le récupérateur.
                                </legend>

                                <div class="mb-4 form-check d-flex align-items-center" style="text-align: center">
                                <input type="checkbox" 
                                    class="form-check-input border-2 me-3" 
                                    id="est_proprietaire" 
                                    name="est_proprietaire" 
                                    value="1"
                                    style="width: 1.5em; 
                                            height: 1.5em;
                                            cursor: pointer;
                                            border-color: #02245b !important;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <label class="form-check-label fw-medium fs-5" 
                                    for="est_proprietaire"
                                    style="cursor: pointer;
                                            color: #02245b;
                                            user-select: none;">
                                    Cochet la case si c'est le propriétaire le récupérateur.
                                </label>
                            </div>
                              <div id="beneficiaire-info">
                                <div class="row">
                                    <!-- Première colonne -->
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="beneficiaire_nom" class="form-label fw-semibold">
                                                <i class="fas fa-user-tag me-2"></i>Nom récupérateur
                                            </label>
                                            <input type="text" 
                                                class="form-control p-3 border-2 border-primary" 
                                                id="beneficiaire_nom" 
                                                name="beneficiaire_nom"
                                                placeholder="Entrez le nom"
                                                style="border-radius: 8px;">
                                        </div>
                                        <div class="mb-4">
                                            <label for="beneficiaire_contact" class="form-label fw-semibold">
                                                <i class="fas fa-phone-alt me-2"></i>Contact récupérateur
                                            </label>
                                            <input type="text" 
                                                class="form-control p-3 border-2 border-primary" 
                                                id="beneficiaire_contact" 
                                                name="beneficiaire_contact"
                                                placeholder="Numéro de téléphone"
                                                style="border-radius: 8px;">
                                        </div>
                                        
                                    </div>

                                    <!-- Deuxième colonne -->
                                    <div class="col-md-6">
                                        
                                        <div class="mb-3">
                                            <label for="beneficiaire_prenom" class="form-label fw-semibold">
                                                <i class="fas fa-user me-2"></i>Prénom récupérateur
                                            </label>
                                            <input type="text" 
                                                class="form-control p-3 border-2 border-primary" 
                                                id="beneficiaire_prenom" 
                                                name="beneficiaire_prenom"
                                                placeholder="Entrez le prénom"
                                                style="border-radius: 8px;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="beneficiaire_email" class="form-label fw-semibold">
                                                <i class="fas fa-envelope me-2"></i>Email récupérateur
                                            </label>
                                            <input type="email" 
                                                class="form-control p-3 border-2 border-primary" 
                                                id="beneficiaire_email" 
                                                name="beneficiaire_email"
                                                placeholder="email@exemple.com"
                                                style="border-radius: 8px;">
                                        </div>
                                        <div class="mb-12">
                                            <label for="numero_cni" class="form-label fw-semibold">
                                                <i class="fas fa-envelope me-2"></i>Numéro CNI du récupérateur
                                            </label>
                                            <input type="text" 
                                                class="form-control p-3 border-2 border-primary" 
                                                id="numero_cni" 
                                                name="numero_cni"
                                                placeholder="CI - XX - XXX"
                                                style="border-radius: 8px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </fieldset>
                        </div>

                        <!-- Section pour Virement Bancaire -->
                        <div id="virement-section" class="p-4 rounded-3 mb-4" style="display: {{ $proprietaire->choix_paiement == 'Virement Bancaire' ? 'block' : 'none' }}; background-color: #f8f9fa; border-left: 4px solid #02245b;">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">RIB du propriétaire</label>
                                <input type="text" class="form-control p-3 bg-white border-2" value="{{ $proprietaire->rib }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="fichier_paiement" class="form-label fw-bold text-dark">Justificatif de virement (PDF)</label>
                                <input type="file" class="form-control p-3 border-2" id="fichier_paiement" name="fichier_paiement" accept=".pdf">
                                <small class="text-muted">Taille maximale: 5MB</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-lg text-white py-3" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%); border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <i class="fas fa-save me-2"></i>Enregistrer le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('mode_paiement').addEventListener('change', function() {
        if (this.value === 'Chèques') {
            document.getElementById('cheque-section').style.display = 'block';
            document.getElementById('virement-section').style.display = 'none';
        } else {
            document.getElementById('cheque-section').style.display = 'none';
            document.getElementById('virement-section').style.display = 'block';
        }
    });

    document.getElementById('est_proprietaire').addEventListener('change', function() {
        const beneficiaireInfo = document.getElementById('beneficiaire-info');
        if (this.checked) {
            beneficiaireInfo.style.display = 'none';
        } else {
            beneficiaireInfo.style.display = 'block';
        }
    });
</script>
@endsection