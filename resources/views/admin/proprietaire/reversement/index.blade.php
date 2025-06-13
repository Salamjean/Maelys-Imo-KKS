@extends('admin.layouts.template')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #02245b;">
                    <h4 class="mb-0">Liste reversements demandés</h4>
                </div>
                
                <div class="card-body">
                    @if($reversements->isEmpty())
                        <div class="alert alert-info text-center">
                            Aucun reversement demandé pour le moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th>demandeur</th>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Référence</th>
                                        <th>Banque</th>
                                        <th>RIB</th>
                                        <th>Statut</th>
                                        <th>Réçu</th>
                                        <th>Montant</th>
                                        <th colspan="2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reversements as $reversement)
                                    <tr class="text-center">
                                        <td>{{ $reversement->proprietaire->name.' '.$reversement->proprietaire->prenom }}</td>
                                        <td>{{ $reversement->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $reversement->created_at->format('H:i') }}</td>
                                        <td><span class="badge text-white" style="background-color: #02245b">{{ $reversement->reference }}</span></td>
                                        <td>{{ $reversement->rib->banque }}</td>
                                        <td>{{ substr($reversement->rib->rib, 0, 4) }}******{{ substr($reversement->rib->rib, -4) }}</td>
                                        <td>
                                            @if($reversement->statut == 'En attente')
                                                <span class="badge badge-warning">En attente</span>
                                            @elseif($reversement->statut == 'Effectué')
                                                <span class="badge badge-success">Effectué</span>
                                            @else
                                                <span class="badge badge-danger">Échoué</span>
                                            @endif
                                        </td>
                                        <td>
                                        @if($reversement->recu_paiement)
                                            @php
                                                $contratPath = asset('storage/' . $reversement->recu_paiement);
                                                $contratPathPdf = strtolower(pathinfo($contratPath, PATHINFO_EXTENSION)) === 'pdf';
                                            @endphp
                                            @if ($contratPathPdf)
                                                <a href="{{ $contratPath }}" target="_blank">
                                                    <img src="{{ asset('assets/images/pdf.jpg') }}" alt="PDF" width="30" height="30">
                                                </a>
                                            @else
                                                <img src="{{ $contratPath }}" 
                                                    alt="Pièce du parent" 
                                                    width="50" 
                                                    height=50
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#imageModal" 
                                                    onclick="showImage(this)" 
                                                    onerror="this.onerror=null; this.src='{{ asset('assets/images/profiles/bébé.jpg') }}'">
                                            @endif
                                        @else
                                            <p>Paiement non effectué</p>
                                        @endif
                                    </td>
                                        <td class=" font-weight-bold">{{ number_format($reversement->montant) }} FCFA</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary " data-toggle="modal" data-target="#detailsModal{{ $reversement->id }}">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#uploadModal{{ $reversement->id }}">
                                                <i class="mdi mdi-cash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal pour les détails -->
                                    <div class="modal fade" id="detailsModal{{ $reversement->id }}" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header text-white" style="background-color: #02245b;">
                                                    <h5 class="modal-title" id="detailsModalLabel">Détails du reversement</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Référence:</strong><br>
                                                            <span class="badge text-white" style="background-color: #02245b">{{ $reversement->reference }}</span>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Date:</strong><br>
                                                            {{ $reversement->created_at->format('d/m/Y H:i') }}
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Banque:</strong><br>
                                                            {{ $reversement->rib->banque }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>RIB:</strong><br>
                                                            {{ substr($reversement->rib->rib, 0, 4) }}******{{ substr($reversement->rib->rib, -4) }}
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-primary">
                                                        <h5 class="text-center mb-0">
                                                            Montant: {{ number_format($reversement->montant) }} FCFA
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ajoutez ce modal après le modal de détails -->
                                    <div class="modal fade" id="uploadModal{{ $reversement->id }}" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title" id="uploadModalLabel">Ajouter un reçu de paiement</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('reversement.upload-recu', $reversement->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="recu_paiement">Sélectionner un fichier PDF</label>
                                                            <input type="file" class="form-control-file" id="recu_paiement" name="recu_paiement" accept=".pdf" required>
                                                            <small class="form-text text-muted">Seuls les fichiers PDF sont acceptés (max: 2MB)</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-success">Enregistrer</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($reversements->hasPages())
                        <div class="mt-4 d-flex justify-content-center">
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-rounded">
                                    {{-- Previous Page Link --}}
                                    @if ($reversements->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link" aria-hidden="true">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $reversements->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($reversements->getUrlRange(1, $reversements->lastPage()) as $page => $url)
                                        @if ($page == $reversements->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($reversements->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $reversements->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
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
</div>

<style>
    .table th {
        border-top: none;
    }
    .badge {
        font-size: 0.9em;
        padding: 5px 10px;
    }
    .card {
        border-radius: 10px;
    }
</style>
@endsection