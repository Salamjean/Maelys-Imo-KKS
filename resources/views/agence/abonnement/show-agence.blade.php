@extends('agence.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="{{ asset('abonnement/adminStyle.css') }}">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.cinetpay.com/seamless/main.js"></script>
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
                            <th>Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse($abonnements as $abonnement)
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
                                <td>{{ number_format($abonnement->montant_actuel, 0, ',', ' ') }} FCFA</td>
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
                                        <a href="{{ route('abonnements.pdf', $abonnement->id) }}" 
                                            class="btn btn-sm btn-danger pdf-btn"
                                            title="Générer PDF">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                       <button 
                                            class="btn btn-sm btn-warning cinetpay-btn"
                                            data-abonnement-id="{{ $abonnement->id }}"
                                            data-user-type="{{ $abonnement->agence_id ? 'agence' : 'proprietaire' }}"
                                            title="Renouveler via CinetPay">
                                            <i class="fas fa-mobile-alt"></i> Renouveler
                                        </button>
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

                @if($abonnements->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-rounded">
                            @if ($abonnements->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnements->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            @endif

                            @foreach ($abonnements->getUrlRange(1, $abonnements->lastPage()) as $page => $url)
                                @if ($page == $abonnements->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach

                            @if ($abonnements->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $abonnements->nextPageUrl() }}" rel="next" aria-label="Next">
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
    // Gestionnaire pour le bouton d'abonnement
    $('.renew-btn').click(function(e) {
        e.preventDefault();
        const abonnementId = $(this).data('abonnement-id');
        console.log('Bouton cliqué pour l\'abonnement ID:', abonnementId); // Debug

        Swal.fire({
            title: 'Renouveler l\'abonnement',
            html: `
                <div class="mb-3">
                    <label class="form-label">Type d'abonnement :</label>
                    <select id="abonnementType" class="form-select">
                        <option value="standard">Standard (5 000 FCFA/mois)</option>
                        <option value="premium">Premium (7 000 FCFA/mois)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Durée :</label>
                    <select id="abonnementDuree" class="form-select">
                        <option value="1">1 mois (pas de réduction)</option>
                        <option value="3">3 mois (20% de réduction)</option>
                        <option value="6">6 mois (20% de réduction)</option>
                        <option value="12">12 mois (17% de réduction)</option>
                    </select>
                </div>
                <div class="alert alert-info mt-3">
                    <small>
                        <strong>Tarif de base :</strong> 
                        <span id="basePrice">5 000 FCFA/mois (Standard)</span>
                        <br>
                        <strong>Montant total :</strong> 
                        <span id="totalPrice">5 000 FCFA (1 mois)</span>
                        <br>
                        <strong>Montant final :</strong> 
                        <span id="finalPrice">5 000 FCFA</span>
                    </small>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirmer le renouvellement',
            cancelButtonText: 'Annuler',
            width: '600px',
            didOpen: () => {
                updatePriceCalculation();
                $('#abonnementType, #abonnementDuree').change(updatePriceCalculation);
            },
            preConfirm: () => {
                return {
                    type: $('#abonnementType').val(),
                    duree: $('#abonnementDuree').val(),
                    montant: parseFloat($('#finalPrice').text().replace(/\D/g, ''))
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Données à envoyer:', result.value); // Debug
                renewAbonnement(abonnementId, result.value);
            }
        });
    });

    // Fonction pour mettre à jour le calcul des prix
    function updatePriceCalculation() {
        const type = $('#abonnementType').val();
        const duree = parseInt($('#abonnementDuree').val());
        
        // Prix de base selon le type
        const prixBase = type === 'standard' ? 100 : 100;
        const prixTotal = prixBase * duree;
        
        // Calcul de la réduction
        let reduction = 0;
        let reductionText = "Pas de réduction";
        
        if (duree === 3 || duree === 6) {
            reduction = 0.20; // 20% de réduction
            reductionText = "20% de réduction";
        } else if (duree === 12) {
            reduction = 0.17; // 17% de réduction
            reductionText = "17% de réduction";
        }
        
        const montantFinal = duree === 1 ? prixTotal : prixTotal * (1 - reduction);
        
        // Mise à jour de l'affichage
        $('#basePrice').text(`${prixBase.toLocaleString()} FCFA/mois (${type === 'standard' ? 'Standard' : 'Premium'})`);
        $('#totalPrice').text(`${prixTotal.toLocaleString()} FCFA (${duree} mois)`);
        $('#finalPrice').text(`${montantFinal.toLocaleString()} FCFA ${duree === 1 ? '' : 'avec ' + reductionText}`);
    }

    // Fonction pour renouveler l'abonnement via AJAX
    function renewAbonnement(abonnementId, data) {
        console.log('Envoi des données:', {abonnementId, data}); // Debug
        
        Swal.fire({
            title: 'Traitement en cours',
            html: 'Veuillez patienter...',
            allowOutsideClick: true,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("abonnements.renew.agence") }}',
            type: 'POST',
            data: JSON.stringify({
                _token: '{{ csrf_token() }}',
                id: abonnementId,
                type: data.type,
                duree: parseInt(data.duree),
                montant: data.montant,
                reduction: calculateReduction(data.duree)
            }),
            contentType: 'application/json',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        title: 'Succès!',
                        html: `L'abonnement a été renouvelé jusqu'au ${response.new_end_date}<br>
                               Montant payé: ${response.amount_paid.toLocaleString()} FCFA`,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Erreur!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                console.error('Erreur AJAX:', xhr.responseJSON);
                Swal.fire(
                    'Erreur!', 
                    xhr.responseJSON?.message || 'Une erreur est survenue', 
                    'error'
                );
            }
        });
    }

    // Fonction pour calculer le pourcentage de réduction
    function calculateReduction(duree) {
        switch(parseInt(duree)) {
            case 3:
            case 6: return 20;
            case 12: return 17;
            default: return 0;
        }
    }
});
</script>
<script>
$('.cinetpay-btn').click(async function() {
    const abonnementId = $(this).data('abonnement-id');
    // 1. Sélection type
    const { value: type } = await Swal.fire({
        title: 'Type d\'abonnement',
        input: 'select',
        inputOptions: {
            'standard': 'Standard (5 000 FCFA/mois)',
            'premium': 'Premium (7 000 FCFA/mois)'
        },
        inputValue: 'standard',
        showCancelButton: true,
        cancelButtonText: 'Annuler'
    });
    if (!type) return;

    // 2. Sélection durée
    const dureeOptions = {
        1: {text: '1 mois', reduction: 0},
        3: {text: '3 mois (20% réduction)', reduction: 20},
        6: {text: '6 mois (20% réduction)', reduction: 20},
        12: {text: '12 mois (17% réduction)', reduction: 17}
    };
    let dureeHtml = '';
    for (let d in dureeOptions) {
        dureeHtml += `<option value="${d}" data-reduction="${dureeOptions[d].reduction}">${dureeOptions[d].text}</option>`;
    }
    const { value: duree } = await Swal.fire({
        title: 'Durée',
        html: `<select id="dureeSelect" class="swal2-input">${dureeHtml}</select>`,
        preConfirm: () => $('#dureeSelect').val(),
        showCancelButton: true,
        cancelButtonText: 'Annuler',
    });
    if (!duree) return;

    // 3. Calcul du montant
    let prixBase = type === 'standard' ? 100 : 100;
    let montant = prixBase * duree;
    let reduction = dureeOptions[duree].reduction;
    if (reduction > 0) {
        montant = montant * (1 - reduction / 100);
    }

    // 4. Paiement confirmation
    const confirmPay = await Swal.fire({
        title: 'Confirmer le paiement',
        html: `<div>
            <strong>Type:</strong> ${type}<br>
            <strong>Durée:</strong> ${duree} mois<br>
            <strong>Montant:</strong> ${montant.toLocaleString()} FCFA
        </div>`,
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        confirmButtonText: 'Payer '
    });
    if (!confirmPay.isConfirmed) return;

    // 5. Paiement CinetPay
    const transactionId = 'ABN-' + Date.now();

    CinetPay.setConfig({
        apikey: '{{ config("services.cinetpay.api_key") }}',
        site_id: '{{ config("services.cinetpay.site_id") }}',
        notify_url: '{{ route("cinetpay.notify") }}',
        mode: 'PRODUCTION'
    });

    Swal.fire({
        title: 'Paiement en cours',
        html: 'Veuillez compléter le paiement...',
        allowOutsideClick: true,
        didOpen: () => Swal.showLoading()
    });

    CinetPay.getCheckout({
        transaction_id: transactionId,
        amount: montant,
        currency: 'XOF',
        channels: 'ALL',
        description: `Renouvellement ${type} (${duree} mois)`
    });

    CinetPay.waitResponse(function(data) {
        Swal.close();
        if (data.status === "ACCEPTED") {
            // 6. Appel AJAX à /abonnements/renew
            Swal.fire({
                title: 'Traitement...',
                html: 'Renouvellement de votre abonnement.',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: true,
            });

            $.ajax({
                url: '{{ route("abonnements.renew.agence") }}',
                type: 'POST',
                data: JSON.stringify({
                    _token: '{{ csrf_token() }}',
                    id: abonnementId,
                    type: type,
                    duree: parseInt(duree),
                    montant: montant,
                    reduction: reduction
                }),
                contentType: 'application/json',
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès!',
                            html: `Abonnement renouvelé jusqu'au ${response.new_end_date}<br>Montant payé: ${response.amount_paid.toLocaleString()} FCFA`
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Erreur!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('Erreur!', xhr.responseJSON?.message || 'Une erreur est survenue', 'error');
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Paiement échoué',
                text: data.message || 'Le paiement n\'a pas pu être traité.',
            });
        }
    });

    CinetPay.onError(function(error) {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Erreur de paiement',
            text: error.message || 'Une erreur technique est survenue.'
        });
    });
});
</script>
@endsection