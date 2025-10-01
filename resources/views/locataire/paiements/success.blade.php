<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi - Confirmation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #02245b 0%, #001a3a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #ff5e14;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: bounce 0.6s ease-in-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            color: #02245b;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #02245b;
            font-weight: 600;
        }

        .amount {
            font-size: 24px;
            color: #ff5e14;
            font-weight: 700;
        }

        .status-badge {
            background: #d4edda;
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ff5e14;
            color: white;
        }

        .btn-primary:hover {
            background: #e04e00;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #02245b;
            color: white;
        }

        .btn-secondary:hover {
            background: #011a3a;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #02245b;
            border: 2px solid #02245b;
        }

        .btn-outline:hover {
            background: #02245b;
            color: white;
        }

        .confirmation-message {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: #155724;
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 25px;
                margin: 10px;
            }

            .actions {
                flex-direction: column;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
            </svg>
        </div>
        
        <h1>Paiement Réussi !</h1>
        <p class="subtitle">Votre transaction a été traitée avec succès</p>

        <div class="confirmation-message">
            ✅ Votre paiement a été confirmé et enregistré dans notre système.
        </div>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Référence :</span>
                <span class="detail-value">{{ $paiement['reference'] ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Montant :</span>
                <span class="detail-value amount">{{ number_format($paiement['montant'] ?? 0, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Mois couvert :</span>
                <span class="detail-value">{{ $paiement['mois_couvert'] ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Méthode :</span>
                <span class="detail-value">{{ $paiement['methode_paiement'] ?? 'Mobile Money' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Statut :</span>
                <span class="status-badge">{{ $paiement['statut'] ?? 'Payé' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date :</span>
                <span class="detail-value">{{ $paiement['date_paiement'] ?? now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="actions">
            <a href="/" class="btn btn-primary">Retour à l'accueil</a>
            <a href="/historique-paiements" class="btn btn-secondary">Voir l'historique</a>
        </div>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 14px;">
                Conservez cette référence pour tout suivi.
            </p>
        </div>
    </div>

    <script>
        // Animation supplémentaire
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.success-container');
            setTimeout(() => {
                container.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    container.style.transform = 'scale(1)';
                }, 150);
            }, 600);
        });
    </script>
</body>
</html>