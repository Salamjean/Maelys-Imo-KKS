<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Agence</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.cinetpay.com/seamless/main.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('abonnement/styles.css') }}">
    <style>
        :root {
            --primary: #ff5e14; /* Orange pour agences */
            --secondary: #062a64; /* Bleu pour contrastes */
            --highlight: #f59e0b; /* Jaune pour badges premium */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .alert {
            background-color: #fff8e1;
            border-left: 4px solid var(--highlight);
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .alert strong {
            color: var(--primary);
        }

        /* Toggle Standard/Premium */
        .subscription-toggle {
            display: flex;
            justify-content: center;
            margin: 25px 0;
        }

        .toggle-option {
            padding: 12px 25px;
            background: #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .toggle-option:first-child {
            border-radius: 8px 0 0 8px;
        }

        .toggle-option:last-child {
            border-radius: 0 8px 8px 0;
        }

        .toggle-option.active {
            background: var(--primary);
            color: white;
        }

        /* Cartes d'abonnement */
        .subscription-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .subscription-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
            border: 2px solid transparent;
        }

        .subscription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .subscription-card.selected {
            border-color: var(--primary);
            background-color: #fffaf5;
        }

        .highlight {
            position: absolute;
            top: -10px;
            right: 20px;
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .highlight-premium {
            background: var(--highlight);
        }

        .subscription-card h2 {
            color: var(--secondary);
            font-size: 1.5rem;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .price span {
            display: block;
            font-size: 1rem;
            color: #666;
            font-weight: 400;
        }

        .features {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .features li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }

        .features li:before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            color: var(--primary);
            position: absolute;
            left: 0;
        }

        /* Récapitulatif */
        .payment-summary {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: none;
        }

        .summary-header {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-details div {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .summary-details div span:first-child {
            font-weight: 500;
            color: #555;
        }

        .summary-details div span:last-child {
            font-weight: 600;
            color: var(--secondary);
        }

        /* Boutons */
        .btn-subscribe {
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: background 0.3s ease;
            margin: 10px 0;
        }

        .btn-subscribe:hover {
            background: #e05510;
        }

        .btn-subscribe:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .btn-home {
            display: block;
            text-align: center;
            color: var(--secondary);
            padding: 10px;
            margin-top: 15px;
            text-decoration: none;
            font-weight: 500;
        }

        .btn-home:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .subscription-options {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-building"></i> Abonnement Agence</h1>
            <p>Choisissez votre formule préférée</p>
        </div>

        <div class="content">
            <div class="alert">
                <strong>Action requise :</strong> Activez votre compte avec un abonnement
            </div>

            <div class="subscription-toggle">
                <div class="toggle-option active" onclick="switchSubscription('standard')">Standard</div>
                <div class="toggle-option" onclick="switchSubscription('premium')">Premium</div>
            </div>

            <div class="subscription-options">
                <!-- Standard -->
                <div class="subscription-card standard-card" onclick="selectSubscription(1, 10000, 'standard')">
                    <h2>1 Mois</h2>
                    <div class="price">
                        10.000 Fcfa
                        <span>10.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Gestion jusqu'à 15 biens</li>
                        <li>Support technique standard</li>
                        <li>Tableau de bord de base</li>
                    </ul>
                </div>

                <div class="subscription-card standard-card" onclick="selectSubscription(3, 24000, 'standard')">
                    <div class="highlight">Économisez 20%</div>
                    <h2>3 Mois</h2>
                    <div class="price">
                        24.000 Fcfa
                        <span>8.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Gestion jusqu'à 15 biens</li>
                        <li>Support prioritaire</li>
                        <li>Statistiques avancées</li>
                    </ul>
                </div>

                <div class="subscription-card standard-card" onclick="selectSubscription(6, 48000, 'standard')">
                    <div class="highlight">Économisez 20%</div>
                    <h2>6 Mois</h2>
                    <div class="price">
                        48.0000 Fcfa
                        <span>8.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Gestion jusqu'à 15 biens</li>
                        <li>Support prioritaire</li>
                        <li>Statistiques avancées</li>
                    </ul>
                </div>
                <div class="subscription-card standard-card" onclick="selectSubscription(12, 100000, 'standard')">
                    <div class="highlight">Économisez 20%</div>
                    <h2>1 an</h2>
                    <div class="price">
                        100.0000 Fcfa
                        <span>8.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Gestion jusqu'à 15 biens</li>
                        <li>Support prioritaire</li>
                        <li>Statistiques avancées</li>
                    </ul>
                </div>

                <!-- Premium -->
                <div class="subscription-card premium-card" style="display:none" onclick="selectSubscription(1, 100, 'premium')">
                    <div class="highlight highlight-premium">+30% de visibilité</div>
                    <h2>1 Mois Premium</h2>
                    <div class="price">
                        15.000 Fcfa
                        <span>15.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Biens illimités</li>
                        <li>20 comptes agents inclus</li>
                        <li>Listage en avant</li>
                        <li>Support premium 24/7</li>
                        <li>Analytics complets</li>
                    </ul>
                </div>

                <div class="subscription-card premium-card" style="display:none" onclick="selectSubscription(3, 36000, 'premium')">
                    <div class="highlight highlight-premium">+50% de visibilité</div>
                    <h2>3 Mois Premium</h2>
                    <div class="price">
                        36.000 Fcfa
                        <span>12.000 Fcfa/mois</span>
                    </div>
                    <ul class="features">
                        <li>Biens et agents illimités</li>
                        <li>Positionnement prioritaire</li>
                        <li>Badge Premium visible</li>
                        <li>Support VIP dédié</li>
                        <li>Rapports personnalisés</li>
                    </ul>
                </div>
            </div>

            <div class="payment-summary" id="paymentSummary" style="display: none;">
                <div class="summary-header">Récapitulatif</div>
                <div class="summary-details">
                    <div><span>Type :</span> <span id="summaryType"></span></div>
                    <div><span>Durée :</span> <span id="summaryDuration"></span></div>
                    <div><span>Total :</span> <span id="summaryAmount"></span> Fcfa</div>
                    <div><span>Expiration :</span> <span id="summaryExpiry"></span></div>
                </div>
            </div>

            <button id="btnSubscribe" class="btn-subscribe" disabled onclick="initiatePayment()">
                Choisir cet abonnement
            </button>

            <a href="/" class="btn-home"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>

            @auth('agence')
                <form id="paymentForm" method="POST" action="{{ route('agence.activate') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="transaction_id" id="transaction_id">
                    <input type="hidden" name="amount" id="amount">
                    <input type="hidden" name="duration" id="duration">
                    <input type="hidden" name="type" id="type" value="standard">
                </form>
            @else
                <script>window.location.href = "{{ route('agence.login') }}";</script>
            @endauth
        </div>
    </div>

    <script>
        let selectedSubscription = null;

        function switchSubscription(type) {
            // Mettre à jour l'interface
            document.querySelectorAll('.toggle-option').forEach(option => {
                option.classList.remove('active');
            });
            event.currentTarget.classList.add('active');

            // Afficher/masquer les cartes
            if (type === 'standard') {
                document.querySelectorAll('.standard-card').forEach(card => {
                    card.style.display = 'block';
                });
                document.querySelectorAll('.premium-card').forEach(card => {
                    card.style.display = 'none';
                });
            } else {
                document.querySelectorAll('.standard-card').forEach(card => {
                    card.style.display = 'none';
                });
                document.querySelectorAll('.premium-card').forEach(card => {
                    card.style.display = 'block';
                });
            }

            // Réinitialiser la sélection
            selectedSubscription = null;
            document.getElementById('paymentSummary').style.display = 'none';
            document.getElementById('btnSubscribe').disabled = true;
            document.getElementById('btnSubscribe').innerHTML = 'Choisir cet abonnement';
            document.getElementById('type').value = type;
        }

        function selectSubscription(duration, amount, type) {
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
            document.getElementById('summaryType').textContent = type === 'premium' ? 'Premium' : 'Standard';
            document.getElementById('summaryDuration').textContent = duration + ' mois';
            document.getElementById('summaryAmount').textContent = amount.toLocaleString('fr-FR');
            document.getElementById('summaryExpiry').textContent = expiryDate.toLocaleDateString('fr-FR');
            
            document.getElementById('paymentSummary').style.display = 'block';
            
            // Mettre à jour le bouton
            const btnSubscribe = document.getElementById('btnSubscribe');
            btnSubscribe.disabled = false;
            btnSubscribe.innerHTML = `Souscrire - ${amount.toLocaleString('fr-FR')} Fcfa`;
            
            // Stocker la sélection
            selectedSubscription = { duration, amount, type };
            
            // Mettre à jour les champs cachés
            document.getElementById('amount').value = amount;
            document.getElementById('duration').value = duration;
            document.getElementById('type').value = type;
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

            // Confirmation
            const { isConfirmed } = await Swal.fire({
                title: 'Confirmer le paiement',
                html: `
                    <div style="text-align: left; margin: 10px 0;">
                        <div><strong>Type :</strong> ${selectedSubscription.type === 'premium' ? 'Premium' : 'Standard'}</div>
                        <div><strong>Durée :</strong> ${selectedSubscription.duration} mois</div>
                        <div><strong>Montant :</strong> ${selectedSubscription.amount.toLocaleString('fr-FR')} Fcfa</div>
                        <div class="mt-3"><small>Vous serez redirigé vers la plateforme de paiement</small></div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Payer maintenant',
                cancelButtonText: 'Annuler',
                confirmButtonColor: 'var(--primary)',
                cancelButtonColor: '#6c757d'
            });

            if (!isConfirmed) return;

            // Configuration CinetPay
            CinetPay.setConfig({
                apikey: '{{ config("services.cinetpay.api_key") }}',
                site_id: '{{ config("services.cinetpay.site_id") }}',
                notify_url: '{{ route("cinetpay.notify") }}',
                mode: 'PRODUCTION'
            });

            // ID de transaction
            const transactionId = 'AG-' + Date.now();
            document.getElementById('transaction_id').value = transactionId;

            // Chargement
            Swal.fire({
                title: 'Redirection en cours',
                html: 'Préparation du paiement...',
                allowOutsideClick: true,
                didOpen: () => Swal.showLoading()
            });

            // Données client
            const customer = {
                name: '{{ Auth::guard("agence")->user()->name ?? "Agence" }}',
                email: '{{ Auth::guard("agence")->user()->email ?? "contact@agence.com" }}',
                phone: '{{ Auth::guard("agence")->user()->telephone ?? "00000000" }}'
            };

            // Paiement
            CinetPay.getCheckout({
                transaction_id: transactionId,
                amount: selectedSubscription.amount,
                currency: 'XOF',
                channels: 'ALL',
                description: `Abonnement Agence ${selectedSubscription.type} (${selectedSubscription.duration} mois)`,
                customer_name: customer.name,
                customer_email: customer.email,
                customer_phone_number: customer.phone,
                customer_address: 'Abidjan',
                customer_city: 'Abidjan',
                customer_country: 'CI',
                customer_state: 'CI',
                customer_zip_code: '00225'
            });

            // Gestion réponse
            CinetPay.waitResponse(function(data) {
                Swal.close();
                if (data.status === "ACCEPTED") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Paiement accepté',
                        text: 'Activation en cours...',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        document.getElementById('paymentForm').submit();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Échec du paiement',
                        text: data.message || 'Erreur lors du traitement'
                    });
                }
            });

            // Gestion erreurs
            CinetPay.onError(function(error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    html: `Une erreur est survenue<br><small>${error.message || 'Veuillez réessayer'}</small>`
                });
            });
        }
    </script>
</body>
</html>