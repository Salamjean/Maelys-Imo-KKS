@extends($layout ?? 'admin.layouts.template')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header text-white d-flex align-items-center justify-content-between"
                        style="background-color: #02245b;">
                        <div>
                            <h4 class="mb-0"><i class="mdi mdi-history mr-2"></i>Historique des locations</h4>
                            <small>{{ $locataire->name }} {{ $locataire->prenom }} — {{ $locataire->contact }}</small>
                        </div>
                        <a href="{{ url()->previous() }}" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i>
                            Retour</a>
                    </div>

                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        @if ($historiques->isEmpty())
                            <div class="alert alert-info text-center">
                                <i class="mdi mdi-information-outline mr-1"></i> Aucun historique de location pour ce
                                locataire.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="bg-light">
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>Bien</th>
                                            <th>Agence / Propriétaire</th>
                                            <th>Date entrée</th>
                                            <th>Date sortie</th>
                                            <th>Durée</th>
                                            <th>Motif sortie</th>
                                            <th>État entrée</th>
                                            <th>État sortie</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($historiques as $h)
                                            <tr class="text-center">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $h->bien->type ?? 'N/A' }}</strong><br>
                                                    <small class="text-muted">{{ $h->bien->commune ?? '' }}</small>
                                                </td>
                                                <td>
                                                    @if ($h->agence)
                                                        <span class="badge badge-primary">🏢 {{ $h->agence->name }}</span>
                                                    @elseif($h->proprietaire)
                                                        <span class="badge badge-secondary">👤 {{ $h->proprietaire->name }}
                                                            {{ $h->proprietaire->prenom }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $h->date_entree->format('d/m/Y') }}</td>
                                                <td>{{ $h->date_sortie ? $h->date_sortie->format('d/m/Y') : '—' }}</td>
                                                <td>
                                                    @if ($h->date_sortie)
                                                        {{ $h->date_entree->diffInMonths($h->date_sortie) }} mois
                                                    @else
                                                        <span class="text-success">En cours</span>
                                                    @endif
                                                </td>
                                                <td>{{ $h->motif_sortie ?? '—' }}</td>
                                                <td>
                                                    @if ($h->etatLieuEntree)
                                                        <a href="{{ route('etat.download', $h->etatLieuEntree->id) }}"
                                                            class="btn btn-xs btn-outline-primary" title="Télécharger">
                                                            <i class="mdi mdi-file-pdf-box"></i> PDF
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($h->etatLieuSortie)
                                                        <a href="{{ route($routePrefix . 'demenagement.download.sortie', $h->id) }}"
                                                            class="btn btn-xs btn-outline-danger" title="Télécharger">
                                                            <i class="mdi mdi-file-pdf-box"></i> PDF
                                                        </a>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (is_null($h->date_sortie))
                                                        <span class="badge badge-success">En cours</span>
                                                        <a href="{{ route($routePrefix . 'demenagement.show', $h->locataire_id) }}"
                                                            class="btn btn-xs btn-danger ml-1" title="Déménager">
                                                            <i class="mdi mdi-home-export-outline"></i> Déménager
                                                        </a>
                                                    @else
                                                        <span class="badge badge-secondary">Terminé</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
