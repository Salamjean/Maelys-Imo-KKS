<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activation du compte propriétaire</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.cinetpay.com/seamless/main.js"></script>
    <style>
        :root {
            --primary: #02245b;
            --primary-dark: #02245b;
            --secondary: #02245b;
            --light: #f8f9ff;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark);
            min-height: 100vh;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .alert {
            background-color: rgba(248, 150, 30, 0.1);
            border-left: 4px solid var(--warning);
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        
        .alert-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: var(--warning);
        }
        
        .subscription-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: var(--border-radius);
            padding: 40px;
            margin: 40px 0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .subscription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .subscription-card h2 {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 15px;
            text-align: center;
        }
        
        .price {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary);
            margin: 25px 0;
            text-align: center;
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .price span {
            font-size: 1.2rem;
            font-weight: 400;
            color: #6c757d;
        }
        
        .features {
            margin: 30px 0;
        }
        
        .features li {
            margin-bottom: 15px;
            list-style-type: none;
            position: relative;
            padding-left: 35px;
            font-size: 1.1rem;
        }
        
        .features li:before {
            content: "✓";
            color: var(--success);
            position: absolute;
            left: 0;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .btn-subscribe {
            display: block;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 18px;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            margin-top: 30px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .btn-subscribe:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #3730a3 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .highlight {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            font-size: 0.9rem;
            border-bottom-left-radius: 20px;
            font-weight: 600;
        }
        
        /* Formulaire caché */
        .payment-form {
            display: none;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .subscription-card {
                padding: 30px 20px;
            }
            
            .price {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>Activation de votre compte</h1>
            <p>Accédez à toutes les fonctionnalités premium</p>
        </div>

        <!-- Contenu principal -->
        <div class="content">
            <div class="alert">
                <strong>Action requise :</strong> Votre compte nécessite un abonnement pour être activé.
            </div>

            <!-- Carte d'abonnement -->
            <div class="subscription-card">
                <h2>Abonnement Premium</h2>
                <div class="price">100 Fcfa / mois</div>
                <ul class="features">
                    <li>Gestion illimitée de propriétés</li>
                    <li>Support technique prioritaire</li>
                    <li>Statistiques détaillées</li>
                </ul>
                <button class="btn-subscribe" onclick="initiatePayment()">Souscrire</button>
            </div>
            <a href="/"><button style="padding: 10px; background-color:#02245b; color:white; cursor: pointer;">Retour a la page d'accueil</button></a>
            @auth('owner')
                <form id="paymentForm" method="POST" action="{{ route('owner.activate') }}" style="display: none;">
                    @csrf
                    <input type="hidden" name="transaction_id" id="transaction_id">
                    <input type="hidden" name="amount" value="100">
                </form>
            @else
                <script>window.location.href = "{{ route('owner.login') }}";</script>
            @endauth
        </div>
    </div>

    <script>
        function initiatePayment() {
            // Configuration de CinetPay
            CinetPay.setConfig({
                apikey: '521006956621e4e7a6a3d16.70681548',
                site_id: '859043',
                notify_url: '{{ route("cinetpay.notify") }}',
                mode: 'PRODUCTION'
            });

            // Génération d'un ID de transaction
            const transactionId = 'SUB-' + Date.now();
            document.getElementById('transaction_id').value = transactionId;

            CinetPay.getCheckout({
                transaction_id: transactionId,
                amount: 100,
                currency: 'XOF',
                channels: 'ALL',
                description: 'Abonnement Premium Propriétaire',
            });

            // Gestion de la réponse
            CinetPay.waitResponse(function(data) {
                if (data.status === "ACCEPTED") {
                    Swal.fire({
                        icon: 'success',
                        title: 'Paiement réussi',
                        text: 'Votre abonnement est en cours d\'activation...',
                        showConfirmButton: false
                    }).then(() => {
                        document.getElementById('paymentForm').submit();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Paiement échoué',
                        text: 'Le paiement n\'a pas pu être accepté.',
                    });
                }
            });

            // Gestion des erreurs
            CinetPay.onError(function(error) {
                console.error('Erreur CinetPay:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors du paiement.',
                });
            });
        }
    </script>
</body>
</html>