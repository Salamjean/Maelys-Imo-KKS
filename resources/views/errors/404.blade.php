<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - 404</title>
    <link rel="icon" href="{{ asset('assets/images/mae-imo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #02245b;
            --secondary-color: #f38a15;
            --text-color: #2d3748;
            --light-bg: #f7fafc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--light-bg);
            color: var(--text-color);
            text-align: center;
            padding: 20px;
            background-image: radial-gradient(circle at 10% 20%, rgba(243, 138, 21, 0.1) 0%, rgba(2, 36, 91, 0.05) 90%);
            line-height: 1.6;
        }
        
        .error-container {
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }
        
        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .logo {
            width: 150px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        h1 {
            font-size: 5rem;
            color: var(--primary-color);
            margin: 10px 0;
            position: relative;
            display: inline-block;
        }
        
        h1 span {
            color: var(--secondary-color);
        }
        
        h1::after {
            content: '404';
            position: absolute;
            top: 0;
            left: 0;
            color: rgba(243, 138, 21, 0.1);
            font-size: 8rem;
            z-index: -1;
            transform: translate(-20px, -20px);
        }
        
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #4a5568;
        }
        
        .home-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), #03408c);
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(2, 36, 91, 0.2);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .home-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(2, 36, 91, 0.3);
        }
        
        .home-btn:active {
            transform: translateY(1px);
        }
        
        .home-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .home-btn:hover::before {
            left: 100%;
        }
        
        .astronaut {
            width: 120px;
            position: absolute;
            right: -30px;
            bottom: -30px;
            opacity: 0.1;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 600px) {
            h1 {
                font-size: 3.5rem;
            }
            
            h1::after {
                font-size: 5rem;
                transform: translate(-10px, -10px);
            }
            
            .error-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="{{ asset('assets/images/mae-imo.png') }}" alt="Logo" class="logo">
        <h1><span>Erreur</span> 404</h1>
        <p>Oups ! La page que vous cherchez semble s'être envolée dans l'espace.</p>
        <p>Ne vous inquiétez pas, notre équipe de cosmonautes la recherche activement.</p>
        <a href="{{ url('/') }}" class="home-btn">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>
        <img src="https://cdn-icons-png.flaticon.com/512/1139/1139982.png" alt="Astronaut" class="astronaut">
    </div>

    <!-- Font Awesome pour les icônes -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>