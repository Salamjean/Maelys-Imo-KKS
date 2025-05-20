@extends('home.pages.layouts.template')
@section('content')
<style>
    .about-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: 'Arial', sans-serif;
        color: #333;
        line-height: 1.6;
    }
    
    .page-title {
        text-align: center;
        font-size: 36px;
        color: #02245b;
        margin-bottom: 50px;
        position: relative;
        padding-bottom: 15px;
    }
    
    .page-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: #02245b;
    }
    
    .about-section {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 60px;
    }
    
    .about-image {
        flex: 1;
        min-width: 300px;
        padding: 20px;
    }
    
    .about-image img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .about-content {
        flex: 1;
        min-width: 300px;
        padding: 20px;
    }
    
    .section-title {
        font-size: 28px;
        color: #02245b;
        margin-bottom: 20px;
    }
    
    .mission-vision {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 60px;
    }
    
    .mission-box, .vision-box {
        flex: 1;
        min-width: 300px;
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .box-title {
        font-size: 22px;
        color: #02245b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
    }
    
    .box-title i {
        margin-right: 10px;
        font-size: 24px;
    }
    
    .team-section {
        margin-bottom: 60px;
    }
    
    .team-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
    }
    
    .team-member {
        width: 200px;
        text-align: center;
    }
    
    .member-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 15px;
        border: 3px solid #02245b;
    }
    
    .member-name {
        font-weight: bold;
        margin-bottom: 5px;
        color: #02245b;
    }
    
    .member-position {
        color: #7f8c8d;
        font-size: 14px;
    }
    
    .stats-section {
        background: #02245b;
        color: white;
        padding: 50px 20px;
        margin-bottom: 60px;
        border-radius: 8px;
    }
    
    .stats-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        text-align: center;
    }
    
    .stat-item {
        margin: 20px;
    }
    
    .stat-number {
        font-size: 42px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 16px;
    }
    
    .values-section {
        margin-bottom: 60px;
    }
    
    .values-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    
    .value-item {
        flex: 1;
        min-width: 200px;
        max-width: 300px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        text-align: center;
    }
    
    .value-icon {
        font-size: 40px;
        color: #02245b;
        margin-bottom: 15px;
    }
    
    @media (max-width: 768px) {
        .about-section {
            flex-direction: column;
        }
        
        .mission-box, .vision-box {
            min-width: 100%;
        }
    }
</style>

<div class="about-container">
    <h1 class="page-title">À Propos de Notre Agence</h1>
    
    <div class="about-section">
        <div class="about-image">
            <img src="{{ asset('assets/images/appart.jpg') }}" alt="Image de l'equipe">
        </div>
        <div class="about-content">
            <h2 class="section-title">Notre Histoire</h2>
            <p>Fondée en 2022, notre agence immobilière s'est rapidement imposée comme un acteur clé du marché locatif dans la région. Ce qui a commencé comme une petite équipe passionnée est devenu une référence pour les propriétaires et locataires recherchant des solutions immobilières de qualité.</p>
            <p>Au fil des années, nous avons développé une expertise approfondie du marché local et construit un réseau de partenaires fiables, nous permettant d'offrir un service complet et personnalisé à nos clients.</p>
        </div>
    </div>
    
    <div class="mission-vision">
        <div class="mission-box">
            <h3 class="box-title"><i>✓</i> Notre Mission</h3>
            <p>Simplifier l'accès au logement en offrant des solutions immobilières adaptées à chaque besoin. Nous nous engageons à fournir un service transparent, professionnel et humain, en plaçant la satisfaction de nos clients au cœur de nos préoccupations.</p>
        </div>
        <div class="vision-box">
            <h3 class="box-title"><i>👁️</i> Notre Vision</h3>
            <p>Devenir le partenaire immobilier privilégié de notre région en développant des relations durables avec nos clients. Nous aspirons à redéfinir les standards du secteur en combinant innovation technologique et expertise humaine.</p>
        </div>
    </div>
    
    <div class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">12+</div>
                <div class="stat-label">Années d'expérience</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Biens gérés</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2000+</div>
                <div class="stat-label">Clients satisfaits</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label">Taux de satisfaction</div>
            </div>
        </div>
    </div>
    
    <div class="team-section">
        <h2 class="section-title" style="text-align: center;">Notre Équipe</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="{{ asset('assets/images/appartement.jpg') }}" alt="Directeur" class="member-photo">
                <div class="member-name">Jean Dupont</div>
                <div class="member-position">Directeur</div>
            </div>
            <div class="team-member">
                <img src="{{ asset('assets/images/maison.jpg') }}" alt="Responsable location" class="member-photo">
                <div class="member-name">Marie Martin</div>
                <div class="member-position">Responsable Location</div>
            </div>
            <div class="team-member">
                <img src="{{ asset('assets/images/terrain.jpg') }}" alt="Conseiller clientèle" class="member-photo">
                <div class="member-name">Pierre Lambert</div>
                <div class="member-position">Conseiller Clientèle</div>
            </div>
            <div class="team-member">
                <img src="{{ asset('assets/images/magasin.jpeg') }}" alt="Gestionnaire de biens" class="member-photo">
                <div class="member-name">Sophie Leroy</div>
                <div class="member-position">Gestionnaire de Biens</div>
            </div>
        </div>
    </div>
    
    <div class="values-section">
        <h2 class="section-title" style="text-align: center;">Nos Valeurs</h2>
        <div class="values-list">
            <div class="value-item">
                <div class="value-icon">✓</div>
                <h3>Intégrité</h3>
                <p>Nous privilégions toujours la transparence et l'honnêteté dans nos relations avec clients et partenaires.</p>
            </div>
            <div class="value-item">
                <div class="value-icon">❤️</div>
                <h3>Engagement</h3>
                <p>Chaque client bénéficie de notre dévouement total et d'un suivi personnalisé.</p>
            </div>
            <div class="value-item">
                <div class="value-icon">🏆</div>
                <h3>Excellence</h3>
                <p>Nous visons l'excellence dans chaque aspect de notre service, des visites aux contrats.</p>
            </div>
            <div class="value-item">
                <div class="value-icon">🌱</div>
                <h3>Innovation</h3>
                <p>Nous adoptons les meilleures technologies pour améliorer continuellement notre service.</p>
            </div>
        </div>
    </div>
</div>
@endsection