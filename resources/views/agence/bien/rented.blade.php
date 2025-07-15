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
        <h4 class="card-title text-center">Gestion des biens immobiliers</h4>
        <p class="card-description text-center">
          Liste des biens déjà loués par des locataires
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
                    <th>Appartient à</th>
                    <th>Type</th>
                    <th>Superficie (m²)</th>
                    <th>Localisation</th>
                    <th>Chambres</th>
                    <th>Toilettes</th>
                    <th>Garage</th>
                    <th>Avance</th>
                    <th>Caution</th>
                    <th>Loyer</th>
                    <th>Montant Total</th>
                    <th>Date de loyer</th>
                    <th>Disponibilité</th>
                    <th>Photo principale</th>
                    <th>Photo supplementaire</th>
                    <th>Republier</th>
                </tr>
            </thead>
            <tbody>
                @forelse($biens as $bien)
                    @php
                        $rowClass = '';
                        if ($bien->type === 'Appartement') {
                            $rowClass = 'table-primary';
                        } elseif ($bien->type === 'Maison') {
                            $rowClass = 'table-danger';
                        } elseif ($bien->type === 'Bureau') {
                            $rowClass = 'table-success';
                        }
                    @endphp
                    
                    <tr class="{{ $rowClass }} text-center pt-3" style="height: 30px">
                        <td>
                            <strong>
                                @if($bien->proprietaire)
                                    {{ $bien->proprietaire->name.' '.$bien->proprietaire->prenom }}
                                @else
                                    Maelys-Imo
                                @endif
                            </strong>
                        </td>
                        <td ><strong>{{ $bien->type }}</strong></td>
                        <td>{{ $bien->superficie }}</td>
                        <td>{{ $bien->commune }}</td>
                        <td class="text-center">{{ $bien->nombre_de_chambres ?? 'N/A' }}</td>
                        <td class="text-center">{{ $bien->nombre_de_toilettes ?? 'N/A' }}</td>
                        <td class="text-center">{{ $bien->garage ?? 'N/A' }}</td>
                        <td>{{ $bien->avance ? number_format($bien->avance, 0, ',', ' ').' Mois' : 'N/A' }}</td>
                        <td>{{ $bien->caution ? number_format($bien->caution, 0, ',', ' ').' Mois' : 'N/A' }}</td>
                        <td class="font-weight-bold">{{ number_format($bien->prix, 0, ',', ' ').' FCFA' }}</td>
                        <td class="font-weight-bold">{{ number_format($bien->montant_total, 0, ',', ' ').' FCFA' }}</td>
                        <td><strong>{{ $bien->date_fixe }}</strong> de chaque mois</td>
                        <td class="text-center">
                            @if($bien->status == 'Disponible')
                                <span class="badge badge-success">Disponible</span>
                            @else
                                <span class="badge badge-danger">Loué</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($bien->image)
                                <img src="{{ asset('storage/'.$bien->image) }}" 
                                     class="img-thumbnail preview-image"
                                     data-image="{{ asset('storage/'.$bien->image) }}"
                                     style="width: 60px; height: 60px; cursor: zoom-in; object-fit: cover;">
                            @else
                                <span class="text-muted">Aucune</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($bien->image1)
                                <img src="{{ asset('storage/'.$bien->image1) }}" 
                                     class="img-thumbnail preview-image"
                                     data-image="{{ asset('storage/'.$bien->image1) }}"
                                     style="width: 60px; height: 60px; cursor: zoom-in; object-fit: cover;">
                            @else
                                <span class="text-muted">Aucune</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('bien.republier.agence', $bien->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success" title="Republier ce bien">
                                        <i class="mdi mdi-replay"></i> Republier
                                    </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="text-center py-4">
                            <div class="alert alert-info">
                                Aucun bien immobilier enregistré pour le moment.
                            </div>
                            
                        </td>
                    </tr>
                @endforelse
            </tbody>
          </table>
          
          @if($biens->hasPages())
<div class="mt-4 d-flex justify-content-center">
    <nav aria-label="Page navigation">
        <ul class="pagination pagination-rounded">
            {{-- Previous Page Link --}}
            @if ($biens->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $biens->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($biens->getUrlRange(1, $biens->lastPage()) as $page => $url)
                @if ($page == $biens->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($biens->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $biens->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
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
                text: "Êtes-vous sûr de vouloir supprimer ce bien ?",
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

   $('form[action*="republier"]').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    
    Swal.fire({
        title: 'Confirmer la republication',
        text: "Êtes-vous sûr de vouloir rendre ce bien disponible à nouveau ?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, republier!',
        cancelButtonText: 'Annuler',
        html: `
            <div class="form-group mt-3">
                <label for="locataireStatus">Statut du locataire :</label>
                <select class="form-control" id="locataireStatus" required>
                    <option value="">Sélectionnez un statut</option>
                    <option value="Inactif">Déménagement</option>
                    <option value="Pas sérieux">Pas sérieux</option>
                </select>
            </div>
            <div class="form-group mt-2" id="motifGroup" style="display: none;">
                <label for="motif">Motif :</label>
                <input type="text" class="form-control" id="motif" placeholder="Raison du changement de statut">
            </div>
        `,
        preConfirm: () => {
            const status = document.getElementById('locataireStatus').value;
            const motif = document.getElementById('motif')?.value;
            
            if (!status) {
                Swal.showValidationMessage('Veuillez sélectionner un statut');
                return false;
            }
            
            if ((status === 'Pas sérieux') && !motif) {
                Swal.showValidationMessage('Veuillez indiquer un motif');
                return false;
            }
            
            return { status, motif: motif || '' };
        },
        didOpen: () => {
            const statusSelect = document.getElementById('locataireStatus');
            const motifGroup = document.getElementById('motifGroup');
            
            statusSelect.addEventListener('change', function() {
                if (this.value === 'Pas sérieux') {
                    motifGroup.style.display = 'block';
                } else {
                    motifGroup.style.display = 'none';
                }
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Ajouter les données au formulaire
            const hiddenStatus = document.createElement('input');
            hiddenStatus.type = 'hidden';
            hiddenStatus.name = 'locataire_status';
            hiddenStatus.value = result.value.status;
            form.append(hiddenStatus);
            
            if (result.value.motif) {
                const hiddenMotif = document.createElement('input');
                hiddenMotif.type = 'hidden';
                hiddenMotif.name = 'locataire_motif';
                hiddenMotif.value = result.value.motif;
                form.append(hiddenMotif);
            }
            
            form.unbind('submit').submit();
        }
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
.preview-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
</style>
@endsection