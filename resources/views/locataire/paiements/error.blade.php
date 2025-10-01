<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur de Paiement</title>
    <style>
        /* Mêmes styles que success.blade.php mais avec couleurs d'erreur */
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

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            color: #02245b;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #ff5e14;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
        
        <h1>Erreur de Paiement</h1>
        
        <div class="error-message">
            {{ $message ?? 'Une erreur est survenue lors du traitement de votre paiement.' }}
        </div>

        <a href="/" class="btn">Retour à l'accueil</a>
    </div>
</body>
</html>