@extends('proprietaire.layouts.template')
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
        <h4 class="card-title text-center">Gestion des visites</h4>
        <p class="card-description text-center">
          Liste des visites programmées
        </p>

        <div class="table-responsive pt-3">
          <table class="table table-bordered table-hover">
            <thead style="background-color: #02245b; color: white;">
                <tr class="text-center">
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Bien visité</th>
                    <th>Type</th>
                    <th>Date visite</th>
                    <th>Heure</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visites as $visite)
                    @php
                        $rowClass = '';
                        $statusClass = '';
                        
                        if ($visite->statut === 'confirmée') {
                            $statusClass = 'badge-primary';
                        } elseif ($visite->statut === 'en attente') {
                            $statusClass = 'badge-warning';
                        } elseif ($visite->statut === 'annulée') {
                            $statusClass = 'badge-danger';
                        } elseif ($visite->statut === 'effectuée') {
                            $statusClass = 'badge-success';
                            
                        }
                    @endphp
                    
                    <tr class="text-center">
                        <td>{{ $visite->nom }}</td>
                        <td>{{ $visite->telephone }}</td>
                        <td>{{ $visite->email }}</td>
                        <td>{{ $visite->bien->type }} à {{ $visite->bien->commune }}
                        <td>{{ $visite->bien->type }}</td>
                        <td>{{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}</td>
                        <td>{{ $visite->heure_visite }}</td>
                        <td>{{ Str::limit($visite->message, 30) }}</td>
                        <td>
                            <span class="badge {{ $statusClass }}">{{ $visite->statut }}</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary confirm-visite-btn" 
                                        data-visite-id="{{ $visite->id }}"
                                        title="Confirmer">
                                    <i class="mdi mdi-check"></i>
                                </button>
                                <button class="btn btn-sm btn-success done-visite-btn" 
                                        data-visite-id="{{ $visite->id }}"
                                        title="Marquer comme effectuée">
                                    <i class="mdi mdi-checkbox-multiple-marked-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-danger cancel-visite-btn" 
                                        data-visite-id="{{ $visite->id }}"
                                        title="Annuler">
                                    <i class="mdi mdi-close"></i>
                                </button>
                                <button class="btn btn-sm btn-info view-visite-btn" 
                                        data-visite-id="{{ $visite->id }}"
                                        title="Détails">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="alert alert-info">
                                Aucune visite programmée pour le moment.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
          </table>
          
          @if($visites->hasPages())
          <div class="mt-4 d-flex justify-content-center">
              <nav aria-label="Page navigation">
                  <ul class="pagination pagination-rounded">
                      {{-- Previous Page Link --}}
                      @if ($visites->onFirstPage())
                          <li class="page-item disabled">
                              <span class="page-link" aria-hidden="true">&laquo;</span>
                          </li>
                      @else
                          <li class="page-item">
                              <a class="page-link" href="{{ $visites->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                          </li>
                      @endif

                      {{-- Pagination Elements --}}
                      @foreach ($visites->getUrlRange(1, $visites->lastPage()) as $page => $url)
                          @if ($page == $visites->currentPage())
                              <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                          @else
                              <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                          @endif
                      @endforeach

                      {{-- Next Page Link --}}
                      @if ($visites->hasMorePages())
                          <li class="page-item">
                              <a class="page-link" href="{{ $visites->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
    <script>
$(document).ready(function() {
    // Notification SweetAlert2
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

    // Confirmation de suppression
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

    // Gestion des visites
    const confirmUrlTemplate = "{{ route('visites.confirm.owner', ['visite' => ':id']) }}";
    const doneUrlTemplate = "{{ route('visites.done.owner', ['visite' => ':id']) }}";
    const cancelUrlTemplate = "{{ route('visites.cancel.owner', ['visite' => ':id']) }}";
    const viewUrlTemplate = "{{ route('visites.show.owner', ['visite' => ':id']) }}";
    const updateDateUrlTemplate = "{{ route('visites.updateDate.owner', ['visite' => ':id']) }}";

    function generateUrl(template, id) {
        return template.replace(':id', id);
    }

    // Confirmation de visite
    $(document).on('click', '.confirm-visite-btn', function() {
        const visiteId = $(this).data('visite-id');
        const row = $(this).closest('tr');
        const currentDate = row.find('td:nth-child(6)').text();
        const currentTime = row.find('td:nth-child(7)').text();
        
        Swal.fire({
            title: 'Confirmer la visite',
            html: `Voulez-vous confirmer cette visite pour le <br> <strong>${currentDate}</strong> à <strong>${currentTime}</strong> ?`,
            icon: 'question',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Oui, confirmer',
            denyButtonText: 'Changer date/heure',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#3085d6',
            denyButtonColor: '#02245b',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: generateUrl(confirmUrlTemplate, visiteId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Confirmée!',
                            'La visite a été confirmée.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Erreur!',
                            xhr.responseJSON.message || 'Une erreur est survenue',
                            'error'
                        );
                    }
                });
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Modifier date et heure',
                    html: `
                        <div class="form-group">
                            <label for="newDate">Nouvelle date</label>
                            <input type="date" id="newDate" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="newTime">Nouvelle heure</label>
                            <input type="time" id="newTime" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="motif">Motif du changement</label>
                            <textarea id="motif" class="form-control" rows="3" required></textarea>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Confirmer',
                    cancelButtonText: 'Annuler',
                    focusConfirm: false,
                    preConfirm: () => {
                        return {
                            newDate: $('#newDate').val(),
                            newTime: $('#newTime').val(),
                            motif: $('#motif').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { newDate, newTime, motif } = result.value;
                        
                        if (!newDate || !newTime || !motif) {
                            Swal.showValidationMessage('Tous les champs sont obligatoires');
                            return;
                        }

                        $.ajax({
                            url: generateUrl(updateDateUrlTemplate, visiteId),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                date_visite: newDate,
                                heure_visite: newTime,
                                motif: motif
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Modifié!',
                                    'La visite a été replanifiée.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Erreur!',
                                    xhr.responseJSON.message || 'Erreur lors de la modification',
                                    'error'
                                );
                            }
                        });
                    }
                });
            }
        });
    });

    // Marquer comme effectuée
    $(document).on('click', '.done-visite-btn', function() {
        const visiteId = $(this).data('visite-id');
        
        Swal.fire({
            title: 'Confirmer',
            text: "Marquer cette visite comme effectuée ?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui',
            cancelButtonText: 'Non'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: generateUrl(doneUrlTemplate, visiteId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Succès!',
                            'Visite marquée comme effectuée.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Erreur!',
                            xhr.responseJSON.message || 'Erreur',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Annulation de visite
    $(document).on('click', '.cancel-visite-btn', function() {
        const visiteId = $(this).data('visite-id');
        
        Swal.fire({
            title: 'Annuler la visite',
            html: `
                <div class="form-group">
                    <label for="cancelMotif">Motif d'annulation</label>
                    <textarea id="cancelMotif" class="form-control" rows="3" required></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Confirmer',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const motif = $('#cancelMotif').val();
                if (!motif) {
                    Swal.showValidationMessage('Le motif est obligatoire');
                    return false;
                }
                return { motif };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const { motif } = result.value;
                
                $.ajax({
                    url: generateUrl(cancelUrlTemplate, visiteId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        motif: motif
                    },
                    success: function(response) {
                        Swal.fire(
                            'Annulée!',
                            'La visite a été annulée.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Erreur!',
                            xhr.responseJSON.message || 'Erreur lors de l\'annulation',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Voir les détails
    $(document).on('click', '.view-visite-btn', function() {
        const visiteId = $(this).data('visite-id');
        
        $.ajax({
            url: generateUrl(viewUrlTemplate, visiteId),
            method: 'GET',
            success: function(response) {
                Swal.fire({
                    title: 'Détails de la visite',
                    html: `
                        <div class="text-start">
                            <p><strong>Client:</strong> ${response.nom}</p>
                            <p><strong>Email:</strong> ${response.email}</p>
                            <p><strong>Téléphone:</strong> ${response.telephone}</p>
                            <hr>
                            <p><strong>Bien:</strong> ${response.bien.type} à ${response.bien.commune}</p>
                            <p><strong>Prix:</strong> ${new Intl.NumberFormat('fr-FR').format(response.bien.prix)} FCFA</p>
                            <hr>
                            <p><strong>Date:</strong> ${new Date(response.date_visite).toLocaleDateString('fr-FR')}</p>
                            <p><strong>Heure:</strong> ${response.heure_visite}</p>
                            <p><strong>Statut:</strong> <span class="badge ${response.statut === 'confirmée' ? 'badge-primary' : response.statut === 'annulée' ? 'badge-danger' : 'badge-warning'}">${response.statut}</span></p>
                            <hr>
                            <p><strong>Message:</strong> ${response.message || 'Aucun message'}</p>
                            ${response.motif ? `<p><strong>Motif:</strong> ${response.motif}</p>` : ''}
                        </div>
                    `,
                    confirmButtonText: 'Fermer',
                    width: '600px'
                });
            },
            error: function() {
                Swal.fire(
                    'Erreur!',
                    'Impossible de charger les détails',
                    'error'
                );
            }
        });
    });
});
</script>
    

<style>
.preview-image:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
</style>
@endsection