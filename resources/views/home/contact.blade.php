@extends('home.pages.layouts.template')
@section('content')
    <style>
        /* Styles de base */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 50%;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* En-tête */
        h1 {
            color: #02245b;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2em;
        }
        
        /* Formulaire */
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #02245b;
            font-weight: bold;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #ff5e14;
            box-shadow: 0 0 5px rgba(255, 94, 20, 0.3);
        }
        
        textarea {
            height: 150px;
            resize: vertical;
        }
        
        /* Bouton */
        .submit-btn {
            background-color: #ff5e14;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            display: block;
            margin: 0 auto;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #e05512;
        }
        
        /* Section présentation */
        .presentation {
            background-color: #02245b;
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .presentation h2 {
            color: #ff5e14;
            margin-top: 0;
        }
        
        /* Pied de page */
        .contact-footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
        }
        
        .contact-info {
            color: #02245b;
            font-weight: bold;
        }
    </style>

    <!-- Ajout de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="container">
        <h1>Contactez-nous</h1>
        
        <div class="presentation">
            <h2>Maelys-IMO : Votre partenaire immobilier</h2>
            <p>Maelys-IMO est une plateforme innovante de gestion immobilière qui simplifie la gestion de votre patrimoine. Notre solution vous permet de :</p>
            <ul>
                <li>Centraliser la gestion de vos biens immobiliers</li>
                <li>Automatiser les tâches administratives</li>
                <li>Suivre vos locations en temps réel</li>
                <li>Optimiser votre rendement locatif</li>
            </ul>
        </div>
        
        <div class="contact-form">
            <form action="{{route('maelys.contact')}}" method="POST" id="contactForm">
                @csrf
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Sujet</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Votre message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Envoyer le message</button>
            </form>
        </div>
        
        <div class="contact-footer">
            <p>Pour toute question, n'hésitez pas à nous contacter à l'adresse :</p>
            <p class="contact-info">contact@maelysimo.com</p>
        </div>
    </div>

    <script>
        // Gestion des messages de succès/erreur avec SweetAlert2
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Message envoyé!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#ff5e14',
                background: '#fff',
                backdrop: `
                    rgba(2,36,91,0.4)
                `
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: '{{ session('error') }}',
                confirmButtonColor: '#ff5e14',
                background: '#fff'
            });
        @endif
    </script>
@endsection