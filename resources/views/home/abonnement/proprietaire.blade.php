<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Propriétaire</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.cinetpay.com/seamless/main.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --accent: #f72585;
            --light: #f8f9ff;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --border-radius: 10px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafc;
            color: var(--dark);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::after {
            content: "";
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .header::before {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 0.95rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 30px;
        }
        
        .alert {
            background-color: rgba(248, 150, 30, 0.1);
            border-left: 4px solid var(--warning);
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .alert strong {
            margin-right: 5px;
        }
        
        .subscription-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        
        .subscription-card {
            border: 1px solid #eaeef5;
            border-radius: var(--border-radius);
            padding: 25px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            background: white;
            cursor: pointer;
        }
        
        .subscription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.15);
            border-color: var(--primary-light);
        }
        
        .subscription-card.selected {
            border: 2px solid var(--primary);
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .subscription-card h2 {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 10px;
            text-align: center;
            font-weight: 600;
        }
        
        .price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            margin: 15px 0;
            text-align: center;
        }
        
        .price span {
            display: block;
            font-size: 0.85rem;
            font-weight: 400;
            color: #64748b;
            margin-top: 5px;
        }
        
        .features {
            margin: 20px 0;
        }
        
        .features li {
            margin-bottom: 10px;
            list-style-type: none;
            position: relative;
            padding-left: 25px;
            font-size: 0.85rem;
        }
        
        .features li:before {
            content: "✓";
            color: var(--success);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .discount-badge {
            background-color: var(--accent);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .btn-subscribe {
            display: block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 14px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            width: 100%;
            transition: var(--transition);
            letter-spacing: 0.5px;
        }
        
        .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-subscribe:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .btn-home {
            display: inline-block;
            background: white;
            color: var(--primary);
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            margin-top: 15px;
            border: 1px solid var(--primary);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            text-align: center;
            width: auto;
        }
        
        .btn-home:hover {
            background: var(--primary);
            color: white;
        }
        
        .highlight {
            position: absolute;
            top: 15px;
            right: -30px;
            background: var(--accent);
            color: white;
            padding: 3px 30px;
            font-size: 0.75rem;
            transform: rotate(45deg);
            font-weight: 600;
            width: 120px;
            text-align: center;
        }
        
        .payment-summary {
            background: #f8fafc;
            border-radius: var(--border-radius);
            padding: 15px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        
        .summary-header {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary);
            font-size: 0.95rem;
        }
        
        .summary-details div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .summary-details span:first-child {
            font-weight: 500;
            color: #64748b;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .content {
                padding: 25px 20px;
            }
            
            .subscription-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
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