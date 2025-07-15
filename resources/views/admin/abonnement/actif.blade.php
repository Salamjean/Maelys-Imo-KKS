@extends('admin.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('abonnement/adminStyle.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="col-lg-12 stretch-card mt-4">
    <div class="card subscription-card">
        <div class="card-header subscription-header">
            <h4 class="card-title subscription-title text-center text-white"><i class="fas fa-users me-2"></i>Abonnés Actifs</h4>
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
                            <th>Montant d'abonnement</th>
                            <th>Montant total d'abonnement</th>
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
                                    $abonneName = $abonnement->proprietaire->name.' '.$abonnement->proprietaire->prenom ?? 'Propriétaire';
                                } elseif ($abonnement->agence) {
                                    $abonneName = $abonnement->agence->name ?? 'Agence';
                                }
                            @endphp
                            
                            <tr class="text-center">
                                <td><strong>{{ $abonneName }}</strong></td>
                                <td><strong>{{$abonnement->type}}</strong></td>
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
                                <td>{{ number_format($abonnement->montant_actuel, 0, ',', ' ') }} FCFA</td>
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
                                        <a href="{{ route('abonnements.pdf', $abonnement->id) }}" 
                                            class="btn btn-sm btn-danger pdf-btn"
                                            title="Générer PDF">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        @else
                                        <span class="text-muted">Non actif</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11">
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