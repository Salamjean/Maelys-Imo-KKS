@extends('agence.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('abonnement/adminStyle.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="col-lg-12 stretch-card mt-4">
    <div class="card subscription-card">
        <div class="card-header subscription-header">
            <h4 class="card-title subscription-title text-center text-white"><i class="fas mdi mdi-cash me-2"></i>Les loyers payés</h4>
            <p class="card-description subscription-subtitle text-center mb-0 text-white">
                Liste de toutes les paiements (Espèces, Mobile money et Virement Bancaire)
            </p>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un locataire...">
            </div>
            <div class="table-responsive">
                <table class="table subscription-table">
                    <thead>
                        <tr class="text-center">
                            <th>Locataire</th>
                            <th>Reference</th>
                            <th>Mois couvert</th>
                            <th>Montant</th>
                            <th>Date Paiement</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Preuve de virement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                   <tbody>
                         @forelse($paiements as $paiement)
                                <tr class="text-center">
                                    <td>
                                        {{ $paiement->bien->locataire ? $paiement->bien->locataire->name . ' ' . $paiement->bien->locataire->prenom : 'il n\'est plus locataire' }}
                                    </td>
                                    <td><strong>{{ $paiement->reference ?? 'pas de refrence'}} </strong></td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</td>
                                    <td class="text-center">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $paiement->methode_paiement }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $paiement->statut == 'payé' ? 'success' : ($paiement->statut == 'échoué' ? 'danger' : 'warning') }}">
                                            {{ $paiement->statut }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($paiement->proof_path)
                                            <a href="{{ asset('storage/' . $paiement->proof_path) }}" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="fas fa-file-download"></i> Télécharger
                                            </a>
                                        @else
                                            <span class="text-muted">Espèce ou <br> Mobile money</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($paiement->statut == 'payé')
                                            <a href="{{ route('locataire.paiements.receipt', $paiement->id) }}" 
                                            class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-receipt"></i> Reçu
                                            </a>
                                        @elseif($paiement->methode_paiement == 'Virement Bancaire' && $paiement->statut == 'En attente')
                                            <button class="btn btn-sm btn-success validate-btn" 
                                                    data-paiement-id="{{ $paiement->id }}">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                        @else
                                            <span class="text-muted">Non disponible</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Aucun paiement effectué.</td>
                                </tr>
                                @endforelse
                            </tbody>
                </table>

                @if($paiements->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($paiements->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $paiements->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($paiements->getUrlRange(1, $paiements->lastPage()) as $page => $url)
                                @if ($page == $paiements->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($paiements->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $paiements->nextPageUrl() }}" rel="next" aria-label="Next">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-right"></i></span>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('.validate-btn').click(function() {
            const paiementId = $(this).data('paiement-id');
            const button = $(this);
            
            Swal.fire({
                title: 'Voulez-vous vraiment confirmer le paiement ??',
                text: "Si oui, avez-vous verifier le document du virement ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, valider',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("paiements.validate") }}',  // Assurez-vous que cette route existe
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: paiementId
                        },
                        beforeSend: function() {
                            button.html('<i class="fas fa-spinner fa-spin"></i>');
                            button.prop('disabled', true);
                        },
                        success: function(response) {
                            Swal.fire(
                                'Validé!',
                                'Le paiement a été validé avec succès.',
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
                            button.html('<i class="fas fa-check"></i> Valider');
                            button.prop('disabled', false);
                        }
                    });
                }
            });
        });
    });
</script>
   <script>
$(document).ready(function() {
    // Système de recherche en temps réel
    $('#searchInput').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        
        $('.subscription-table tbody tr').each(function() {
            // Exclure la ligne "Aucun résultat" si elle existe
            if ($(this).find('td[colspan="10"]').length) {
                return true; // continue à l'itération suivante
            }
            
            const rowText = $(this).text().toLowerCase();
            if (rowText.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Gérer le cas où aucune correspondance n'est trouvée
        const visibleRows = $('.subscription-table tbody tr:visible').not('tr:has(td[colspan="10"])').length;
        if (visibleRows === 0) {
            // Supprimer d'abord les anciens messages "Aucun résultat"
            $('.subscription-table tbody tr td[colspan="10"]').closest('tr').remove();
            
            // Ajouter le nouveau message seulement si ce n'est pas déjà là
            if ($('.subscription-table tbody tr td[colspan="10"]').length === 0) {
                $('.subscription-table tbody').append(`
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h5>Aucun résultat trouvé</h5>
                                <p class="text-muted">Aucun abonnement ne correspond à votre recherche.</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        } else {
            // Supprimer le message "Aucun résultat" s'il existe
            $('.subscription-table tbody tr td[colspan="10"]').closest('tr').remove();
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
@endsection