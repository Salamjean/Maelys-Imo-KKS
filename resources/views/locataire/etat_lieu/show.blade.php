@extends('locataire.layouts.template')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-12">
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                <!-- En-tête avec dégradé de couleur -->
                <div class="card-header py-3 text-white" style="background-color:#02245b ">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-home me-2"></i>État des lieux - Code de vérification</h3>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- Section QR Code avec animation -->
                    <div class="text-center mb-5 p-4 bg-light rounded-3 border border-2 border-dashed border-primary">
                        <h4 class="mb-3 fw-bold text-primary"><i class="fas fa-qrcode me-2"></i>Code de vérification</h4>
                        @if(isset($qrCodeBase64))
                            <div class="position-relative d-inline-block">
                                <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" class="img-fluid shadow-sm" style="max-width: 200px;">
                                <div class="position-absolute top-0 start-100 translate-middle">
                                    <span class="badge rounded-pill bg-danger pulse-animation">
                                        <i class="fas fa-circle me-1 small"></i> Actif
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="mb-1"><span class="text-black">Code:</span> <span class="fw-bold text-dark">{{ $verificationCode }}</span></p>
                                <p class="mb-0"><span class="text-black">Expire à:</span> <span class="fw-bold">{{ $expiresAt->format('d/m/Y H:i') }}</span></p>
                            </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Aucun QR code disponible pour le moment
                                </div>
                            @endif
                    </div>

                    <!-- Diviseur stylisé -->
                    <div class="divider my-4">
                        <span class="divider-line"></span>
                        <span class="divider-icon"><i class="fas fa-info-circle"></i></span>
                        <span class="divider-line"></span>
                    </div>

                    <!-- Section Informations sur le bien avec icônes -->
                    <div class="mb-5">
                        <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
                            <i class="fas fa-building me-2"></i> Informations sur le bien
                        </h4>
                        @if($locataire->bien)
                            <div class="row g-3">
                                <!-- Adresse -->
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center h-100">
                                        <i class="fas fa-map-marker-alt text-primary fs-5 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 text-black small">Commune</h6>
                                            <p class="mb-0 fw-bold">  {{ $locataire->bien->commune }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Type -->
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center h-100">
                                        <i class="fas fa-home text-primary fs-5 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 text-black small">Type</h6>
                                            <p class="mb-0 fw-bold">  {{ $locataire->bien->type }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Surface -->
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center h-100">
                                        <i class="fas fa-ruler-combined text-primary fs-5 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 text-black small">Surface</h6>
                                            <p class="mb-0 fw-bold">  {{ $locataire->bien->superficie }} m²</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Loyer -->
                                <div class="col-md-3 col-6">
                                    <div class="d-flex align-items-center h-100">
                                        <i class="fas fa-euro-sign text-primary fs-5 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 text-black small">Loyer</h6>
                                            <p class="mb-0 fw-bold">  {{ number_format($locataire->bien->prix, 0, '', ' ') }} Fcfa</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucun bien associé à ce locataire
                            </div>
                        @endif
                    </div>

                    <!-- Diviseur stylisé -->
                    <div class="divider my-4">
                        <span class="divider-line"></span>
                        <span class="divider-icon"><i class="fas fa-user-tie"></i></span>
                        <span class="divider-line"></span>
                    </div>

                    <!-- Section Agent avec carte de présentation -->
                    <div class="mb-5">
                        <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
                            <i class="fas fa-user-tie me-2"></i> Agent en charge
                        </h4>
                        @if($comptable)
                            <div class="card agent-card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        @if($comptable->profile_image)
                                            <div class="avatar me-3">
                                                <img src="{{ asset('assets/images/Vatar.jpg') }}" class="rounded-circle" width="80" height="80" alt="Photo de l'agent">
                                            </div>
                                        @else
                                            <div class="avatar-placeholder me-3">
                                                <img src="{{ asset('assets/images/Vatar.jpg') }}" class="rounded-circle" width="80" height="80" alt="Photo de l'agent">
                                            </div>
                                        @endif
                                        <div class="flex-grow-1" style="display: flex; justify-content:space-between">
                                            <h5 class="mb-1">{{ $comptable->name }} {{ $comptable->prenom }}</h5>
                                            <p class="text-black small mb-2"><i class="fas fa-briefcase me-1"></i>   Agent immobilier</p>
                                            
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="tel:{{ $comptable->contact }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fas fa-phone-alt me-1"></i> {{ $comptable->contact }}
                                                </a>
                                                <a href="mailto:{{ $comptable->email }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fas fa-envelope me-1"></i> {{ $comptable->email }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucun agent assigné
                            </div>
                        @endif
                    </div>

                    <!-- Diviseur stylisé -->
                    <div class="divider my-4">
                        <span class="divider-line"></span>
                        <span class="divider-icon"><i class="fas fa-clipboard-check"></i></span>
                        <span class="divider-line"></span>
                    </div>

                    <!-- Section État des lieux -->
                    <div class="mb-4">
                        <h4 class="mb-4 fw-bold text-primary d-flex align-items-center">
                            <i class="fas fa-clipboard-list me-2"></i> Détails de l'état des lieux
                        </h4>
                        
                        @if($etatLieu)
                            <!-- Sélecteur d'état des lieux -->
                            <div class="row mb-4">
                                <div class="col-md-6 mx-auto">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-primary active" id="btn-entree" data-etat="entree">
                                            <i class="fas fa-sign-in-alt me-2"></i>État Entrée
                                        </button>
                                        @if($etatLieuSortie)
                                        <button type="button" class="btn btn-outline-primary" id="btn-sortie" data-etat="sortie">
                                            <i class="fas fa-sign-out-alt me-2"></i>État Sortie
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Conteneur pour l'état des lieux d'entrée -->
                            <div id="etat-entree-container">
                                <!-- Résumé état des lieux entrée -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card stat-card bg-light-success border-0 h-100">
                                            <div class="card-body text-center p-3">
                                                <div class="stat-icon mb-2 text-success">
                                                    <i class="fas fa-sign-in-alt fs-2"></i>
                                                </div>
                                                <div class="etat-confirmation-container">
                                                    @if($etatLieu->status_etat_entre === 'Oui')
                                                        <div class="d-flex align-items-center" style="display:flex; justify-content:center">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            <div>
                                                                <h6 class="mb-0 text-success">État d'entrée confirmé</h6>
                                                                @if($etatLieu->updated_at)
                                                                    <small class="text-muted">Confirmé le: {{ $etatLieu->updated_at->format('d/m/Y à H:i') }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a href="#" id="confirm-entree-btn" data-etat-id="{{ $etatLieu->id }}" class="text-decoration-none">
                                                            <div class="d-flex align-items-center"  style="display:flex; justify-content:center">
                                                                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                                                <div>
                                                                    <h6 class="mb-0">Confirmation requise</h6>
                                                                    <small class="text-muted">Cliquez pour vérifier et confirmer</small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card stat-card bg-light-info border-0 h-100">
                                            <div class="card-body text-center p-3">
                                                <div class="stat-icon mb-2 text-info">
                                                    <i class="fas fa-key fs-2"></i>
                                                </div>
                                                <h6 class="mb-1">Nombre de clés</h6>
                                                <p class="mb-0 fw-bold">{{ $etatLieu->nombre_cle ?? 'Non spécifié' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation par onglets -->
                                <ul class="nav nav-tabs mb-4" id="etatLieuTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="communes-tab" data-bs-toggle="tab" data-bs-target="#communes" type="button" role="tab">
                                            <i class="fas fa-door-open me-1"></i> Parties communes
                                        </button>
                                    </li>
                                    @foreach($etatLieu->chambres as $index => $chambre)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="chambre{{ $index }}-tab" data-bs-toggle="tab" data-bs-target="#chambre{{ $index }}" type="button" role="tab">
                                            <i class="fas fa-bed me-1"></i> Chambre {{ $index + 1 }}
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>

                                <!-- Contenu des onglets -->
                                <div class="tab-content" id="etatLieuTabsContent">
                                    <!-- Onglet Parties communes -->
                                    <div class="tab-pane fade show active" id="communes" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="25%">Élément</th>
                                                        <th width="15%">État</th>
                                                        <th width="60%">Observations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach([
                                                        'SOL' => ['field' => 'sol', 'obs' => 'observation_sol'],
                                                        'MURS' => ['field' => 'murs', 'obs' => 'observation_murs'],
                                                        'PLAFONDS' => ['field' => 'plafond', 'obs' => 'observation_plafond'],
                                                        'PORTES' => ['field' => 'porte_entre', 'obs' => 'observation_porte_entre'],
                                                        'ELECTRICITE' => ['field' => 'interrupteur', 'obs' => 'observation_interrupteur'],
                                                        'ROBINETTERIE' => ['field' => 'robinet', 'obs' => 'observation_robinet'],
                                                        'EVIER INOX DE LAVABO' => ['field' => 'lavabo', 'obs' => 'observation_lavabo'],
                                                        'DOUCHE ET SDB' => ['field' => 'douche', 'obs' => 'observation_douche']
                                                    ] as $label => $fields)
                                                    <tr>
                                                        <td>{{ $label }}</td>
                                                        <td>
                                                            @if(isset($etatLieu->parties_communes[$fields['field']]))
                                                            <span class="badge rounded-pill bg-{{ $etatLieu->parties_communes[$fields['field']] === 'bon' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($etatLieu->parties_communes[$fields['field']]) }}
                                                            </span>
                                                            @else
                                                            <span class="badge rounded-pill bg-secondary">Non renseigné</span>
                                                            @endif
                                                        </td>
                                                        <td class="observation-cell">
                                                            {{ $etatLieu->parties_communes[$fields['obs']] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Onglets pour chaque chambre -->
                                    @foreach($etatLieu->chambres as $index => $chambre)
                                    <div class="tab-pane fade" id="chambre{{ $index }}" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="25%">Élément</th>
                                                        <th width="15%">État</th>
                                                        <th width="60%">Observations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach([
                                                        'SOL' => ['field' => 'sol', 'obs' => 'observation_sol'],
                                                        'MURS' => ['field' => 'murs', 'obs' => 'observation_murs'],
                                                        'PLAFONDS' => ['field' => 'plafond', 'obs' => 'observation_plafond']
                                                    ] as $label => $fields)
                                                    <tr>
                                                        <td>{{ $label }}</td>
                                                        <td>
                                                            @if(isset($chambre[$fields['field']]))
                                                            <span class="badge rounded-pill bg-{{ $chambre[$fields['field']] === 'bon' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($chambre[$fields['field']]) }}
                                                            </span>
                                                            @else
                                                            <span class="badge rounded-pill bg-secondary">Non renseigné</span>
                                                            @endif
                                                        </td>
                                                        <td class="observation-cell">
                                                            {{ $chambre[$fields['obs']] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Conteneur pour l'état des lieux de sortie (caché par défaut) -->
                            @if($etatLieuSortie)
                            <div id="etat-sortie-container" style="display: none;">
                                <!-- Résumé état des lieux sortie -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card stat-card bg-light-warning border-0 h-100">
                                            <div class="card-body text-center p-3">
                                                <div class="stat-icon mb-2 text-warning">
                                                    <i class="fas fa-sign-out-alt fs-2"></i>
                                                </div>
                                               <div class="etat-confirmation-container">
                                                    @if($etatLieuSortie->status_sorti === 'Oui')
                                                        <div class="d-flex align-items-center" style="display:flex; justify-content:center">
                                                            <i class="fas fa-check-circle text-success me-2"></i>
                                                            <div>
                                                                <h6 class="mb-0 text-success">État de sortie confirmé</h6>
                                                                @if($etatLieuSortie->updated_at)
                                                                    <small class="text-muted">Confirmé le: {{ $etatLieuSortie->updated_at->format('d/m/Y à H:i') }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a href="#" id="confirm-sortie-btn" data-etat-id="{{ $etatLieuSortie->id }}" class="text-decoration-none">
                                                            <div class="d-flex align-items-center" style="display:flex; justify-content:center">
                                                                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                                                <div>
                                                                    <h6 class="mb-0">Confirmation requise</h6>
                                                                    <small class="text-muted">Cliquez pour vérifier et confirmer</small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card stat-card bg-light-info border-0 h-100">
                                            <div class="card-body text-center p-3">
                                                <div class="stat-icon mb-2 text-info">
                                                    <i class="fas fa-key fs-2"></i>
                                                </div>
                                                <h6 class="mb-1">Nombre de clés</h6>
                                                <p class="mb-0 fw-bold">{{ $etatLieuSortie->nombre_cle ?? 'Non spécifié' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation par onglets -->
                                <ul class="nav nav-tabs mb-4" id="etatLieuSortieTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="communes-sortie-tab" data-bs-toggle="tab" data-bs-target="#communes-sortie" type="button" role="tab">
                                            <i class="fas fa-door-open me-1"></i> Parties communes
                                        </button>
                                    </li>
                                    @foreach($etatLieuSortie->chambres as $index => $chambre)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="chambre-sortie-{{ $index }}-tab" data-bs-toggle="tab" data-bs-target="#chambre-sortie-{{ $index }}" type="button" role="tab">
                                            <i class="fas fa-bed me-1"></i> Chambre {{ $index + 1 }}
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>

                                <!-- Contenu des onglets -->
                                <div class="tab-content" id="etatLieuSortieTabsContent">
                                    <!-- Onglet Parties communes -->
                                    <div class="tab-pane fade show active" id="communes-sortie" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="25%">Élément</th>
                                                        <th width="15%">État</th>
                                                        <th width="60%">Observations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach([
                                                        'SOL' => ['field' => 'sol', 'obs' => 'observation_sol'],
                                                        'MURS' => ['field' => 'murs', 'obs' => 'observation_murs'],
                                                        'PLAFONDS' => ['field' => 'plafond', 'obs' => 'observation_plafond'],
                                                        'PORTES' => ['field' => 'porte_entre', 'obs' => 'observation_porte_entre'],
                                                        'ELECTRICITE' => ['field' => 'interrupteur', 'obs' => 'observation_interrupteur'],
                                                        'ROBINETTERIE' => ['field' => 'robinet', 'obs' => 'observation_robinet'],
                                                        'EVIER INOX DE LAVABO' => ['field' => 'lavabo', 'obs' => 'observation_lavabo'],
                                                        'DOUCHE ET SDB' => ['field' => 'douche', 'obs' => 'observation_douche']
                                                    ] as $label => $fields)
                                                    <tr>
                                                        <td>{{ $label }}</td>
                                                        <td>
                                                            @if(isset($etatLieuSortie->parties_communes[$fields['field']]))
                                                            <span class="badge rounded-pill bg-{{ $etatLieuSortie->parties_communes[$fields['field']] === 'bon' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($etatLieuSortie->parties_communes[$fields['field']]) }}
                                                            </span>
                                                            @else
                                                            <span class="badge rounded-pill bg-secondary">Non renseigné</span>
                                                            @endif
                                                        </td>
                                                        <td class="observation-cell">
                                                            {{ $etatLieuSortie->parties_communes[$fields['obs']] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Onglets pour chaque chambre -->
                                    @foreach($etatLieuSortie->chambres as $index => $chambre)
                                    <div class="tab-pane fade" id="chambre-sortie-{{ $index }}" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="25%">Élément</th>
                                                        <th width="15%">État</th>
                                                        <th width="60%">Observations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach([
                                                        'SOL' => ['field' => 'sol', 'obs' => 'observation_sol'],
                                                        'MURS' => ['field' => 'murs', 'obs' => 'observation_murs'],
                                                        'PLAFONDS' => ['field' => 'plafond', 'obs' => 'observation_plafond']
                                                    ] as $label => $fields)
                                                    <tr>
                                                        <td>{{ $label }}</td>
                                                        <td>
                                                            @if(isset($chambre[$fields['field']]))
                                                            <span class="badge rounded-pill bg-{{ $chambre[$fields['field']] === 'bon' ? 'success' : 'danger' }}">
                                                                {{ ucfirst($chambre[$fields['field']]) }}
                                                            </span>
                                                            @else
                                                            <span class="badge rounded-pill bg-secondary">Non renseigné</span>
                                                            @endif
                                                        </td>
                                                        <td class="observation-cell">
                                                            {{ $chambre[$fields['obs']] ?? '-' }}
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @else
                            <div class="alert alert-warning d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucun état des lieux disponible
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styles personnalisés */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
    }
    
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
    }
    
    .divider-line {
        flex-grow: 1;
        height: 1px;
        background-color: #e0e0e0;
    }
    
    .divider-icon {
        padding: 0 1rem;
        color: #3a7bd5;
        font-size: 1.25rem;
    }
    .etat-confirmation-container {
    transition: all 0.3s ease;
    padding: 0.5rem;
    border-radius: 0.5rem;
}

.etat-confirmation-container:hover {
    background-color: rgba(0,0,0,0.03);
}

#confirm-entree-btn:hover {
    opacity: 0.8;
}
    
    .agent-card {
        transition: transform 0.3s ease;
        border-radius: 0.75rem !important;
    }
    
    .agent-card:hover {
        transform: translateY(-3px);
    }
    
    .avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e0e0e0;
    }
    
    .avatar-placeholder {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f8f9fa;
        border: 3px solid #e0e0e0;
    }
    
    .observation-cell {
        font-size: 0.9rem;
        color: #666;
    }
    
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 0.75rem !important;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.2);
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.95); }
        50% { transform: scale(1.05); }
        100% { transform: scale(0.95); }
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .nav-tabs .nav-link.active {
        color: #3a7bd5;
        background-color: rgba(58, 123, 213, 0.1);
        border-bottom: 3px solid #3a7bd5;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du basculement entre états des lieux entrée/sortie
    const btnEntree = document.getElementById('btn-entree');
    const btnSortie = document.getElementById('btn-sortie');
    const containerEntree = document.getElementById('etat-entree-container');
    const containerSortie = document.getElementById('etat-sortie-container');

    if (btnEntree && btnSortie) {
        btnEntree.addEventListener('click', function() {
            this.classList.add('active');
            btnSortie.classList.remove('active');
            containerEntree.style.display = 'block';
            containerSortie.style.display = 'none';
        });

        btnSortie.addEventListener('click', function() {
            this.classList.add('active');
            btnEntree.classList.remove('active');
            containerEntree.style.display = 'none';
            containerSortie.style.display = 'block';
        });
    }

    // Réinitialisation des onglets Bootstrap lorsqu'on change d'état des lieux
    if (typeof bootstrap !== 'undefined') {
        const tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElms.forEach(tabEl => {
            tabEl.addEventListener('click', function() {
                const tab = new bootstrap.Tab(this);
                tab.show();
            });
        });
    }
});
</script>

{{-- confirmer l'etat des lieux d'entrée  --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirm-entree-btn');
    const confirmUrl = "{{ route('etat-lieu.confirm-entree', ['id' => '__ID__']) }}";
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const etatId = this.getAttribute('data-etat-id');
            const finalUrl = confirmUrl.replace('__ID__', etatId);
            
            Swal.fire({
                title: 'Confirmer l\'état des lieux d\'entrée?',
                text: 'Voulez-vous vraiment confirmer cet état des lieux?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(finalUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(handleResponse)
                    .then(handleSuccess)
                    .catch(handleError);
                }
            });
        });
    }

    function handleResponse(response) {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    }

    function handleSuccess(data) {
        if (data.success) {
            return Swal.fire('Confirmé!', data.message, 'success')
                .then(() => location.reload());
        }
        throw new Error(data.message || 'Erreur inconnue');
    }

    function handleError(error) {
        Swal.fire('Erreur', error.message || 'Une erreur est survenue', 'error');
        console.error(error);
    }
});
</script>

{{-- confirmer l'etat des lieux de sortie  --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirm-sortie-btn');
    const confirmUrl = "{{ route('etat-lieu.confirm-sortie', ['id' => '__ID__']) }}";
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const etatId = this.getAttribute('data-etat-id');
            const finalUrl = confirmUrl.replace('__ID__', etatId);
            
            Swal.fire({
                title: 'Confirmer l\'état des lieux de sortie ?',
                text: 'Voulez-vous vraiment confirmer cet état des lieux?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(finalUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(handleResponse)
                    .then(handleSuccess)
                    .catch(handleError);
                }
            });
        });
    }

    function handleResponse(response) {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    }

    function handleSuccess(data) {
        if (data.success) {
            return Swal.fire('Confirmé!', data.message, 'success')
                .then(() => location.reload());
        }
        throw new Error(data.message || 'Erreur inconnue');
    }

    function handleError(error) {
        Swal.fire('Erreur', error.message || 'Une erreur est survenue', 'error');
        console.error(error);
    }
});
</script>

@endsection