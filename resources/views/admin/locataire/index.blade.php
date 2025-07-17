@extends('admin.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Boutons personnalisés */
    .send-reminder-btn {
        background-color: #3a7bd5;
        border-color: #3a7bd5;
    }
    .send-reminder-btn:hover {
        background-color: #2c5fb3;
        border-color: #2c5fb3;
    }
    
    /* Pagination personnalisée */
    .pagination.pagination-custom {
        --bs-pagination-color: #02245b;
        --bs-pagination-hover-color: #fff;
        --bs-pagination-hover-bg: #02245b;
        --bs-pagination-hover-border-color: #02245b;
        --bs-pagination-focus-color: #fff;
        --bs-pagination-focus-bg: #02245b;
        --bs-pagination-active-bg: #02245b;
        --bs-pagination-active-border-color: #02245b;
    }
    
    .pagination-rounded .page-item:first-child .page-link {
        border-radius: 20px 0 0 20px;
    }
    
    .pagination-rounded .page-item:last-child .page-link {
        border-radius: 0 20px 20px 0;
    }
    
    .pagination-rounded .page-link {
        padding: 0.5rem 1rem;
        margin: 0 0.15rem;
        min-width: 40px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .pagination-rounded .page-item.active .page-link {
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .pagination-rounded .page-item:not(.active):not(.disabled) .page-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    /* Images preview */
    .preview-image {
        transition: all 0.3s ease;
    }
    
    .preview-image:hover {
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    
    /* Tableau */
    .table-custom thead {
        background-color: #02245b;
        color: white;
    }
    
    /* Modal image */
    .modal-image {
        max-height: 80vh;
        width: auto;
    }
    
    /* Hauteur des lignes du tableau */
    .table-row-custom {
        height: 30px;
    }
</style>

<div class="col-12 grid-margin stretch-card mb-4">
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
                        <div class="modal-header text-white" style="background-color: #02245b;">
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


           <!-- Modal pour afficher l'état des lieux -->
            <div class="modal fade" id="etatLieuModal" tabindex="-1" aria-labelledby="etatLieuModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #02245b;">
                            <h5 class="modal-title" id="etatLieuModalLabel">Détails de l'état des lieux</h5>
                            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="etatLieuContent">
                            <!-- Le contenu sera généré dynamiquement par JavaScript -->
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
                        <div class="modal-header  text-white" style="background-color: #02245b;">
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
                                <button type="submit" class="btn text-white " style="background-color: #02245b;">Enregistrer</button>
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
                            <th>Faire l'état des lieux</th>
                            <th>Paiement</th>
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
                                            Voir la pièce
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
                                    <div class="btn-group gap-10" role="group">
                                        <a href="{{ route('locataire.admin.edit', $locataire->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger change-status-btn"
                                                data-locataire-id="{{ $locataire->id }}"
                                                data-current-status="{{ $locataire->status }}"
                                                title="Changer statut">
                                            <i class="mdi mdi-account-convert"></i>
                                        </button>
                                         @if(is_null($locataire->bien_id) && $locataire->status === 'Inactif')
                                            <button class="btn btn-sm btn-primary attribuer-bien-btn"
                                                    data-locataire-id="{{ $locataire->id }}"
                                                    title="Attribuer un bien">
                                                <i class="mdi mdi-home-plus"></i>
                                            </button>
                                        @endif
                                        {{-- <form action="#" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger delete-btn" title="Supprimer">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form> --}}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $etatExiste = App\Models\EtatLieu::where('locataire_id', $locataire->code_id)->first();
                                    @endphp
                                                            
                                    @if($etatExiste)
                                        <button class="btn btn-sm btn-info view-etat-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#etatLieuModal"
                                                data-etat-lieu="{{ json_encode($etatExiste) }}"
                                                title="Voir l'état des lieux">
                                            <i class="mdi mdi-eye"></i> Voir
                                        </button>
                                    @else
                                        <a href="{{ route('locataire.admin.etat', $locataire->id) }}" 
                                        class="btn btn-sm btn-warning" 
                                        title="Créer l'état des lieux">
                                            <i class="mdi mdi-file-document-edit"></i> Créer
                                        </a>
                                    @endif
                                </td>

                               <td>
                                    @if($locataire->status === 'Actif') <!-- Vérifiez le statut du locataire -->
                                        @if($locataire->show_reminder_button)
                                            <button class="btn btn-sm btn-primary send-reminder-btn"
                                                    data-locataire-id="{{ $locataire->id }}"
                                                    data-locataire-email="{{ $locataire->email }}"
                                                    title="Envoyer un rappel de paiement">
                                                <i class="mdi mdi-email-open"></i> Rappel 
                                            </button>
                                        @endif

                                        <button class="btn btn-sm btn-success generate-cash-code"
                                                data-locataire-id="{{ $locataire->id }}"
                                                title="Générer un code pour paiement en espèces">
                                            <i class="mdi mdi-cash"></i>Espèces
                                        </button>
                                        
                                        <button class="btn btn-sm btn-warning verify-cash-code"
                                                data-locataire-id="{{ $locataire->id }}"
                                                title="Saisir le code de vérification">
                                            <i class="mdi mdi-key"></i>
                                        </button>
                                    @else
                                        <span class="text-danger">Locataire inactif - aucune action disponible.</span>
                                    @endif
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
        form.attr('action', "{{ route('locataire.admin.updateStatus', ['locataire' => 'LOCATAIRE_ID']) }}".replace('LOCATAIRE_ID', locataireId));

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
// Gestion de la génération du code pour paiement en espèces
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
// Modifiez la fonction startQRScanner comme ceci :
function startQRScanner(locataireId, nombreMois) {
    const scannerModal = Swal.fire({
        title: 'Scanner le QR Code du locataire',
        html: `
            <div class="text-center">
                <video id="qr-video" width="100%" style="border: 1px solid #ccc;"></video>
                <div id="qr-result" class="mt-2" style="display: none;">
                    <p>Code détecté: <span id="qr-detected-code"></span></p>
                </div>
            </div>
            <div class="mt-3">
                <button id="enter-code-manually" class="btn btn-sm btn-secondary">
                    <i class="mdi mdi-keyboard"></i> Saisir le code manuellement
                </button>
            </div>
        `,
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        showConfirmButton: false,
        didOpen: async () => {
            const videoElement = document.getElementById('qr-video');
            const qrResult = document.getElementById('qr-result');
            const qrDetectedCode = document.getElementById('qr-detected-code');
            
            // Gestion du bouton de saisie manuelle
            $('#enter-code-manually').on('click', function() {
                scannerModal.close();
                showManualCodeInput(locataireId, nombreMois);
            });
            
            try {
                // Vérifier d'abord les permissions
                const permissionStatus = await navigator.permissions.query({ name: 'camera' });
                
                if (permissionStatus.state === 'denied') {
                    throw new Error('Permission refusée');
                }
                
                // Utilisation de la caméra
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: "environment",
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    } 
                });
                
                videoElement.srcObject = stream;
                videoElement.play();
                
                // Créer un canvas pour la détection
                const canvasElement = document.createElement('canvas');
                const canvasContext = canvasElement.getContext('2d');
                
                function scanQR() {
                    if (!scannerModal.isVisible()) return;
                    
                    if (videoElement.readyState === videoElement.HAVE_ENOUGH_DATA) {
                        canvasElement.height = videoElement.videoHeight;
                        canvasElement.width = videoElement.videoWidth;
                        canvasContext.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height);
                        
                        const imageData = canvasContext.getImageData(0, 0, canvasElement.width, canvasElement.height);
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        
                        if (code) {
                            qrDetectedCode.textContent = code.data;
                            qrResult.style.display = 'block';
                            
                            // Fermer le scanner après 1 seconde et valider le code
                            setTimeout(() => {
                                scannerModal.close();
                                verifyAndSubmitPayment(locataireId, code.data, nombreMois);
                            }, 1000);
                        } else {
                            requestAnimationFrame(scanQR);
                        }
                    } else {
                        requestAnimationFrame(scanQR);
                    }
                }
                
                scanQR();
            } catch (err) {
                console.error("Erreur camera: ", err);
                scannerModal.close();
                
                // Afficher un message plus clair selon le type d'erreur
                let errorMessage = 'Impossible d\'accéder à la caméra.';
                
                if (err.name === 'NotAllowedError') {
                    errorMessage = 'Permission d\'accès à la caméra refusée.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage = 'Aucun périphérique de caméra trouvé.';
                }
                
                Swal.fire({
                    title: 'Erreur',
                    text: `${errorMessage} Veuillez saisir le code manuellement.`,
                    icon: 'error'
                }).then(() => {
                    showManualCodeInput(locataireId, nombreMois);
                });
            }
        },
        willClose: () => {
            // Arrêter la caméra quand le modal se ferme
            const videoElement = document.getElementById('qr-video');
            if (videoElement && videoElement.srcObject) {
                videoElement.srcObject.getTracks().forEach(track => track.stop());
            }
        }
    });
}

// Fonction pour afficher l'input de code manuel
function showManualCodeInput(locataireId, nombreMois) {
    Swal.fire({
        title: 'Saisie du code manuelle',
        html: `
            <div class="mb-3">
                <label for="cashVerificationCode" class="form-label">
                    Entrez le code à 6 caractères du locataire :
                </label>
                <input type="text" class="form-control" id="cashVerificationCode" 
                       placeholder="Code à 6 caractères" maxlength="6">
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Valider',
        cancelButtonText: 'Annuler',
        preConfirm: () => {
            const code = $('#cashVerificationCode').val().trim();
            if (!code || code.length !== 6) {
                Swal.showValidationMessage('Veuillez entrer un code valide (6 caractères)');
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

// Fonction pour vérifier et soumettre le paiement
function verifyAndSubmitPayment(locataireId, code, nombreMois = 1) {
    Swal.fire({
        title: 'Validation en cours',
        html: 'Vérification du code...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            
            $.ajax({
                url: "{{ route('paiements.verifyCashCode') }}",
                type: 'POST',
                data: { 
                    locataire_id: locataireId,
                    code: code,
                    nombre_mois: nombreMois
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Paiement réussi',
                            html: `
                                <p>${response.message}</p>
                                <p>Mois payés: ${response.mois_payes}</p>
                                <p>Montant total: ${response.montant_total} FCFA</p>
                            `,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON?.message || 'Erreur lors du paiement';
                    Swal.fire('Erreur', errorMsg, 'error');
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
    $.get("{{ route('biens.disponibles') }}", function(biens) {
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
                    url: "{{ route('locataire.attribuer-bien', ['locataire' => 'LOCATAIRE_ID']) }}".replace('LOCATAIRE_ID', locataireId),
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
<style>
#searchInput {
    padding: 10px 15px;
    border-radius: 20px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.3s;
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