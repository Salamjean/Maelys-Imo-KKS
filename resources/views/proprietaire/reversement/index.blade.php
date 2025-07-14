@extends('proprietaire.layouts.template')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #02245b;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Historique des Reversements</h4>
                        <a href="{{ route('reversement.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus mr-2"></i>Nouveau reversement
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                     <div class="mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un abonné...">
                    </div>
                    @if($reversements->isEmpty())
                        <div class="alert alert-info">
                            Aucun reversement effectué pour le moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr class="text-center">
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Référence</th>
                                        <th>Banque</th>
                                        <th>RIB</th>
                                        <th>Statut</th>
                                        <th>Réçu</th>
                                        <th>Montant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reversements as $reversement)
                                    <tr class="text-center">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                         @endif
                       <!-- Pagination -->
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Afficher le RIB quand une banque est sélectionnée
document.getElementById('banque').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('rib').value = selectedOption.getAttribute('data-rib');
    updateMaxMontant();
});

// Mettre à jour le max du champ montant
function updateMaxMontant() {
    fetch("{{ route('reversement.solde') }}")
        .then(response => response.json())
        .then(data => {
            const soldeElement = document.getElementById('solde-disponible');
            const montantInput = document.getElementById('montant');
            
            montantInput.max = data.solde;
            soldeElement.textContent = new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2}).format(data.solde);
            
            // Animation du changement de solde
            soldeElement.classList.add('text-success');
            setTimeout(() => soldeElement.classList.remove('text-success'), 1000);
        });
}

// Actualisation périodique du solde
setInterval(updateMaxMontant, 30000);

// Validation Bootstrap
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Afficher le RIB quand une banque est sélectionnée
document.getElementById('banque').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('rib').value = selectedOption.getAttribute('data-rib');
    updateMaxMontant();
});

// Mettre à jour le max du champ montant
function updateMaxMontant() {
    fetch("{{ route('reversement.solde') }}")
        .then(response => response.json())
        .then(data => {
            const soldeElement = document.getElementById('solde-disponible');
            const montantInput = document.getElementById('montant');
            
            montantInput.max = data.solde;
            soldeElement.textContent = new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2}).format(data.solde);
            
            // Animation du changement de solde
            soldeElement.classList.add('text-success');
            setTimeout(() => soldeElement.classList.remove('text-success'), 1000);
        });
}

// Actualisation périodique du solde
setInterval(updateMaxMontant, 30000);

// Gestion des alertes SweetAlert
@if(session('success'))
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Opération réussie',
        html: `{!! str_replace("'", "\\'", session('success')) !!}<br><br>
               <strong>Nouveau solde disponible:</strong> {{ number_format(session('solde'), 2) }} FCFA`,
        confirmButtonText: 'Fermer',
        confirmButtonColor: '#3b82f6',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
});
@endif

// Gestion du formulaire
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reversement-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation Bootstrap
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            Swal.fire({
                title: 'Confirmer le reversement',
                text: "Voulez-vous vraiment effectuer ce reversement?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, confirmer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    }
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