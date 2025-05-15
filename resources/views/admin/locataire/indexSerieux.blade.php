@extends('admin.layouts.template')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Style personnalisé pour la pagination */
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
        <h4 class="card-title text-center">Locataires pas serieux</h4>
        <p class="card-description text-center">
          Listes des locataires pas serieux enregistrés dans le système.
        </p>

        <!-- Modal pour afficher les images -->
       <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="imageModalLabel">Visualisation de l'image</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center p-0">
                        <img id="modalImage" src="" class="img-fluid" style="max-height: 80vh; width: auto;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Fin du modal -->
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
                                <label for="motif" class="form-label">Motif (obligatoire si inactif)</label>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <div class="table-responsive pt-3">
            <table class="table table-bordered table-hover">
                <thead style="background-color: #02245b; color: white;">
                    <tr class="text-center">
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Contact</th>
                        <th>Profession</th>
                        <th>Adresse complète</th>
                        <th>Pièce d'identité</th>
                        <th>Attestation de travail</th>
                        <th>Agence</th>
                        <th>Statut</th>
                        <th>Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locataires as $locataire)
                        <tr class="text-center pt-3" style="height: 30px">
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
                                @if($locataire->attestation)
                                    <button class="btn btn-sm btn-info preview-image" 
                                            data-image="{{ asset('storage/'.$locataire->attestation) }}"
                                            data-title="Attestation de travail de {{ $locataire->name }}">
                                        Voir l'attestation
                                    </button>
                                @else
                                    <span class="text-muted">Non fournie</span>
                                @endif
                            </td>
                            <td>
                                {{ $locataire->agence->name ?? 'Non attribuée' }}
                            </td>
                            <td>
                                @if($locataire->status == 'Actif')
                                    <span class="badge bg-success text-white">Actif</span>
                                @elseif($locataire->status == 'Inactif')
                                    <span class="badge bg-danger text-white">Inactif</span>
                                @else
                                    <span class="badge bg-danger text-white">Pas sérieux</span>
                                @endif
                            </td>
                           <td>{{ $locataire->motif }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="alert alert-info">
                                    Aucun locataire non sérieux enregistré pour le moment.
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
                        {{-- Previous Page Link --}}
                        @if ($locataires->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&laquo;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $locataires->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($locataires->getUrlRange(1, $locataires->lastPage()) as $page => $url)
                            @if ($page == $locataires->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($locataires->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $locataires->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link" aria-hidden="true">&raquo;</span>
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
<!-- Scripts nécessaires -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
const updateStatusUrl = "{{ route('locataires.updateStatus', ['locataire' => 'LOCATAIRE_ID']) }}";

$(document).ready(function() {
    // Notification SweetAlert2 pour succès
    @if(session('success'))
    Swal.fire({
        title: 'Succès !',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK',
        allowOutsideClick: false
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

    // Confirmation de suppression
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
        const url = updateStatusUrl.replace('LOCATAIRE_ID', locataireId);
        form.attr('action', url);
        
        const statusSelect = $('#newStatus');
        statusSelect.val(currentStatus);

        // Appliquer directement l'affichage du motif selon statut actuel
        toggleMotifField(currentStatus);

        // Sur changement du select
        statusSelect.off('change').on('change', function() {
            toggleMotifField($(this).val());
        });

        const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
    });

    // Validation du formulaire avant soumission
    $('#statusForm').on('submit', function(e) {
        const selectedStatus = $('#newStatus').val();
        const motifValue = $('#motif').val().trim();

        if ((selectedStatus === 'Inactif' || selectedStatus === 'Pas sérieux') && motifValue === '') {
            e.preventDefault();
            Swal.fire({
                title: 'Erreur',
                text: 'Veuillez saisir un motif pour ce statut.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>
 
@endsection