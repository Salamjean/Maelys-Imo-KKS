<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Propriétaire</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.cinetpay.com/seamless/main.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('abonnement/styles.css') }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Abonnement Propriétaire</h1>
            <p>Choisissez votre formule préférée</p>
        </div>

        <div class="content">
            <div class="alert">
                <strong>Action requise :</strong> Activez votre compte avec un abonnement
            </div>

            <div class="subscription-options">
                <div class="subscription-card" onclick="selectSubscription(1, 5000)">
                    <h2>1 Mois</h2>
                    <div class="price">
                        5.000 Fcfa
                        <span>5.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Accès complet pour 1 mois</li>
                        <li>Support technique</li>
                    </ul>
                </div>

                <div class="subscription-card" onclick="selectSubscription(3, 12000)">
                    <div class="highlight">Économisez 20%</div>
                    <h2>3 Mois</h2>
                    <div class="price">
                        12.000 Fcfa
                        <span>4.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Accès complet pour 3 mois</li>
                        <li>Support technique standard</li>
                    </ul>
                </div>

                <div class="subscription-card" onclick="selectSubscription(6, 24000)">
                    <div class="highlight">Économisez 20%</div>
                    <h2>6 Mois</h2>
                    <div class="price">
                        24.000 Fcfa
                        <span>4.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Accès complet pour 6 mois</li>
                        <li>Support technique prioritaire</li>
                    </ul>
                </div>

                <div class="subscription-card" onclick="selectSubscription(12, 50000)">
                    <div class="highlight">Économisez 17%</div>
                    <h2>1 An</h2>
                    <div class="price">
                        50.000 Fcfa
                        <span>4.167 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Accès complet pour 1 an</li>
                        <li>Support technique 24/7</li>
                    </ul>
                </div>
            </div>

            <div class="payment-summary" id="paymentSummary" style="display: none;">
                <div class="summary-header">Récapitulatif</div>
                <div class="summary-details">
                    <div><span>Durée :</span> <span id="summaryDuration"></span></div>
                    <div><span>Total :</span> <span id="summaryAmount"></span> Fcfa</div>
                    <div><span>Expiration :</span> <span id="summaryExpiry"></span></div>
                </div>
            </div>

            <button id="btnSubscribe" class="btn-subscribe" disabled onclick="initiatePayment()">
                Choisir cet abonnement
            </button>

            <a href="/" class="btn-home">Retour à l'accueil</a>

            @auth('owner')
                <form id="paymentForm" method="POST" action="{{ route('owner.activate') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="transaction_id" id="transaction_id">
                    <input type="hidden" name="amount" id="amount">
                    <input type="hidden" name="duration" id="duration">
                </form>
            @else
                <script>window.location.href = "{{ route('owner.login') }}";</script>
            @endauth
        </div>
    </div>

    <script>
        let selectedSubscription = null;

        function selectSubscription(duration, amount) {
            // Désélectionner toutes les cartes
            document.querySelectorAll('.subscription-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Sélectionner la carte cliquée
            event.currentTarget.classList.add('selected');
            
            // Calcul de la date d'expiration
            const expiryDate = new Date();
            expiryDate.setMonth(expiryDate.getMonth() + duration);
            
            // Mettre à jour le récapitulatif
            const summary = document.getElementById('paymentSummary');
            summary.style.display = 'block';
            summary.style.animation = 'fadeIn 0.3s ease-out';
            
            document.getElementById('summaryDuration').textContent = duration + ' mois';
            document.getElementById('summaryAmount').textContent = amount.toLocaleString();
            document.getElementById('summaryExpiry').textContent = expiryDate.toLocaleDateString('fr-FR');
            
            // Mettre à jour le bouton de souscription
            const btnSubscribe = document.getElementById('btnSubscribe');
            btnSubscribe.disabled = false;
            btnSubscribe.innerHTML = `Souscrire - ${amount.toLocaleString()} Fcfa`;
            
            // Stocker la sélection
            selectedSubscription = { duration, amount };
            
            // Mettre à jour les champs cachés
            document.getElementById('amount').value = amount;
            document.getElementById('duration').value = duration;
        }

        async function initiatePayment() {
            if (!selectedSubscription) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Sélection requise',
                    text: 'Veuillez choisir un abonnement avant de continuer.',
                    confirmButtonColor: 'var(--primary)'
                });
                return;
            }

            // Afficher une confirmation
            const { isConfirmed } = await Swal.fire({
                title: 'Confirmer le paiement',
                html: `<div style="text-align: left; margin: 10px 0;">
                    <div><strong>Formule :</strong> ${selectedSubscription.duration} mois</div>
                    <div><strong>Montant :</strong> ${selectedSubscription.amount.toLocaleString()} Fcfa</div>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Payer maintenant',
                cancelButtonText: 'Annuler',
                confirmButtonColor: 'var(--primary)',
                cancelButtonColor: '#94a3b8'
            });

            if (!isConfirmed) return;

            // Configuration de CinetPay
            CinetPay.setConfig({
                apikey: '{{ config("services.cinetpay.api_key") }}',
                site_id: '{{ config("services.cinetpay.site_id") }}',
                notify_url: '{{ route("cinetpay.notify") }}',
                mode: 'PRODUCTION'
            });

            // Génération d'un ID de transaction
            const transactionId = 'PROP-' + Date.now();
            document.getElementById('transaction_id').value = transactionId;

            // Afficher un loader
            Swal.fire({
                title: 'Redirection en cours',
                html: 'Préparation du paiement...',
                allowOutsideClick: true,
                showConfirmButton: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Initialiser le paiement
            CinetPay.getCheckout({
                transaction_id: transactionId,
                amount: selectedSubscription.amount,
                currency: 'XOF',
                channels: 'ALL',
                description: `Abonnement Propriétaire (${selectedSubscription.duration} mois)`,
            });

            // Gestion de la réponse
            CinetPay.waitResponse(function(data) {
                Swal.close();
                if (data.status === "ACCEPTED") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Paiement accepté',
                        text: 'Activation en cours...',
                        confirmButtonColor: 'var(--primary)',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        document.getElementById('paymentForm').submit();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Paiement échoué',
                        text: data.message || 'Le paiement n\'a pas pu être traité.',
                        confirmButtonColor: 'var(--primary)'
                    });
                }
            });

            // Gestion des erreurs
            CinetPay.onError(function(error) {
                Swal.close();
                console.error('Erreur CinetPay:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    html: `Une erreur est survenue<br><small>${error.message || 'Veuillez réessayer'}</small>`,
                    confirmButtonColor: 'var(--primary)'
                });
            });
        }
    </script>
</body>
</html>