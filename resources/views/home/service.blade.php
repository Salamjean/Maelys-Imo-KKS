@extends('home.pages.layouts.template')
@section('content')
<style>
    .service-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    .page-title {
        text-align: center;
        font-size: 32px;
        color: #02245b;
        margin-bottom: 30px;
    }
    .intro-text {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 40px;
        color: #555;
        line-height: 1.6;
    }
    .services-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 40px;
    }
    .service-card {
        width: 32%;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .service-card:hover {
        transform: translateY(-5px);
    }
    .service-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .service-content {
        padding: 20px;
    }
    .service-title {
        font-size: 22px;
        color: #02245b;
        margin-bottom: 15px;
    }
    .service-description {
        color: #666;
        margin-bottom: 15px;
        line-height: 1.5;
    }
    .service-features {
        margin-bottom: 20px;
    }
    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        color: #555;
    }
    .feature-icon {
        color: #27ae60;
        margin-right: 10px;
    }
    .service-button {
        display: inline-block;
        background: #02245b;
        color: white;
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        transition: background 0.3s ease;
    }
    .service-button:hover {
        background: #02245b;
    }
    .process-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 40px;
    }
    .process-title {
        text-align: center;
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 30px;
    }
    .process-steps {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .process-step {
        width: 23%;
        text-align: center;
    }
    .step-number {
        background: #e3f2fd;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: #02245b;
        font-weight: bold;
        font-size: 20px;
    }
    .step-title {
        font-weight: bold;
        margin-bottom: 10px;
        color: #2c3e50;
    }
    .step-description {
        color: #666;
        font-size: 14px;
    }
    .cta-section {
        text-align: center;
        margin-top: 40px;
    }
    .cta-title {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 15px;
    }
    .cta-text {
        color: #666;
        margin-bottom: 20px;
    }
    .cta-button {
        display: inline-block;
        background: #02245b;
        color: white;
        padding: 12px 30px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    .cta-button:hover {
        background: #2980b9;
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
        .service-card {
            width: 48%;
        }
        .process-step {
            width: 48%;
            margin-bottom: 20px;
        }
    }
    @media (max-width: 600px) {
        .service-card {
            width: 100%;
        }
        .process-step {
            width: 100%;
        }
    }
</style>

<div class="service-container">
    <h1 class="page-title">Nos Services Immobiliers</h1>
    
    <p class="intro-text">
        Découvrez notre sélection exclusive de biens immobiliers à louer. Que vous cherchiez un appartement confortable, 
        une maison spacieuse ou un bureau professionnel, nous avons la solution adaptée à vos besoins.
    </p>

    <div class="services-grid">
        <!-- Appartement -->
        <div class="service-card">
            <img src="{{ asset('assets/images/appart.jpg') }}" alt="Appartement" class="service-image">
            <div class="service-content">
                <h2 class="service-title">Appartements</h2>
                <p class="service-description">
                    Des appartements modernes et fonctionnels, du studio au 5 pièces, dans les meilleurs quartiers de la ville.
                </p>
                <div class="service-features">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Meublés ou non meublés
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Sécurité 24/7
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Proches commodités
                    </div>
                </div>
                <a href="{{ route('bien.appartement') }}" class="service-button">
                    Voir les offres
                </a>
            </div>
        </div>

        <!-- Maison -->
        <div class="service-card">
            <img src="{{ asset('assets/images/maison_ser.jpg') }}" alt="Maison" class="service-image">
            <div class="service-content">
                <h2 class="service-title">Maisons</h2>
                <p class="service-description">
                    Des maisons spacieuses avec jardin, idéales pour les familles, dans des environnements calmes et sécurisés.
                </p>
                <div class="service-features">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> De 2 à 6 chambres
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Jardin et garage
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Quartiers résidentiels
                    </div>
                </div>
                <a href="{{ route('bien.maison') }}" class="service-button">
                    Voir les offres
                </a>
            </div>
        </div>

        <!-- Bureau -->
        <div class="service-card">
            <img src="{{ asset('assets/images/bureau.jpg') }}" alt="Bureau" class="service-image">
            <div class="service-content">
                <h2 class="service-title">Bureaux</h2>
                <p class="service-description">
                    Des espaces professionnels modernes, adaptés aux entreprises de toutes tailles, en centre-ville ou zones d'affaires.
                </p>
                <div class="service-features">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Espaces modulables
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Accès 24/7
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">✓</span> Services inclus
                    </div>
                </div>
                <a href="{{ route('bien.terrain') }}" class="service-button">
                    Voir les offres
                </a>
            </div>
        </div>
    </div>

    <div class="process-section">
        <h2 class="process-title">Comment louer avec nous</h2>
        <div class="process-steps">
            <div class="process-step">
                <div class="step-number">1</div>
                <h3 class="step-title">Recherche</h3>
                <p class="step-description">Trouvez le bien qui correspond à vos critères</p>
            </div>
            <div class="process-step">
                <div class="step-number">2</div>
                <h3 class="step-title">Visite</h3>
                <p class="step-description">Planifiez une visite avec notre équipe</p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Dossier</h3>
                <p class="step-description">Complétez votre dossier locatif</p>
            </div>
            <div class="process-step">
                <div class="step-number">4</div>
                <h3 class="step-title">Signature</h3>
                <p class="step-description">Signez votre contrat et emménagez</p>
            </div>
        </div>
    </div>

    <div class="cta-section">
        <h3 class="cta-title">Vous ne trouvez pas ce que vous cherchez ?</h3>
        <p class="cta-text">Notre équipe se fera un plaisir de vous aider à trouver le bien parfait.</p>
        <a href="#" class="cta-button">Contactez-nous</a>
    </div>
</div>
@endsection