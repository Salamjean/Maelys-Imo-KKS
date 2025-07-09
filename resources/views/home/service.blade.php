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
    .platform-section {
        background: #02245b;
        color: white;
        padding: 40px;
        border-radius: 8px;
        margin-bottom: 40px;
    }
    .platform-title {
        text-align: center;
        font-size: 28px;
        margin-bottom: 30px;
    }
    .platform-features {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .platform-feature {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .platform-icon {
        margin-right: 10px;
        color: #27ae60;
        font-weight: bold;
    }
    .platform-text {
        flex: 1;
    }
    
    /* Nouveaux styles pour les sections propriétaires/agences */
    .target-section {
        margin-bottom: 50px;
    }
    .target-header {
        background: linear-gradient(135deg, #02245b 0%, #ff5e14 100%);
        color: white;
        padding: 20px;
        border-radius: 8px 8px 0 0;
        margin-bottom: 0;
    }
    .target-content {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 0 0 8px 8px;
    }
    .benefit-list {
        margin-top: 20px;
    }
    .benefit-item {
        display: flex;
        margin-bottom: 15px;
        align-items: flex-start;
    }
    .benefit-number {
        background: #02245b;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    .benefit-text {
        flex: 1;
    }
    .benefit-title {
        font-weight: bold;
        color: #02245b;
        margin-bottom: 5px;
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
        .platform-features {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="service-container">
    <h1 class="page-title">Nos Services Immobiliers</h1>
    
    <p class="intro-text">
       Chez <span style="color:#ff5e14 ">MAELYS-</span><span style="color: #02245b">IMO</span>, nous transformons la gestion immobilière en offrant une solution digitale tout-en-un qui allie technologie de pointe, flexibilité et efficacité. Que vous soyez propriétaire d'un bien immobilier ou gestionnaire d'une agence, notre plateforme innovante vous permet de gérer vos propriétés de manière totalement dématérialisée, sécurisée et optimisée.
    </p>

    <div class="services-grid">
        <!-- Appartement -->
        <div class="service-card">
            <img src="{{ asset('assets/images/appart.jpg') }}" alt="Appartement" class="service-image">
            <div class="service-content">
                <h2 class="service-title">Appartements</h2>
                <p class="service-description" style="text-align: justify">
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

    <!-- Section pour les propriétaires -->
    <div class="target-section">
        <h2 class="target-header">Pour les Propriétaires : Un Contrôle Total de Votre Patrimoine Immobilier</h2>
        <div class="target-content">
            <p>En tant que propriétaire, vous avez des objectifs clairs : maximiser vos revenus locatifs, réduire les périodes de vacance, et gérer efficacement votre patrimoine. <strong>MAELYS-IMO</strong> vous offre une solution qui vous permet de tout gérer à partir d'une interface simple et intuitive, à tout moment et de n'importe où.</p>
            
            <div class="benefit-list">
                <div class="benefit-item">
                    <div class="benefit-number">1</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Gestion Simplifiée des Biens</div>
                        <p>La gestion de vos biens immobiliers n'a jamais été aussi simple. Vous pouvez mettre vos biens en location en quelques clics : créez des annonces attractives, fixez les loyers, et gérez les visites grâce à notre plateforme. Vous avez un accès immédiat à toutes les informations liées à vos biens, ce qui vous permet de suivre leur état et leur rentabilité en temps réel.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">2</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Recouvrement des Loyers Automatisé</div>
                        <p>Fini le stress des retards de paiement ! Grâce à <strong>MAELYS-IMO</strong>, les loyers sont collectés via notre application mobile, que ce soit par Mobile Money ou en espèces auprès de vous ou de votre agent de recouvrement mandaté. En cas d'impayés, nous activons automatiquement un processus de recouvrement pour récupérer rapidement les sommes dues. Vous êtes informé à chaque étape du processus, vous offrant ainsi une tranquillité d'esprit totale.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">3</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Gestion des Contrats et Documents Juridiques</div>
                        <p>La plateforme prend en charge la rédaction, la signature et la gestion de vos contrats de location, de manière entièrement numérique et sécurisée. Vous êtes assuré de respecter toutes les obligations légales locales grâce à des modèles de contrats personnalisés, adaptés à chaque bien et à chaque locataire.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">4</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Optimisation de Rentabilité</div>
                        <p>Nous vous aidons à maximiser la rentabilité de vos investissements en vous fournissant des outils analytiques avancés. Grâce à notre plateforme, vous pouvez ajuster les loyers en fonction du marché local, optimiser les coûts de maintenance et réduire les périodes de vacance locative. Vous bénéficiez également de rapports détaillés sur la rentabilité de vos biens, afin de prendre des décisions éclairées pour votre portefeuille.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">5</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Suivi des Demandes d'Entretien</div>
                        <p>Les demandes d'entretien sont traitées rapidement grâce à notre système intégré de gestion des incidents. Vous pouvez suivre l'avancement des réparations en temps réel, gérer les prestataires de services et vous assurer que vos biens sont toujours en excellent état pour vos locataires.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">6</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Sécurisation des Transactions</div>
                        <p>Toutes les transactions sont protégées par des protocoles de sécurité de haut niveau. Vous pouvez être sûr que vos paiements, ainsi que les informations personnelles et bancaires de vos locataires, sont traités en toute sécurité.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section pour les agences immobilières -->
    <div class="target-section">
        <h2 class="target-header">Pour les Agences Immobilières : Gérez Votre Portefeuille avec une Solution Complète et Performante</h2>
        <div class="target-content">
            <p>En tant qu'agence immobilière, vous êtes confronté à la gestion de multiples biens et locataires, ce qui peut vite devenir un défi sans les bons outils. <strong>MAELYS-IMO</strong> révolutionne la gestion immobilière en vous offrant une plateforme centralisée qui vous permet de gérer efficacement tous vos biens, locataires et transactions, à partir d'un seul endroit.</p>
            
            <div class="benefit-list">
                <div class="benefit-item">
                    <div class="benefit-number">1</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Gestion Multi-Biens Centralisée</div>
                        <p>Notre plateforme vous permet de gérer un grand nombre de biens immobiliers de manière centralisée. Publiez des annonces, gérez les contrats, suivez les paiements et gérez les demandes des locataires, le tout à partir d'un tableau de bord unique, accessible en temps réel.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">2</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Recouvrement des Loyers Automatisé et Suivi des Paiements</div>
                        <p>Pour les agences, le recouvrement des loyers est simplifié grâce à notre système intégré. Les locataires peuvent payer leurs loyers directement via notre application, que ce soit par Mobile Money ou en espèces. En cas de non-paiement, la plateforme active automatiquement un processus de recouvrement pour garantir la réception des fonds.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">3</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Suivi des Contrats et Documents Juridiques</div>
                        <p>Gérez tous les contrats de location de manière numérique. Notre plateforme vous permet de suivre chaque contrat, d'effectuer des renouvellements, et de gérer les documents légaux associés (avis d'expulsion, caution, etc.). De plus, nous mettons à votre disposition des outils pour rédiger des contrats conformes aux normes locales.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">4</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Rapports Détailés et Analyses</div>
                        <p>Les agences immobilières bénéficient de rapports détaillés qui vous permettent de suivre l'état de votre portefeuille immobilier. Vous avez accès à des statistiques sur la performance de vos biens, le taux d'occupation, les loyers collectés, ainsi que les dépenses liées à l'entretien.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">5</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Optimisation des Transactions et des Relations Locataires</div>
                        <p>Grâce à notre interface de communication intégrée, vous pouvez interagir facilement avec vos locataires. De plus, les outils de gestion de maintenance et d'entretien vous permettent de répondre rapidement aux besoins des locataires, renforçant ainsi leur satisfaction et la fidélité à votre agence.</p>
                    </div>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-number">6</div>
                    <div class="benefit-text">
                        <div class="benefit-title">Sécurisation et Conformité Légale</div>
                        <p>Nous garantissons que toutes les transactions effectuées sur la plateforme sont sécurisées et conformes aux réglementations locales. Vous pouvez être sûr que vos informations et celles de vos clients sont protégées grâce à des technologies de sécurité avancées.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="platform-section">
        <h2 class="platform-title" style="color:white">Maelys - <span style="color: orangered">Imo</span> propose</h2>
        <div class="platform-features">
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Enregistrement des biens immobiliers (appartements, maisons, bureaux, etc.)</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Création et gestion des profils locataires</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Attribution des biens à un locataire unique</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Encaissement des loyers via Mobile Money ou en espèces</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Gestion des recouvrements par une personne désignée</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Reversement automatique ou manuel des loyers perçus</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Délivrance électronique de quittances de loyer</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Messagerie intégrée pour le suivi des réparations</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Page d'accueil dynamique avec biens disponibles</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Demande de virement bancaire avec preuve</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Alertes automatiques pour loyers impayés</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Base de données des locataires à risques</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Système de commissions intégré pour les agences</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">États de gestion détaillés pour chaque bien</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Application mobile et espace web dédiés</span>
            </div>
            <div class="platform-feature">
                <span class="platform-icon">•</span>
                <span class="platform-text">Plateforme modulable pour indépendants et agences</span>
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
                <p class="step-description">Planifiez une visite avec l'agence/propriéraire</p>
            </div>
            <div class="process-step">
                <div class="step-number">3</div>
                <h3 class="step-title">Dossier</h3>
                <p class="step-description">rassemblez vos documents neccessaire</p>
            </div>
            <div class="process-step">
                <div class="step-number">4</div>
                <h3 class="step-title">Signature</h3>
                <p class="step-description">Signez votre contrat et emménagez</p>
            </div>
        </div>
    </div>
</div>
@endsection