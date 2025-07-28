@extends('agence.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">


<div class="col-lg-12 stretch-card">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Locataires actuels</h4>
            <p class="card-description text-center">
                Listes des locations de votre agence
            </p>

            <!-- Modal pour afficher les images -->
            <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="imageModalLabel">Visualisation de l'image</h5>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center p-0">
                            <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh; width: auto;">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Modal pour changer le statut -->
            <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="statusModalLabel">Changer le statut du locataire</h5>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="statusForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="newStatus" class="form-label">Nouveau statut</label>
                                    <select class="form-select" id="newStatus" name="status" required>
                                        <option value="Actif">Actif</option>
                                        <option value="Inactif">Inactif</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="motifField">
                                    <label for="motif" class="form-label">Motif (obligatoire si inactif ou pas sérieux)</label>
                                    <textarea class="form-control" id="motif" name="motif" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
           
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un locataire...">
            </div>
            <div class="table-responsive pt-3">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #02245b; color: white;">
                        <tr class="text-center">
                            <th>ID du locataire</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Contact</th>
                            <th>Profession</th>
                            <th>Adresse complète</th>
                            <th>Pièce d'identité</th>
                            <th>Statut</th>
                            <th>Contrat</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locataires as $locataire)
                            <tr class="text-center pt-3" style="height: 30px">
                                <td><strong>{{ $locataire->code_id }}</strong></td>
                                <td><strong>{{ $locataire->name }}</strong></td>
                                <td><strong>{{ $locataire->prenom }}</strong></td>
                                <td>{{ $locataire->contact }}</td>
                                <td>{{ $locataire->profession }}</td>
                                <td>{{ $locataire->adresse }}</td>
                                <td>
                                    @if($locataire->piece)
                                        <button class="btn btn-sm btn-info preview-image"
                                                data-image="{{ asset('storage/'.$locataire->piece) }}"
                                                data-title="Pièce d'identité de {{ $locataire->name }}">
                                            Voir
                                        </button>
                                    @else
                                        <span class="text-muted">Non fournie</span>
                                    @endif
                                </td>
                                <td>
                                    @if($locataire->status == 'Actif')
                                        <span class="badge bg-success text-white">Actif</span>
                                    @elseif($locataire->status == 'Inactif')
                                        <span class="badge bg-danger text-white">Inactif</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pas sérieux</span>
                                    @endif
                                </td>
                                <td>
                                    @if($locataire->contrat)
                                        <div class="btn-group d-flex gap-2" role="group">
                                            <!-- Bouton Voir -->
                                            <a href="{{ asset('storage/'.$locataire->contrat) }}" 
                                            class="btn btn-sm btn-info"
                                            target="_blank"
                                            title="Voir le contrat">
                                                <i class="mdi mdi-eye"></i>
                                            </a>

                                            <!-- Bouton Télécharger -->
                                            <a href="{{ route('locataires.downloadContrat', $locataire->id) }}"
                                            class="btn btn-sm btn-primary"
                                            title="Télécharger le contrat">
                                                <i class="mdi mdi-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">Aucun contrat</span>
                                    @endif
                                </td>
                                <td class="text-center ">
                                    <div class="btn-group gap-2" role="group">
                                        @if(($locataire->bien_id) && $locataire->status === 'Inactif')
                                        <a href="{{ route('locataire.edit', $locataire->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger change-status-btn"
                                                data-locataire-id="{{ $locataire->id }}"
                                                data-current-status="{{ $locataire->status }}"
                                                title="Changer statut">
                                            <i class="mdi mdi-account-convert"></i>
                                        </button>
                                        @endif
                                            <button class="btn btn-sm btn-primary attribuer-bien-btn"
                                                    data-locataire-id="{{ $locataire->id }}"
                                                    title="Attribuer un bien">
                                                <i class="mdi mdi-home-plus"></i>
                                            </button>
                                        
                                        {{-- <form action="#" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" title="Supprimer">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4">
                                    <div class="alert alert-info">
                                        Aucun locataire enregistré pour le moment.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($locataires->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($locataires->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">«</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $locataires->previousPageUrl() }}" rel="prev" aria-label="Previous">«</a>
                                </li>
                            @endif

                            @foreach ($locataires->getUrlRange(1, $locataires->lastPage()) as $page => $url)
                                @if ($page == $locataires->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($locataires->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $locataires->nextPageUrl() }}" rel="next" aria-label="Next">»</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">»</span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Scripts nécessaires -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
$(document).ready(function() {
    // CSRF Token pour AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Notification SweetAlert2 pour succès
    @if(session('success'))
    Swal.fire({
        title: 'Succès !',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK',
    });
    @endif

    // Gestion des images dans la modal
    $('.preview-image').on('click', function() {
        const imgUrl = $(this).data('image');
        const title = $(this).data('title');

        $('#modalImage').attr('src', imgUrl);
        $('#imageModalLabel').text(title);

        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        imageModal.show();
    });

    // Confirmation de suppression Locataire
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Confirmer la suppression',
            text: "Êtes-vous sûr de vouloir supprimer ce locataire ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer!',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $(document).ready(function() {
    // Gestion de l'affichage des états des lieux
    $(document).on('click', '.view-etat-btn', function() {
        const etatLieuData = $(this).data('etat-lieu');
        const modal = $('#etatLieuModal');
        
        // Remplir le contenu de la modal
        $('#etatLieuContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h5>Informations générales</h5>
                    <hr>
                    <p><strong>Adresse du bien:</strong> ${etatLieuData.adresse_bien || 'Non renseigné'}</p>
                    <p><strong>Type de bien:</strong> ${etatLieuData.type_bien || 'Non renseigné'}</p>
                    <p><strong>Date de l'état:</strong> ${etatLieuData.date_etat || 'Non renseigné'}</p>
                    <p><strong>Nature de l'état:</strong> ${etatLieuData.nature_etat || 'Non renseigné'}</p>
                    <p><strong>Nom du locataire:</strong> ${etatLieuData.nom_locataire || 'Non renseigné'}</p>
                    <p><strong>Nom du propriétaire/agence:</strong> ${etatLieuData.nom_proprietaire || 'Non renseigné'}</p>
                </div>

                <div class="col-md-6">
                    <h5>Relevés des compteurs</h5>
                    <hr>
                    <p><strong>Type compteur:</strong> ${etatLieuData.type_compteur || 'Non renseigné'}</p>
                    <p><strong>Numéro compteur:</strong> ${etatLieuData.numero_compteur || 'Non renseigné'}</p>
                    <p><strong>Relevé entrée:</strong> ${etatLieuData.releve_entre || 'Non renseigné'}</p>
                    <p><strong>Relevé sortie:</strong> ${etatLieuData.releve_sorti || 'Non renseigné'}</p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h5>État des lieux par pièce</h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Élément</th>
                                    <th>État</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sol</td>
                                    <td>${etatLieuData.sol || 'Non renseigné'}</td>
                                </tr>
                                <tr>
                                    <td>Murs</td>
                                    <td>${etatLieuData.murs || 'Non renseigné'}</td>
                                </tr>
                                <tr>
                                    <td>Plafond</td>
                                    <td>${etatLieuData.plafond || 'Non renseigné'}</td>
                                </tr>
                                <tr>
                                    <td>Porte</td>
                                    <td>${etatLieuData.porte_entre || 'Non renseigné'}</td>
                                </tr>
                                <tr>
                                    <td>Interrupteurs</td>
                                    <td>${etatLieuData.interrupteur || 'Non renseigné'}</td>
                                </tr>
                                <tr>
                                    <td>Éclairage</td>
                                    <td>${etatLieuData.eclairage || 'Non renseigné'}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            ${etatLieuData.remarque ? `
            <div class="row mt-3">
                <div class="col-12">
                    <h5>Remarques</h5>
                    <hr>
                    <p>${etatLieuData.remarque}</p>
                </div>
            </div>
            ` : ''}
        `);
        
        // Afficher la modal
        modal.modal('show');
    });
});

    // Fonction pour afficher ou cacher le champ motif
    function toggleMotifField(status) {
        if (status === 'Inactif' || status === 'Pas sérieux') {
            $('#motifField').show();
            $('#motif').prop('required', true);
        } else {
            $('#motifField').hide();
            $('#motif').prop('required', false);
        }
    }

    // Gestion du changement de statut
    $('.change-status-btn').on('click', function() {
        const locataireId = $(this).data('locataire-id');
        const currentStatus = $(this).data('current-status');

        const form = $('#statusForm');
        form.attr('action', "{{ route('locataires.updateStatus', ['locataire' => 'LOCATAIRE_ID']) }}".replace('LOCATAIRE_ID', locataireId));

        const statusSelect = $('#newStatus');
        statusSelect.val(currentStatus);

        toggleMotifField(currentStatus);

        statusSelect.off('change').on('change', function() {
            toggleMotifField($(this).val());
        });

        const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    });

    // Validation du formulaire statut avant soumission
    $('#statusForm').on('submit', function(e) {
        const selectedStatus = $('#newStatus').val();
        const motifValue = $('#motif').val().trim();

        if ((selectedStatus === 'Inactif' || selectedStatus === 'Pas sérieux') && motifValue === '') {
            e.preventDefault();
            Swal.fire({
                title: 'Erreur de validation',
                text: 'Veuillez saisir un motif pour le statut sélectionné.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });

    // Gestion de l'envoi de rappel de paiement
    $('body').on('click', '.send-reminder-btn', function() {
        const button = $(this);
        const locataireId = button.data('locataire-id');
        const locataireEmail = button.data('locataire-email');
        
        // Afficher un modal avec les options de taux
        Swal.fire({
            title: 'Envoyer un rappel de paiement',
            html: `
                <div class="mb-3">
                    <label for="tauxMajoration" class="form-label">Taux de majoration :</label>
                    <select class="form-select" id="tauxMajoration">
                        <option value="0">Aucune majoration (0%)</option>
                        <option value="5">Majoration de 5%</option>
                        <option value="10">Majoration de 10%</option>
                        <option value="15">Majoration de 15%</option>
                        <option value="20">Majoration de 20%</option>
                    </select>
                </div>
                <p>Êtes-vous sûr de vouloir envoyer un rappel de paiement à <strong>${locataireEmail}</strong> ?</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3a7bd5',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, envoyer',
            cancelButtonText: 'Annuler',
            didOpen: () => {
                // Récupérer le montant du loyer via AJAX
                $.ajax({
                    url: "{{ route('locataires.getMontantLoyer') }}",
                    type: 'GET',
                    data: { locataire_id: locataireId },
                    success: function(response) {
                        $('#montantLoyer').val(response.montant + ' FCFA');
                        $('#nouveauMontant').val(response.montant + ' FCFA');
                    }
                });

                // Calculer le nouveau montant quand le taux change
                $('#tauxMajoration').on('change', function() {
                    const taux = parseFloat($(this).val()) || 0;
                    const montant = parseFloat($('#montantLoyer').val().replace(' FCFA', '')) || 0;
                    const nouveauMontant = montant * (1 + taux / 100);
                    $('#nouveauMontant').val(nouveauMontant.toFixed(0) + ' FCFA');
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const tauxMajoration = $('#tauxMajoration').val();
                
                button.prop('disabled', true);
                button.html('<i class="mdi mdi-loading mdi-spin"></i>');
                
                $.ajax({
                    url: "{{ route('locataires.sendPaymentReminder') }}",
                    type: 'POST',
                    data: {
                        locataire_id: locataireId,
                        email: locataireEmail,
                        taux_majoration: tauxMajoration // Ce paramètre est maintenant envoyé
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Succès!',
                            text: response.message || 'Le rappel a été envoyé avec succès',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Une erreur est survenue lors de l\'envoi';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Erreur!', errorMsg, 'error');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.html('<i class="mdi mdi-email-open"></i>Rappel');
                    }
                });
            }
        });
    });
});

$('body').on('click', '.generate-cash-code', function() {
    const locataireId = $(this).data('locataire-id');
    const button = $(this);
    
    button.prop('disabled', true);
    button.html('<i class="mdi mdi-loading mdi-spin"></i>');

    // D'abord demander le nombre de mois
    Swal.fire({
        title: 'Nombre de mois à payer',
        html: `
            <div class="mb-3">
                <label for="nombreMois" class="form-label">Combien de mois voulez-vous payer ?</label>
                <input type="number" class="form-control" id="nombreMois" min="1" value="1">
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Continuer',
        cancelButtonText: 'Annuler',
        preConfirm: () => {
            const mois = $('#nombreMois').val();
            if (!mois || mois < 1) {
                Swal.showValidationMessage('Veuillez entrer un nombre valide (au moins 1 mois)');
                return false;
            }
            return { mois: mois };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const nombreMois = result.value.mois;
            
            // Générer le code via AJAX
            $.ajax({
                url: "{{ route('paiements.generateCashCode') }}",
                type: 'POST',
                data: { 
                    locataire_id: locataireId,
                    nombre_mois: nombreMois
                },
                success: function(response) {
                    if (response.success) {
                        // Afficher le message de succès et lancer directement le scanner
                        Swal.fire({
                            title: 'Code généré',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Scanner le QR code',
                            showCancelButton: true,
                            cancelButtonText: 'Annuler'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                startQRScanner(locataireId, nombreMois);
                            }
                        });
                    } else {
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Une erreur est survenue';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    Swal.fire('Erreur', errorMsg, 'error');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<i class="mdi mdi-cash"></i> Espèces');
                }
            });
        } else {
            button.prop('disabled', false);
            button.html('<i class="mdi mdi-cash"></i> Espèces');
        }
    });
});

// Fonction pour démarrer le scan du QR code
function startQRScanner(locataireId, nombreMois) {
    let scanning = true; // Variable pour contrôler le scan
    
    const scannerModal = Swal.fire({
        title: 'Scanner le QR Code du locataire',
        html: `
            <div class="text-center">
                <div id="camera-status" class="mb-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                    <p class="mt-2">Demande d'accès à la caméra...</p>
                </div>
                <video id="qr-video" width="100%" style="border: 1px solid #ccc; display: none;"></video>
                <div id="qr-result" class="mt-2" style="display: none;">
                    <p class="text-success" style="display:none">Code détecté: <span id="qr-detected-code"></span></p>
                </div>
                <div id="camera-error" class="mt-2" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-circle"></i>
                        
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button id="enter-code-manually" class="btn btn-sm btn-secondary">
                    <i class="mdi mdi-keyboard"></i> Saisir le code manuellement
                </button>
                <button id="retry-camera" class="btn btn-sm btn-primary" style="display: none;">
                    <i class="mdi mdi-refresh"></i> Réessayer la caméra
                </button>
            </div>
        `,
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: async () => {
            const videoElement = document.getElementById('qr-video');
            const cameraStatus = document.getElementById('camera-status');
            const cameraError = document.getElementById('camera-error');
            const errorMessage = document.getElementById('error-message');
            const retryButton = document.getElementById('retry-camera');
            
            // Gestion du bouton de saisie manuelle
            $('#enter-code-manually').on('click', function() {
                scanning = false; // Arrêter le scan
                Swal.close(); // Fermer le modal
                showManualCodeInput(locataireId, nombreMois);
            });
            
            // Gestion du bouton retry
            $('#retry-camera').on('click', function() {
                initializeCamera();
            });
            
            async function initializeCamera() {
                try {
                    // Masquer les éléments d'erreur
                    cameraError.style.display = 'none';
                    retryButton.style.display = 'none';
                    cameraStatus.style.display = 'block';
                    videoElement.style.display = 'none';
                    
                    // Arrêter toute caméra existante
                    if (videoElement.srcObject) {
                        videoElement.srcObject.getTracks().forEach(track => track.stop());
                    }
                    
                    // Vérifier si l'API est disponible
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        throw new Error('API caméra non supportée par ce navigateur');
                    }
                    
                    // Différentes configurations à essayer
                    const constraints = [
                        { 
                            video: { 
                                facingMode: "environment",
                                width: { ideal: 1280 },
                                height: { ideal: 720 }
                            } 
                        },
                        { 
                            video: { 
                                facingMode: "environment",
                                width: { ideal: 640 },
                                height: { ideal: 480 }
                            } 
                        },
                        { 
                            video: { 
                                facingMode: "environment"
                            } 
                        },
                        { 
                            video: { 
                                facingMode: "user"
                            } 
                        },
                        { video: true }
                    ];
                    
                    let stream = null;
                    let lastError = null;
                    
                    for (const constraint of constraints) {
                        try {
                            console.log('Tentative avec:', constraint);
                            stream = await navigator.mediaDevices.getUserMedia(constraint);
                            break;
                        } catch (err) {
                            console.log('Échec avec constraint:', constraint, err);
                            lastError = err;
                            continue;
                        }
                    }
                    
                    if (!stream) {
                        throw lastError || new Error('Impossible d\'obtenir l\'accès à la caméra');
                    }
                    
                    // Succès - configurer la vidéo
                    videoElement.srcObject = stream;
                    cameraStatus.style.display = 'none';
                    videoElement.style.display = 'block';
                    
                    // Attendre que la vidéo soit prête
                    await new Promise((resolve, reject) => {
                        videoElement.onloadedmetadata = () => {
                            videoElement.play().then(resolve).catch(reject);
                        };
                        videoElement.onerror = reject;
                    });
                    
                    // Démarrer le scan
                    startScanning();
                    
                } catch (err) {
                    console.error("Erreur camera détaillée:", err);
                    handleCameraError(err);
                }
            }
            
            function handleCameraError(err) {
                cameraStatus.style.display = 'none';
                cameraError.style.display = 'block';
                retryButton.style.display = 'inline-block';
                
                let userMessage = 'Erreur inconnue';
                
                if (err.name === 'NotAllowedError') {
                    userMessage = 'Permission d\'accès à la caméra refusée. Veuillez autoriser l\'accès dans les paramètres de votre navigateur.';
                } else if (err.name === 'NotFoundError') {
                    userMessage = 'Aucune caméra trouvée sur cet appareil.';
                } else if (err.name === 'NotReadableError') {
                    userMessage = 'Caméra occupée par une autre application.';
                } else if (err.name === 'OverconstrainedError') {
                    userMessage = 'Caméra ne supporte pas les paramètres demandés.';
                } else if (err.name === 'AbortError') {
                    userMessage = 'Accès à la caméra interrompu.';
                } else if (err.name === 'NotSupportedError') {
                    userMessage = 'Caméra non supportée par ce navigateur.';
                } else if (err.message) {
                    userMessage = err.message;
                }
                
                errorMessage.textContent = userMessage;
                
                // Auto-focus sur le bouton manuel après quelques secondes
                setTimeout(() => {
                    $('#enter-code-manually').addClass('btn-primary').removeClass('btn-secondary');
                }, 2000);
            }
            
            function startScanning() {
                const canvasElement = document.createElement('canvas');
                const canvasContext = canvasElement.getContext('2d');
                
                function scanQR() {
                    // Vérifier si le scan doit continuer
                    if (!scanning) return;
                    
                    try {
                        if (videoElement.readyState === videoElement.HAVE_ENOUGH_DATA) {
                            canvasElement.height = videoElement.videoHeight;
                            canvasElement.width = videoElement.videoWidth;
                            canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
                            
                            const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
                            const code = jsQR(imageData.data, imageData.width, imageData.height);
                            
                            if (code) {
                                scanning = false; // Arrêter le scan
                                document.getElementById('qr-detected-code').textContent = code.data;
                                document.getElementById('qr-result').style.display = 'block';
                                
                                // Feedback visuel
                                videoElement.style.border = '3px solid #28a745';
                                
                                // Fermer le scanner et valider le code
                                setTimeout(() => {
                                    Swal.close();
                                    verifyAndSubmitPayment(locataireId, code.data, nombreMois);
                                }, 1000);
                            } else {
                                requestAnimationFrame(scanQR);
                            }
                        } else {
                            requestAnimationFrame(scanQR);
                        }
                    } catch (err) {
                        console.error('Erreur pendant le scan:', err);
                        requestAnimationFrame(scanQR);
                    }
                }
                
                scanQR();
            }
            
            // Initialiser la caméra
            await initializeCamera();
        },
        willClose: () => {
            // Arrêter le scan et la caméra quand le modal se ferme
            scanning = false;
            const videoElement = document.getElementById('qr-video');
            if (videoElement && videoElement.srcObject) {
                videoElement.srcObject.getTracks().forEach(track => track.stop());
            }
        }
    });
}

// Fonction pour afficher l'input de code manuel (inchangée)
function showManualCodeInput(locataireId, nombreMois) {
    Swal.fire({
        title: 'Saisie du code manuelle',
        html: `
            <div class="mb-3">
                <label for="cashVerificationCode" class="form-label">
                    Entrez le code à 6 caractères du locataire :
                </label>
                <input type="text" class="form-control form-control-lg text-center" 
                       id="cashVerificationCode" 
                       placeholder="XXXXXX" 
                       maxlength="6"
                       style="letter-spacing: 0.5em; font-size: 1.2em; font-weight: bold;">
                <small class="text-muted">Le code contient 6 caractères (lettres et chiffres)</small>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Valider',
        cancelButtonText: 'Annuler',
        didOpen: () => {
            const input = document.getElementById('cashVerificationCode');
            input.focus();
            
            // Convertir en majuscules automatiquement
            input.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            // Valider avec Enter
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.value.length === 6) {
                    Swal.clickConfirm();
                }
            });
        },
        preConfirm: () => {
            const code = document.getElementById('cashVerificationCode').value.trim();
            if (!code) {
                Swal.showValidationMessage('Veuillez entrer un code');
                return false;
            }
            if (code.length !== 6) {
                Swal.showValidationMessage('Le code doit contenir exactement 6 caractères');
                return false;
            }
            return { code: code };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            verifyAndSubmitPayment(locataireId, result.value.code, nombreMois);
        }
    });
}

// Fonction pour vérifier et soumettre le paiement (inchangée)
function verifyAndSubmitPayment(locataireId, code, nombreMois = 1) {
    Swal.fire({
        title: 'Validation en cours',
        html: `
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <p>Vérification du code <strong>${code}</strong>...</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            $.ajax({
                url: "{{ route('paiements.verifyCashCode') }}",
                type: 'POST',
                data: { 
                    locataire_id: locataireId,
                    code: code,
                    nombre_mois: nombreMois
                },
                timeout: 30000, // 30 secondes de timeout
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Paiement réussi !',
                            html: `
                                <div class="text-center">
                                    <i class="mdi mdi-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="mt-3"><strong>${response.message}</strong></p>
                                    <div class="alert alert-success">
                                        <p class="mb-1">Mois payés: <strong>${response.mois_payes}</strong></p>
                                        <p class="mb-0">Montant total: <strong>${response.montant_total} FCFA</strong></p>
                                    </div>
                                </div>
                            `,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Code invalide',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Réessayer',
                            showCancelButton: true,
                            cancelButtonText: 'Annuler'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showManualCodeInput(locataireId, nombreMois);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Erreur lors de la vérification du code';
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMsg = 'Problème de connexion réseau';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Erreur du serveur';
                    }
                    
                    Swal.fire({
                        title: 'Erreur',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Réessayer',
                        showCancelButton: true,
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showManualCodeInput(locataireId, nombreMois);
                        }
                    });
                }
            });
        }
    });
}

// Gestion du bouton de vérification manuelle existant
$('body').on('click', '.verify-cash-code', function() {
    const locataireId = $(this).data('locataire-id');
    startQRScanner(locataireId);
});
function submitCashPayment(locataireId, code) {
    const form = $('<form>', {
        'method': 'POST',
        'action': "{{ route('locataire.paiements.store', ['locataire' => 'LOCATAIRE_ID']) }}".replace('LOCATAIRE_ID', locataireId)
    }).append($('<input>', {
        'type': 'hidden',
        'name': '_token',
        'value': '{{ csrf_token() }}'
    })).append($('<input>', {
        'type': 'hidden',
        'name': 'methode_paiement',
        'value': 'Espèces'
    })).append($('<input>', {
        'type': 'hidden',
        'name': 'verif_espece',
        'value': code
    }));

    $('body').append(form);
    form.submit();
}
</script>
<script>
// Gestion de l'attribution de bien
$(document).on('click', '.attribuer-bien-btn', function() {
    const locataireId = $(this).data('locataire-id');
    const button = $(this);
    
    button.prop('disabled', true);
    button.html('<i class="mdi mdi-loading mdi-spin"></i>');

    // Charger la liste des biens disponibles
    $.get("{{ route('biens.disponibles.agence') }}", function(biens) {
        button.prop('disabled', false);
        button.html('<i class="mdi mdi-home-plus"></i>');
        
        if (biens.length === 0) {
            Swal.fire({
                title: 'Aucun bien disponible',
                text: 'Il n\'y a actuellement aucun bien disponible à attribuer.',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Préparer les options pour le select
        let options = '';
        biens.forEach(bien => {
            options += `<option value="${bien.id}">${bien.type} - ${bien.commune} (${bien.prix} FCFA/mois)</option>`;
        });

        // Afficher le popup de sélection
        Swal.fire({
            title: 'Attribuer un nouveau bien',
            html: `
                <form id="attributionForm">
                    <div class="form-group">
                        <label for="bienSelect" class="form-label">Sélectionnez un bien :</label>
                        <select class="form-select" id="bienSelect" required>
                            <option value="">-- Choisir un bien --</option>
                            ${options}
                        </select>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonColor: '#02245b',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Attribuer',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const bienId = $('#bienSelect').val();
                if (!bienId) {
                    Swal.showValidationMessage('Veuillez sélectionner un bien');
                    return false;
                }
                return { bienId };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Envoyer la requête d'attribution
                button.prop('disabled', true);
                button.html('<i class="mdi mdi-loading mdi-spin"></i>');
                
                $.ajax({
                    url: "{{ route('locataire.attribuer-bien.agence', ['locataire' => 'LOCATAIRE_ID']) }}".replace('LOCATAIRE_ID', locataireId),
                    method: 'POST',
                    data: {
                        bien_id: result.value.bienId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Succès !',
                            text: response.success,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Erreur !',
                            text: xhr.responseJSON.error || 'Une erreur est survenue',
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.html('<i class="mdi mdi-home-plus"></i>');
                    }
                });
            }
        });
    }).fail(function() {
        button.prop('disabled', false);
        button.html('<i class="mdi mdi-home-plus"></i>');
        
        Swal.fire({
            title: 'Erreur !',
            text: 'Impossible de charger la liste des biens',
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    });
});
</script>
  <script>
$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        let hasResults = false;

        $('table tbody tr').each(function() {
            const rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchText)) {
                $(this).show();
                hasResults = true;
            } else {
                $(this).hide();
            }
        });

        // Affichage message "Aucun résultat"
        if (!hasResults) {
            if ($('.no-results-message').length === 0) {
                $('table tbody').append(`
                    <tr class="no-results-message">
                        <td colspan="18" class="text-center py-4">
                            <div class="alert alert-warning mb-0">
                                Aucun bien ne correspond à votre recherche.
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('.no-results-message').remove();
        }
    });
});

</script>

{{-- Attribuer un agent de recouvrement a un locataire pour l'etat des lieux  --}}
<script>
    // Gestion de l'attribution d'agent de recouvrement
$(document).on('click', '.assign-comptable-btn', function() {
    const locataireId = $(this).data('locataire-id');
    const button = $(this);
    
    // Afficher le modal
    const modal = $('#assignComptableModal');
    $('#locataireId').val(locataireId);
    
    // Charger la liste des agents de recouvrement
    $.ajax({
        url: "{{ route('comptables.recouvrement') }}",
        type: 'GET',
        data: {
            agence_id: "{{ Auth::guard('agence')->user()->code_id }}"
        },
        beforeSend: function() {
            $('#comptableSelect').html('<option value="">Chargement en cours...</option>');
        },
        success: function(response) {
            if (response.length > 0) {
                let options = '<option value="">-- Choisir un agent --</option>';
                response.forEach(comptable => {
                    options += `<option value="${comptable.id}">${comptable.name} ${comptable.prenom} (${comptable.contact})</option>`;
                });
                $('#comptableSelect').html(options);
            } else {
                $('#comptableSelect').html('<option value="">Aucun agent disponible</option>');
                Swal.fire({
                    title: 'Aucun agent disponible',
                    text: 'Aucun agent de recouvrement n\'est disponible dans votre agence.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            modal.modal('show');
        },
        error: function() {
            Swal.fire({
                title: 'Erreur',
                text: 'Impossible de charger la liste des agents',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

// Soumission du formulaire d'attribution
// Soumission du formulaire d'attribution
// Gestion de l'attribution d'agent de recouvrement avec SweetAlert2
$(document).on('click', '.assign-comptable-btn', function() {
    const locataireId = $(this).data('locataire-id');
    const button = $(this);
    
    // Afficher un indicateur de chargement
    button.prop('disabled', true);
    button.html('<i class="mdi mdi-loading mdi-spin"></i>');
    
    // Charger la liste des agents de recouvrement
    $.ajax({
        url: "{{ route('comptables.recouvrement') }}",
        type: 'GET',
        data: {
            agence_id: "{{ Auth::guard('agence')->user()->code_id }}"
        },
        success: function(response) {
            button.prop('disabled', false);
            button.html('<i class="mdi mdi-account-plus"></i> Attribuer');
            
            if (response.length === 0) {
                Swal.fire({
                    title: 'Aucun agent disponible',
                    text: 'Aucun agent de recouvrement n\'est disponible dans votre agence.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Préparer les options pour le select
            let options = '';
            response.forEach(comptable => {
                options += `<option value="${comptable.id}">${comptable.name} ${comptable.prenom} (${comptable.contact})</option>`;
            });
            
            // Afficher le popup SweetAlert2
            Swal.fire({
                title: 'Attribuer un agent de recouvrement',
                html: `
                    <form id="swalAssignForm">
                        <input type="hidden" name="locataire_id" value="${locataireId}">
                        <div class="mb-3">
                            <label for="swalComptableSelect" class="form-label">Sélectionnez un agent :</label>
                            <select class="form-select" id="swalComptableSelect" name="comptable_id" required>
                                <option value="">-- Choisir un agent --</option>
                                ${options}
                            </select>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Attribuer',
                cancelButtonText: 'Annuler',
                focusConfirm: false,
                preConfirm: () => {
                    const selectedId = $('#swalComptableSelect').val();
                    if (!selectedId) {
                        Swal.showValidationMessage('Veuillez sélectionner un agent');
                        return false;
                    }
                    return {
                        locataire_id: locataireId,
                        comptable_id: selectedId
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Envoyer la requête d'attribution
                    button.prop('disabled', true);
                    button.html('<i class="mdi mdi-loading mdi-spin"></i>');
                    
                    $.ajax({
                        url: "{{ route('locataire.assign.comptable') }}",
                        type: 'POST',
                        data: result.value,
                        success: function(response) {
                            // Trouver le bouton "Attribuer" correspondant à ce locataire
                            const assignBtn = $(`.assign-comptable-btn[data-locataire-id="${locataireId}"]`);
                            
                            // Remplacer le bouton par le badge avec le nom du comptable
                            assignBtn.replaceWith(`
                                <span class="badge bg-primary text-white" style="font-size: 20px">
                                    <i class="mdi mdi-account-check"></i> 
                                    ${response.comptable.name} ${response.comptable.prenom}
                                </span>
                            `);
                            
                            Swal.fire({
                                title: 'Succès !',
                                text: response.success,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        },
                        error: function(xhr) {
                            let errorMsg = 'Une erreur est survenue';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Erreur !',
                                text: errorMsg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function() {
                            button.prop('disabled', false);
                            button.html('<i class="mdi mdi-account-plus"></i> Attribuer');
                        }
                    });
                }
            });
        },
        error: function() {
            button.prop('disabled', false);
            button.html('<i class="mdi mdi-account-plus"></i> Attribuer');
            
            Swal.fire({
                title: 'Erreur',
                text: 'Impossible de charger la liste des agents',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>
<style>
#searchInput {
    padding: 10px 15px;
    border-radius: 20px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.swal2-popup .form-select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
}

.swal2-popup .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

#searchInput:focus {
    border-color: #4b7bec;
    box-shadow: 0 2px 10px rgba(75, 123, 236, 0.3);
    outline: none;
}

.empty-state .empty-icon {
    font-size: 3rem;
    color: #a5b1c2;
    margin-bottom: 1rem;
}
</style> 
@endsection