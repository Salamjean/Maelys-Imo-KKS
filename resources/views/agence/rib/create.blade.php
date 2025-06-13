@extends('agence.layouts.template')

@section('content')
<div class="container-fluid rib-container">
    <div class="row">
        <!-- Formulaire RIB -->
        <div class="col-md-5">
            <div class="card form-card">
                <div class="card-header">
                    <h2 class="text-center">Enregistrer mon RIB</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('rib.store.agence') }}" method="POST" class="form-styled">
                        @csrf
                        <div class="form-group">
                            <label for="rib">Numéro RIB</label>
                            <input type="text" class="form-control" id="rib" name="rib" value="{{ old('rib') }}" required>
                            @error('rib')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="banque">Nom de la banque</label>
                            <input type="text" class="form-control" id="banque" value="{{ old('banque') }}" name="banque" required>
                            @error('banque')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tableau des RIBs -->
        <div class="col-md-7">
            <div class="card table-card">
                <div class="card-header">
                    <h3 class="text-center">RIBs Enregistrés</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr class="text-center">
                                    <th>Numéro RIB</th>
                                    <th>Nom de la Banque</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ribs as $rib)
                                    <tr>
                                        <td class="text-center">{{ substr($rib->rib, 0, 4) }}******{{ substr($rib->rib, -4) }}</td>
                                        <td class="text-center">{{ $rib->banque }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('rib.destroy', $rib->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger delete-btn" title="Supprimer">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rib-container {
        padding: 30px;
        background-color: #f5f7fa;
    }
    
    .form-card, .table-card {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: none;
        margin-bottom: 20px;
    }
    
    .form-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-top: 4px solid #02245b;
    }
    
    .table-card {
        background: white;
        border-top: 4px solid #02245b;
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 20px;
    }
    
    .card-header h2, .card-header h3 {
        color: #02245b;
        margin: 0;
        font-weight: 600;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        border-radius: 5px;
        border: 1px solid #ced4da;
        padding: 12px 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #02245b;
        box-shadow: 0 0 0 0.2rem rgba(2, 36, 91, 0.25);
    }
    
    label {
        font-weight: 500;
        color: #495057;
    }
    
    .btn-primary {
        background-color: #02245b;
        border: none;
        padding: 10px 25px;
        border-radius: 5px;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background-color: #021c4a;
        transform: translateY(-2px);
    }
    
    .btn-danger {
        padding: 5px 15px;
        border-radius: 4px;
        transition: all 0.3s;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
    }
    
    table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    thead th {
        background-color: #02245b;
        color: white;
        font-weight: 500;
        padding: 12px 15px;
    }
    
    tbody tr {
        transition: all 0.2s;
    }
    
    tbody tr:hover {
        background-color: rgba(2, 36, 91, 0.05);
    }
    
    tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
    }
    
    @media (max-width: 768px) {
        .col-md-5, .col-md-7 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .form-card {
            margin-bottom: 30px;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Succès!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: true,
        confirmButtonText: 'OK',
        confirmButtonColor: '#02245b'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Erreur!',
        text: '{{ session('error') }}',
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif

<script>
    // Confirmation de suppression
    $(document).ready(function() {
        $('.delete-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Confirmer la suppression',
                text: "Êtes-vous sûr de vouloir supprimer ce RIB ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer!',
                cancelButtonText: 'Annuler',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection