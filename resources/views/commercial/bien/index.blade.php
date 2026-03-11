@extends('commercial.layouts.template')

@section('content')
<div class="content-wrapper p-4" style="background: #f8fafc !important;">
    <!-- Premium Styles -->
    <style>
        :root {
            --primary: #02245b;
            --accent: #ff5e14;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .premium-card {
            background: var(--surface);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .premium-table thead th {
            background: #f1f5f9;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border: none;
            padding: 15px;
        }

        .premium-table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-disponible { background: #ecfdf5; color: #059669; }
        .badge-loue { background: #fef2f2; color: #dc2626; }

        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            margin: 0 2px;
            border: none;
        }

        .btn-view { background: #eff6ff; color: #2563eb; }
        .btn-edit { background: #fff7ed; color: #ea580c; }
        .btn-delete { background: #fef2f2; color: #dc2626; }

        .btn-action:hover {
            transform: translateY(-2px);
            filter: brightness(0.95);
        }

        .bien-thumbnail {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold" style="color: var(--primary);">Mes Biens Immobilier</h2>
            <p class="text-muted">Gérez vos annonces et suivez leur statut en temps réel.</p>
        </div>
        <a href="{{ route('commercial.biens.choice') }}" class="btn btn-primary px-4 py-2" style="background: var(--primary); border: none; border-radius: 12px;">
            <i class="mdi mdi-plus-circle mr-2"></i> Nouveau Bien
        </a>
    </div>

    <!-- Filters -->
    <div class="filter-section">
        <form action="{{ route('commercial.biens.index') }}" method="GET" class="row align-items-end">
            <div class="col-md-3">
                <label class="small font-weight-bold text-muted">Filtrer par Agence</label>
                <select name="agence_id" class="form-control" style="border-radius: 10px;">
                    <option value="">Toutes les agences</option>
                    @foreach($agences as $agence)
                        <option value="{{ $agence->code_id }}" {{ request('agence_id') == $agence->code_id ? 'selected' : '' }}>
                            {{ $agence->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold text-muted">Filtrer par Propriétaire</label>
                <select name="proprietaire_id" class="form-control" style="border-radius: 10px;">
                    <option value="">Tous les propriétaires</option>
                    @foreach($proprietaires as $proprio)
                        <option value="{{ $proprio->code_id }}" {{ request('proprietaire_id') == $proprio->code_id ? 'selected' : '' }}>
                            {{ $proprio->name }} {{ $proprio->prenom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold text-muted">Type</label>
                <select name="type" class="form-control" style="border-radius: 10px;">
                    <option value="">Tous types</option>
                    <option value="Maison" {{ request('type') == 'Maison' ? 'selected' : '' }}>Maison</option>
                    <option value="Appartement" {{ request('type') == 'Appartement' ? 'selected' : '' }}>Appartement</option>
                    <option value="Bureau" {{ request('type') == 'Bureau' ? 'selected' : '' }}>Bureau</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary px-4" style="background: var(--primary); border-radius: 10px;">Filtrer</button>
                <a href="{{ route('commercial.biens.index') }}" class="btn btn-light px-4 ml-2" style="border-radius: 10px;">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="premium-card">
        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th>Bien</th>
                        <th>Détails</th>
                        <th>Cible</th>
                        <th>Localisation</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($biens as $bien)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/' . $bien->image) }}" class="bien-thumbnail mr-3" alt="thumb">
                                    <div>
                                        <div class="font-weight-bold">{{ $bien->code_bien }}</div>
                                        <small class="text-muted">{{ $bien->type }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="d-block"><strong>Surf:</strong> {{ $bien->superficie }} m²</small>
                                <small class="d-block"><strong>Chambres:</strong> {{ $bien->nombre_de_chambres ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($bien->agence_id)
                                    <span class="small text-primary"><i class="mdi mdi-domain mr-1"></i>{{ $bien->agence->name }}</span>
                                @else
                                    <span class="small text-success"><i class="mdi mdi-account-tie mr-1"></i>{{ $bien->proprietaire->name }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="small"><i class="mdi mdi-map-marker text-danger mr-1"></i>{{ $bien->commune }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--primary);">{{ number_format($bien->prix, 0, ',', ' ') }} FCFA</div>
                            </td>
                            <td>
                                <span class="status-badge {{ $bien->status == 'Disponible' ? 'badge-disponible' : 'badge-loue' }}">
                                    {{ $bien->status }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('commercial.biens.show', $bien->id) }}" class="btn-action btn-view" title="Afficher">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                <a href="{{ route('commercial.biens.edit', $bien->id) }}" class="btn-action btn-edit" title="Modifier">
                                    <i class="mdi mdi-pencil"></i>
                                </a>
                                <form action="{{ route('commercial.biens.destroy', $bien->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-action btn-delete btn-delete-confirm" title="Supprimer">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="mdi mdi-home-variant-outline" style="font-size: 50px; color: var(--primary);"></i>
                                <p class="mt-3 text-muted">Aucun bien ne correspond à vos critères.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 d-flex justify-content-center">
            {{ $biens->appends(request()->input())->links() }}
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.btn-delete-confirm').on('click', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#02245b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
