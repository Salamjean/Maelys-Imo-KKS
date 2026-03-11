@extends('admin.layouts.template')
@section('content')
    <style>
        .pagination {
            --bs-pagination-color: #02245b;
            --bs-pagination-bg: #fff;
            --bs-pagination-border-color: #dee2e6;
            --bs-pagination-hover-color: #fff;
            --bs-pagination-hover-bg: #02245b;
            --bs-pagination-active-bg: #02245b;
            --bs-pagination-active-border-color: #02245b;
        }

        .page-link {
            padding: 0.5rem 1rem;
            margin: 0 0.15rem;
            border-radius: 50%;
            min-width: 40px;
            text-align: center;
            transition: all 0.3s ease;
        }
    </style>
    <div class="col-lg-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center">Liste des commerciaux</h4>
                <p class="card-description text-center">
                    Gestion des commerciaux de la plateforme
                </p>

                <div class="mb-3 d-flex justify-content-between">
                    <input type="text" id="searchInput" class="form-control w-50" placeholder="Rechercher un commercial...">
                    <a href="{{ route('admin.commercial.create') }}" class="btn btn-primary">Ajouter un commercial</a>
                </div>
                <div class="table-responsive pt-3">
                    <table class="table table-bordered table-hover">
                        <thead style="background-color: #02245b; color: white;">
                            <tr class="text-center">
                                <th>Code ID</th>
                                <th>Nom & Prénom</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Localisation</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commercials as $commercial)
                                <tr class="text-center">
                                    <td><strong>{{ $commercial->code_id }}</strong></td>
                                    <td>{{ $commercial->name . ' ' . $commercial->prenom }}</td>
                                    <td>{{ $commercial->email }}</td>
                                    <td>{{ $commercial->contact }}</td>
                                    <td>{{ $commercial->commune }}</td>
                                    <td>
                                        <form action="{{ route('admin.commercial.toggle-status', $commercial->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $commercial->is_active ? 'btn-outline-success' : 'btn-outline-danger' }}" 
                                                title="{{ $commercial->is_active ? 'Désactiver le compte' : 'Activer le compte' }}">
                                                <i class="mdi {{ $commercial->is_active ? 'mdi-check-circle' : 'mdi-close-circle' }}"></i>
                                                {{ $commercial->is_active ? 'Actif' : 'Inactif' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group" style="gap: 10px">
                                            <a href="{{ route('admin.commercial.edit', $commercial->id) }}"
                                                class="btn btn-sm btn-warning" title="Modifier">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.commercial.destroy', $commercial->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                    title="Supprimer">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="alert alert-info">Aucun commercial inscrit.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $commercials->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            @if(session('success'))
                Swal.fire({
                    title: 'Succès !',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
            @endif

            $('.delete-btn').on('click', function (e) {
                e.preventDefault();
                const form = $(this).closest('form');
                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: "Êtes-vous sûr de vouloir supprimer ce commercial ?",
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

            $('#searchInput').on('keyup', function () {
                const searchText = $(this).val().toLowerCase();
                $('table tbody tr').each(function () {
                    const rowText = $(this).text().toLowerCase();
                    $(this).toggle(rowText.includes(searchText));
                });
            });
        });
    </script>
@endsection