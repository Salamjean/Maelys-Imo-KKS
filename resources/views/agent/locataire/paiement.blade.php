@extends('comptable.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .send-reminder-btn {
        color: white;
        background-color: #3a7bd5;
        border-color: #3a7bd5;
    }

    .send-reminder-btn:hover {
        background-color: #2c5fb3;
        border-color: #2c5fb3;
    }
    
    .pagination {
        --bs-pagination-color: #02245b;
        --bs-pagination-bg: #fff;
        --bs-pagination-border-color: #dee2e6;
        --bs-pagination-hover-color: #fff;
        --bs-pagination-hover-bg: #02245b;
        --bs-pagination-hover-border-color: #02245b;
        --bs-pagination-focus-color: #fff;
        --bs-pagination-focus-bg: #02245b;
        --bs-pagination-focus-box-shadow: 0 0 0 0.25rem rgba(2, 36, 91, 0.25);
        --bs-pagination-active-color: #fff;
        --bs-pagination-active-bg: #02245b;
        --bs-pagination-active-border-color: #02245b;
        --bs-pagination-disabled-color: #6c757d;
        --bs-pagination-disabled-bg: #fff;
        --bs-pagination-disabled-border-color: #dee2e6;
    }

    .pagination-rounded .page-item:first-child .page-link {
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
    }

    .pagination-rounded .page-item:last-child .page-link {
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    .page-link {
        padding: 0.5rem 1rem;
        margin: 0 0.15rem;
        border-radius: 50%;
        min-width: 40px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .page-item.active .page-link {
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .page-item:not(.active):not(.disabled) .page-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .preview-image:hover {
        transform: scale(1.05);
        transition: transform 0.3s ease;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
</style>

<div class="col-lg-12 stretch-card">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Locataires qui n'ont pas encore régler le loyer du mois en cours </h4>
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
                                        <option value="Pas sérieux">Pas sérieux</option>
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
           

            <div class="table-responsive pt-3">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: #02245b; color: white;">
                        <tr class="text-center">
                            <th>Agence</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Contact</th>
                            <th>Profession</th>
                            <th>Adresse complète</th>
                            {{-- <th>Pièce d'identité</th>
                            <th>Attestation de travail</th> --}}
                            <th>Statut</th>
                            <th>Paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locataires as $locataire)
                            <tr class="text-center pt-3" style="height: 30px">
                                <td>
                                    {{ $locataire->agence->name ?? 'Non attribuée' }}
                                </td>
                                <td><strong>{{ $locataire->name }}</strong></td>
                                <td><strong>{{ $locataire->prenom }}</strong></td>
                                <td>{{ $locataire->contact }}</td>
                                <td>{{ $locataire->profession }}</td>
                                <td>{{ $locataire->adresse }}</td>
                                {{-- <td>
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
                                    @if($locataire->attestation)
                                        <button class="btn btn-sm btn-info preview-image"
                                                data-image="{{ asset('storage/'.$locataire->attestation) }}"
                                                data-title="Attestation de travail de {{ $locataire->name }}">
                                            Voir
                                        </button>
                                    @else
                                        <span class="text-muted">Non fournie</span>
                                    @endif
                                </td> --}}
                                
                                <td>
                                    @if($locataire->status == 'Actif')
                                        <span class="badge bg-success text-white">Actif</span>
                                    @elseif($locataire->status == 'Inactif')
                                        <span class="badge bg-danger text-white">Inactif</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pas sérieux</span>
                                    @endif
                                </td>
                                {{-- <td>
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
                                </td> --}}
                                {{-- <td class="text-center ">
                                    <div class="btn-group gap-2" role="group">
                                        <a href="{{ route('locataire.edit', $locataire->id) }}" class="btn btn-sm btn-warning" title="Modifier">
                                            <i class="mdi mdi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger change-status-btn"
                                                data-locataire-id="{{ $locataire->id }}"
                                                data-current-status="{{ $locataire->status }}"
                                                title="Changer statut">
                                            <i class="mdi mdi-account-convert"></i>
                                        </button> --}}
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
                                    @if($locataire->show_reminder_button)
                                        <button class="btn btn-sm btn-primary send-reminder-btn"
                                                data-locataire-id="{{ $locataire->id }}"
                                                data-locataire-email="{{ $locataire->email }}"
                                                title="Envoyer un rappel de paiement">
                                                <i class="mdi mdi-email-open"></i> Rappel paiement
                                        </button>
                                    @endif

                                        <button class="btn btn-sm btn-success generate-cash-code"
                                                data-locataire-id="{{ $locataire->id }}"
                                                title="Générer un code pour paiement en espèces">
                                                <i class="mdi mdi-cash"></i> Code Espèces
                                        </button>

                                        <button class="btn btn-sm btn-warning verify-cash-code"
                                            data-locataire-id="{{ $locataire->id }}"
                                            title="Saisir le code de vérification">
                                            <i class="mdi mdi-key"></i>
                                        </button>
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
                        button.html('<i class="mdi mdi-email-send"></i>');
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

    // D'abord générer le code
    $.ajax({
        url: "{{ route('paiements.generateCashCode') }}",
        type: 'POST',
        data: { locataire_id: locataireId },
        success: function(response) {
            if (response.success) {
                // Afficher le champ de saisie après envoi réussi
                Swal.fire({
                    title: 'Code envoyé',
                    html: `
                        <p>${response.message}</p>
                        <div class="mb-3 mt-3">
                            <label for="cashVerificationCode" class="form-label">
                                Entrez le code reçu par le locataire :
                            </label>
                            <input type="text" class="form-control" id="cashVerificationCode" 
                                   placeholder="Code à 6 caractères" maxlength="6">
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Valider le paiement',
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
                        // Vérifier le code
                        verifyAndSubmitPayment(locataireId, result.value.code);
                    }
                });
            } else {
                Swal.fire('Erreur', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur lors de la génération du code', 'error');
        },
        complete: function() {
            button.prop('disabled', false);
            button.html('<i class="mdi mdi-cash"></i> Code Espèces');
        }
    });
});

function verifyAndSubmitPayment(locataireId, code) {
    Swal.fire({
        title: 'Validation en cours',
        html: 'Vérification du code...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ route('paiements.verifyCashCode') }}",
        type: 'POST',
        data: { 
            locataire_id: locataireId,
            code: code 
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Paiement réussi',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
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

// Gestion du nouveau bouton de vérification
$('body').on('click', '.verify-cash-code', function() {
    const locataireId = $(this).data('locataire-id');
    
    Swal.fire({
        title: 'Validation du paiement en espèces',
        html: `
            <div class="mb-3 mt-3">
                <label for="cashVerificationCode" class="form-label">
                    Entrez le code reçu par le locataire :
                </label>
                <input type="text" class="form-control" id="cashVerificationCode" 
                       placeholder="Code à 6 caractères" maxlength="6">
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Valider le paiement',
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
            verifyAndSubmitPayment(locataireId, result.value.code);
        }
    });
});

// Fonction pour vérifier et soumettre le paiement (existante)
function verifyAndSubmitPayment(locataireId, code) {
    Swal.fire({
        title: 'Validation en cours',
        html: 'Vérification du code...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ route('paiements.verifyCashCode') }}",
        type: 'POST',
        data: { 
            locataire_id: locataireId,
            code: code 
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Paiement réussi',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
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
</script>

@endsection