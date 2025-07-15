@extends('admin.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .subscription-card {
        border-radius: 15px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
    }
    
    .subscription-header {
        background: linear-gradient(135deg, #02245b 0%, #3a7bd5 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .subscription-title {
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .subscription-subtitle {
        opacity: 0.9;
        font-weight: 300;
    }
    
    .subscription-table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    
    .subscription-table thead th {
        background-color: #02245b;
        color: white;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px;
    }
    
    .subscription-table tbody tr {
        background-color: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .subscription-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .subscription-table td {
        padding: 15px;
        vertical-align: middle;
        border-top: none;
        border-bottom: 1px solid #f1f1f1;
    }
    
    .subscription-table td:first-child {
        border-left: 4px solid #3a7bd5;
        border-radius: 10px 0 0 10px;
    }
    
    .subscription-table td:last-child {
        border-radius: 0 10px 10px 0;
    }
    
    .days-remaining {
        font-weight: 700;
    }
    
    .days-remaining.danger {
        color: #dc3545;
    }
    
    .days-remaining.warning {
        color: #ffc107;
    }
    
    .days-remaining.success {
        color: #28a745;
    }
    
    .status-badge {
        padding: 8px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-expired {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .empty-state {
        padding: 3rem;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 10px;
    }
    
    .empty-icon {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
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
        color: #02245b;
        border: 1px solid #dee2e6;
        margin: 0 3px;
    }
    
    .page-item.active .page-link {
        background-color: #02245b;
        border-color: #02245b;
    }
    
    .payment-method-icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 8px;
        background-color: #f1f5fd;
        color: #3a7bd5;
    }
    .activate-btn {
    transition: all 0.3s ease;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.75rem;
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.activate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    background-color: #218838;
    border-color: #1e7e34;
}
    .activate-btn1 {
    transition: all 0.3s ease;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.75rem;
    background-color: #02245b;
    border-color: #02245b;
    color: white;
}

.activate-btn1:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    background-color: #02388e;
    border-color: #02388e;
    color: white;
}

.swal2-popup .form-control {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
}

.swal2-popup .form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}
</style>

<div class="col-lg-12 stretch-card mt-4">
    <div class="card subscription-card">
        <div class="card-header subscription-header">
            <h4 class="card-title subscription-title text-center text-white"><i class="fas fa-users me-2"></i>Abonnés Inactifs</h4>
            <p class="card-description subscription-subtitle text-center mb-0 text-white">
                Liste des abonnements actifs (valides et non expirés)
            </p>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un abonné...">
            </div>
            <div class="table-responsive">
                <table class="table subscription-table">
                    <thead>
                        <tr class="text-center">
                            <th>Abonné</th>
                            <th>Type</th>
                            <th>Date Début</th>
                            <th>Date Fin</th>
                            <th>Jours Restants</th>
                            <th>Montant</th>
                            <th>Mois Abonné</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($abonnementsInactifs as $abonnement)
                            @php
                                // Calcul des jours depuis l'expiration (nombre entier positif)
                                $joursDepuisExpiration = max(0, now()->diffInDays($abonnement->date_fin));
                                
                                // Classe CSS en fonction du temps depuis l'expiration
                                $daysClass = match(true) {
                                    $joursDepuisExpiration <= 7 => 'warning',  // Expiré récemment (1-7 jours)
                                    $joursDepuisExpiration > 7 => 'danger',    // Expiré depuis longtemps (>7 jours)
                                    default => 'secondary'
                                };
                                
                                // Déterminer le nom de l'abonné
                                $abonneName = 'N/A';
                                if ($abonnement->proprietaire) {
                                    $abonneName = $abonnement->proprietaire->name ?? 'Propriétaire';
                                } elseif ($abonnement->agence) {
                                    $abonneName = $abonnement->agence->name ?? 'Agence';
                                }
                            @endphp
                            
                            <tr class="text-center">
                                <td>{{ $abonneName }}</td>
                                <td>
                                    @if($abonnement->proprietaire_id)
                                        <span class="badge text-white" style="background-color: #062a64">Propriétaire</span>
                                    @elseif($abonnement->agence_id)
                                        <span class="badge text-white" style="background-color: #ff5e14">Agence</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($abonnement->date_debut)->translatedFormat('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($abonnement->date_fin)->translatedFormat('d/m/Y') }}</td>
                                 <td>
                                    <span class="days-remaining {{ $daysClass }}">
                                        @if($abonnement->date_fin >= now())
                                            {{ sprintf("%02d", now()->diffInDays($abonnement->date_fin)) }} jours
                                        @else
                                            Expiré ({{ $joursDepuisExpiration }}j)
                                        @endif
                                    </span>
                                </td>
                                <td>{{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $abonnement->mois_abonne }}</td>
                                <td>
                                    @if($abonnement->mode_paiement === 'Espèces')
                                        <span class="payment-method-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    @elseif($abonnement->mode_paiement === 'Mobile Money')
                                        <span class="payment-method-icon"><i class="fas fa-mobile-alt"></i></span>
                                    @else
                                        <span class="payment-method-icon"><i class="fas fa-credit-card"></i></span>
                                    @endif
                                    {{ $abonnement->mode_paiement }}
                                </td>
                                <td>
                                    @if($abonnement->statut === 'inactif' || $abonnement->date_fin < now())
                                        <span class="status-badge status-expired">
                                            <i class="fas fa-times-circle me-1"></i> 
                                            {{ $abonnement->date_fin < now() ? 'Expiré' : 'Inactif' }}
                                        </span>
                                    @else
                                        <span class="status-badge status-pending">
                                            <i class="fas fa-clock me-1"></i> 
                                            {{ ucfirst($abonnement->statut) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($abonnement->statut == 'inactif')
                                        <!-- Bouton Activer (sans durée) -->
                                        <button class="btn btn-sm btn-success activate-btn me-1" 
                                                data-abonnement-id="{{ $abonnement->id }}"
                                                title="Activer sans modifier la durée">
                                            <i class="fas fa-power-off"></i> Activer
                                        </button>
                                    @else
                                        <span class="text-muted">Déjà actif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-calendar-times"></i>
                                        </div>
                                        <h5>Aucun abonnement actif trouvé</h5>
                                        <p class="text-muted">Il n'y a actuellement aucun abonnement valide.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($abonnementsInactifs->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($abonnementsInactifs->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnementsInactifs->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($abonnementsInactifs->getUrlRange(1, $abonnementsInactifs->lastPage()) as $page => $url)
                                @if ($page == $abonnementsInactifs->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($abonnementsInactifs->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnementsInactifs->nextPageUrl() }}" rel="next" aria-label="Next">
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
document.addEventListener('DOMContentLoaded', function() {
    // 1. Bouton "Activer" (sans durée)
    document.querySelectorAll('.activate-btn').forEach(button => {
        button.addEventListener('click', function() {
            const abonnementId = this.getAttribute('data-abonnement-id');
            
            Swal.fire({
                title: 'Confirmer l\'activation',
                text: 'Voulez-vous activer cet abonnement sans modifier sa durée ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Activer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    sendActivationRequest(abonnementId);
                }
            });
        });
    });

    // 2. Bouton "Prolonger" (avec durée)
    document.querySelectorAll('.extend-btn').forEach(button => {
        button.addEventListener('click', function() {
            const abonnementId = this.getAttribute('data-abonnement-id');
            
            Swal.fire({
                title: 'Prolonger l\'abonnement',
                html: `
                    <div class="mb-3">
                        <label for="monthsInput" class="form-label">Nombre de mois :</label>
                        <input type="number" id="monthsInput" class="form-control" 
                               min="1" max="12" value="1" required>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Prolonger',
                cancelButtonText: 'Annuler',
                preConfirm: () => {
                    const months = Swal.getPopup().querySelector('#monthsInput').value;
                    if (!months || months < 1 || months > 12) {
                        Swal.showValidationMessage('Veuillez entrer un nombre entre 1 et 12');
                        return false;
                    }
                    return { months: parseInt(months) };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const months = result.value.months;
                    sendActivationRequest(abonnementId, months);
                }
            });
        });
    });

    // Fonction commune d'envoi de requête
    function sendActivationRequest(abonnementId, months = null) {
        const button = document.querySelector(`[data-abonnement-id="${abonnementId}"]`);
        const originalHTML = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        const requestData = { id: abonnementId };
        if (months) requestData.months = months;
        
        fetch('{{ route("abonnements.activate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            Swal.fire(
                'Succès!',
                months ? `L'abonnement a été prolongé de ${months} mois.` : 'L\'abonnement a été activé.',
                'success'
            ).then(() => {
                location.reload();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            button.innerHTML = originalHTML;
            button.disabled = false;
            Swal.fire(
                'Erreur!',
                'Une erreur est survenue',
                'error'
            );
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