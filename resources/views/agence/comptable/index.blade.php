@extends('agence.layouts.template')
@section('content')
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
</style>
<div class="col-lg-12 stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title text-center">Comptable inscrire pour votre agence</h4>
        <p class="card-description text-center">
          Listes des comptables de votre agence
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
        <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un locataire...">
            </div>
        <div class="table-responsive pt-3">
          <table class="table table-bordered table-hover">
            <thead style="background-color: #02245b; color: white;">
                <tr class="text-center">
                    <th>ID de l'agent</th>
                    <th>Nom de l'agent</th>
                    <th>Email</th>
                    <th>Lieu de résidence</th>
                    <th>Contact</th>
                    <th>Date de naissance</th>
                    <th>Rôle de l'agent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comptables as $comptable)
                    <tr class="text-center pt-3" style="height: 30px">
                        <td>{{ $comptable->code_id }}</td>
                        <td ><strong>{{ $comptable->name. ' '. $comptable->prenom }}</strong></td>
                        <td>{{ $comptable->email }}</td>
                        <td>{{ $comptable->commune }}</td>
                        <td>{{ $comptable->contact }}</td>
                        <td>{{ $comptable->date_naissance }}</td>
                        <td>{{ $comptable->user_type }}</td>
                        <td class="text-center">
                            <div class="btn-group " role="group" style="gap: 10px">
                                <a href="{{ route('accounting.edit', $comptable) }}" class="btn btn-sm btn-warning" title="Modifier">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                <form action="{{ route('accounting.destroy.agence', $comptable->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" title="Supprimer">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="alert alert-info">
                                Aucune agent inscrire.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
          </table>
          
          @if($comptables->hasPages())
<div class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-rounded">
            {{-- Previous Page Link --}}
            @if ($comptables->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $comptables->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($comptables->getUrlRange(1, $comptables->lastPage()) as $page => $url)
                @if ($page == $comptables->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($comptables->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $comptables->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Notification SweetAlert2 modifiée pour ressembler à la confirmation
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
    
        // Gestion des images
        $('.preview-image').on('click', function() {
            const imgUrl = $(this).data('image');
            $('#modalImage').attr('src', imgUrl);
            $('#imageModal').modal('show');
        });
    
        // Confirmation de suppression (inchangé)
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Confirmer la suppression',
                text: "Êtes-vous sûr de vouloir supprimer cet comptable ?",
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
<style>
.preview-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
</style>
@endsection