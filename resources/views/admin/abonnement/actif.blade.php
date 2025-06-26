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
    }

    .activate-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }

    .deactivate-btn {
    transition: all 0.3s ease;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.75rem;
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.deactivate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
    background-color: #e0a800;
    border-color: #d39e00;
}
.extend-btn {
    transition: all 0.3s ease;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.75rem;
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.extend-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    background-color: #0069d9;
    border-color: #0062cc;
}
</style>

<div class="col-lg-12 stretch-card mt-4">
    <div class="card subscription-card">
        <div class="card-header subscription-header">
            <h4 class="card-title subscription-title text-center text-white"><i class="fas fa-users me-2"></i>Abonnés Actifs</h4>
            <p class="card-description subscription-subtitle text-center mb-0 text-white">
                Liste des abonnements actifs (valides et non expirés)
            </p>
        </div>
        <div class="card-body">
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($abonnementsActifs as $abonnement)
                            @php
                                 // Calcul des jours restants (nombre entier)
                                    $joursRestants = now()->diffInDays($abonnement->date_fin, false);
                                    $joursRestants = $joursRestants > 0 ? $joursRestants : 0; // Évite les nombres négatifs
                                    
                                    // Classe CSS en fonction des jours restants
                                    $daysClass = 'success';
                                    if ($joursRestants <= 7 && $joursRestants > 3) {
                                        $daysClass = 'warning';
                                    } elseif ($joursRestants <= 3) {
                                        $daysClass = 'danger';
                                    }
                                
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
                                        @if($joursRestants > 0)
                                            {{ sprintf("%02d", $joursRestants) }} jours
                                        @else
                                            Expiré
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
                                    @if($abonnement->statut == 'actif')
                                        <span class="status-badge status-active"><i class="fas fa-check-circle me-1"></i> Actif</span>
                                    @elseif($abonnement->statut == 'expiré')
                                        <span class="status-badge status-expired"><i class="fas fa-times-circle me-1"></i> Expiré</span>
                                    @else
                                        <span class="status-badge status-pending"><i class="fas fa-clock me-1"></i> {{ ucfirst($abonnement->statut) }}</span>
                                    @endif
                                </td>
                               <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        @if($abonnement->statut == 'actif')
                                        <button class="btn btn-sm btn-warning deactivate-btn" 
                                                data-abonnement-id="{{ $abonnement->id }}"
                                                title="Désactiver cet abonnement">
                                            <i class="fas fa-ban"></i> Désactiver
                                        </button>
                                        <button class="btn btn-sm btn-primary extend-btn" 
                                                data-abonnement-id="{{ $abonnement->id }}"
                                                title="Prolonger cet abonnement">
                                            <i class="fas fa-calendar-plus"></i> Prolonger
                                        </button>
                                        @else
                                        <span class="text-muted">Non actif</span>
                                        @endif
                                    </div>
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

                @if($abonnementsActifs->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($abonnementsActifs->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnementsActifs->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($abonnementsActifs->getUrlRange(1, $abonnementsActifs->lastPage()) as $page => $url)
                                @if ($page == $abonnementsActifs->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($abonnementsActifs->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnementsActifs->nextPageUrl() }}" rel="next" aria-label="Next">
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
        $('.deactivate-btn').click(function() {
            const abonnementId = $(this).data('abonnement-id');
            const button = $(this);
            
            Swal.fire({
                title: 'Confirmer la désactivation',
                text: "Voulez-vous vraiment désactiver cet abonnement?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, désactiver',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("abonnements.deactivate") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: abonnementId
                        },
                        beforeSend: function() {
                            button.html('<i class="fas fa-spinner fa-spin"></i>');
                            button.prop('disabled', true);
                        },
                        success: function(response) {
                            Swal.fire(
                                'Désactivé!',
                                'L\'abonnement a été désactivé avec succès.',
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
                            button.html('<i class="fas fa-ban"></i> Désactiver');
                            button.prop('disabled', false);
                        }
                    });
                }
            });
        }); // Fin du gestionnaire pour deactivate-btn

        // Gestionnaire séparé pour extend-btn
      $('.extend-btn').click(function() {
            const abonnementId = $(this).data('abonnement-id');
            const button = $(this);
            
            Swal.fire({
                title: 'Modifier la durée',
                html: `
                    <div class="mb-3">
                        <label class="form-label">Nombre de mois :</label>
                        <input type="number" id="monthsInput" class="form-control" 
                            min="1" max="12" value="1" required>
                    </div>
                `,
                focusConfirm: false,
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-plus"></i> Prolonger',
                denyButtonText: '<i class="fas fa-minus"></i> Réduire',
                cancelButtonText: 'Annuler',
                preConfirm: () => {
                    const input = document.getElementById('monthsInput');
                    const months = parseInt(input.value);
                    
                    if (isNaN(months) || months < 1 || months > 12) {
                        Swal.showValidationMessage('Veuillez entrer un nombre entre 1 et 12');
                        return false;
                    }
                    return months;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Pour prolonger, on utilise la valeur retournée par preConfirm
                    modifyAbonnement(abonnementId, result.value, 'extend');
                } else if (result.isDenied) {
                    // Pour réduire, on doit récupérer la valeur depuis l'input
                    const months = parseInt(document.getElementById('monthsInput').value);
                    modifyAbonnement(abonnementId, months, 'reduce');
                }
            });
        });

    function modifyAbonnement(abonnementId, months, action) {
    const button = $(`.extend-btn[data-abonnement-id="${abonnementId}"]`);
    const actionText = action === 'extend' ? 'prolonger' : 'réduire';
    
    // Trouver la ligne correspondante dans le tableau
    const row = button.closest('tr');
    
    // Déterminer le type d'abonné (Propriétaire ou Agence)
    const userType = row.find('td:nth-child(2)').text().trim() === 'Propriétaire' ? 'Propriétaire' : 'Agence';
    
    // Définir le prix mensuel en fonction du type d'utilisateur
    const prixMensuel = userType === 'Propriétaire' ? 5000 : 10000;
    const montant = months * prixMensuel;
    
    const operation = action === 'extend' ? 'ajouter' : 'retirer';

    // Message différent selon l'action
    const confirmationMessage = action === 'extend' 
        ? `Vous êtes sur le point de ${actionText} cet abonnement de ${months} mois.<br>
           <strong>Montant à ${operation} : ${montant.toLocaleString()} FCFA</strong>
           <br><small>(Tarif ${userType}: ${prixMensuel.toLocaleString()} FCFA/mois)</small>`
        : `Vous êtes sur le point de ${actionText} cet abonnement de ${months} mois.<br>
           <strong>Montant à ${operation} : ${montant.toLocaleString()} FCFA</strong>
           <br><small>(Tarif ${userType}: ${prixMensuel.toLocaleString()} FCFA/mois)</small>`;

    Swal.fire({
        title: 'Confirmation',
        html: confirmationMessage,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Confirmer',
        cancelButtonText: 'Annuler',
        reverseButtons: true
    }).then((result) => {
        if (!result.isConfirmed) {
            button.html('<i class="fas fa-calendar-plus"></i> Prolonger');
            button.prop('disabled', false);
            return;
        }

        $.ajax({
            url: action === 'extend' ? '{{ route("abonnements.extend") }}' : '{{ route("abonnements.reduce") }}',
            type: 'POST',
            data: JSON.stringify({
                _token: '{{ csrf_token() }}',
                id: abonnementId,
                months: months,
                user_type: userType // Envoyer le type d'utilisateur au serveur
            }),
            contentType: 'application/json',
            beforeSend: function() {
                button.html('<i class="fas fa-spinner fa-spin"></i>');
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    const successMessage = action === 'extend'
                        ? `L'abonnement a été prolongé de ${months} mois.<br>
                           <strong>Nouveau montant : ${response.nouveau_montant.toLocaleString()} FCFA</strong><br>
                           Nouvelle date de fin: ${response.nouvelle_date_fin}`
                        : `L'abonnement a été réduit de ${months} mois.<br>
                           <strong>Nouveau montant : ${response.nouveau_montant.toLocaleString()} FCFA</strong><br>
                           Nouvelle date de fin: ${response.nouvelle_date_fin}`;

                    Swal.fire({
                        title: 'Succès!',
                        html: successMessage,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erreur!', response.message || 'Action non effectuée', 'error');
                    button.html('<i class="fas fa-calendar-plus"></i> Prolonger');
                    button.prop('disabled', false);
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Une erreur est survenue';
                Swal.fire('Erreur!', errorMsg, 'error');
                button.html('<i class="fas fa-calendar-plus"></i> Prolonger');
                button.prop('disabled', false);
            }
        });
    });
}
    }); // Fin du gestionnaire pour extend-btn
</script>
@endsection