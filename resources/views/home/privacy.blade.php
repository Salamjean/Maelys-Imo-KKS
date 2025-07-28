@extends('home.pages.layouts.template')
@section('content')
<style>
    /* Styles de base */
    .privacy-container {
        max-width: 80%;
        margin: 0 auto;
        padding: 20px;
        font-family: Arial, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    
    .privacy-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #02245b;
        grid-column: 1 / -1; /* Prend toute la largeur */
    }
    
    .privacy-header h1 {
        color: #02245b;
        font-size: 28px;
        margin-bottom: 10px;
    }
    
    .update-date {
        color: #666;
        font-style: italic;
    }
    
    .privacy-content {
        display: grid;
        grid-template-columns: 1fr 1fr; /* Deux colonnes égales */
        gap: 30px; /* Espace entre les colonnes */
    }
    
    .privacy-column {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .privacy-section {
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .privacy-section:last-child {
        border-bottom: none;
    }
    
    .privacy-section h2 {
        color: #02245b;
        font-size: 22px;
        margin-bottom: 15px;
        padding-left: 10px;
        border-left: 4px solid #ff5e14;
    }
    
    .privacy-section p {
        margin-bottom: 15px;
        color: #444;
    }
    
    .privacy-list {
        padding-left: 20px;
        margin-bottom: 15px;
    }
    
    .privacy-list li {
        margin-bottom: 8px;
        color: #444;
    }
    
    .contact-link {
        color: #ff5e14;
        text-decoration: none;
        font-weight: bold;
    }
    
    .contact-link:hover {
        text-decoration: underline;
    }

    /* Responsive pour mobiles */
    @media (max-width: 768px) {
        .privacy-content {
            grid-template-columns: 1fr; /* Une seule colonne */
        }
        
        .privacy-container {
            max-width: 100%;
            padding: 15px;
        }
    }
</style>

<div class="privacy-container">
    <div class="privacy-header">
        <h1>Politique de Confidentialité</h1>
        <p class="update-date">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>
    
    <div class="privacy-content">
        <!-- Première colonne -->
        <div class="privacy-column">
            <section class="privacy-section">
                <h2>1. Introduction</h2>
                <p>
                    Maelys-IMO ("nous", "notre", "nos") s'engage à protéger la vie privée de ses utilisateurs. Cette politique explique comment nous collectons, utilisons, partageons et protégeons vos informations personnelles lorsque vous utilisez notre plateforme de gestion immobilière.
                </p>
            </section>
            
            <section class="privacy-section">
                <h2>2. Données que nous collectons</h2>
                <p>Nous pouvons collecter :</p>
                <ul class="privacy-list">
                    <li>Informations d'identification (nom, prénom, adresse email)</li>
                    <li>Coordonnées professionnelles (téléphone, adresse postale)</li>
                    <li>Informations sur vos propriétés immobilières</li>
                    <li>Données de paiement (traitée sécurisé par nos prestataires)</li>
                </ul>
            </section>
            
            <section class="privacy-section">
                <h2>3. Utilisation des données</h2>
                <p>Nous utilisons vos données pour :</p>
                <ul class="privacy-list">
                    <li>Fournir et maintenir nos services</li>
                    <li>Personnaliser votre expérience utilisateur</li>
                    <li>Améliorer notre plateforme</li>
                    <li>Communiquer avec vous (support, mises à jour)</li>
                </ul>
            </section>
            
            <section class="privacy-section">
                <h2>4. Partage des données</h2>
                <p>
                    Nous ne vendons pas vos données personnelles. Nous pouvons les partager avec :
                </p>
                <ul class="privacy-list">
                    <li>Prestataires de services (hébergement, paiement, analyse)</li>
                    <li>Autorités légales si requis par la loi</li>
                </ul>
            </section>
        </div>
        
        <!-- Deuxième colonne -->
        <div class="privacy-column">
            <section class="privacy-section">
                <h2>5. Protection des données</h2>
                <p>
                    Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données contre tout accès non autorisé, altération ou destruction.
                </p>
                <p>
                    Cela inclut le chiffrement, le contrôle d'accès et des audits de sécurité réguliers.
                </p>
            </section>
            
            <section class="privacy-section">
                <h2>6. Vos droits</h2>
                <p>Conformément au RGPD, vous avez le droit de :</p>
                <ul class="privacy-list">
                    <li>Accéder à vos données personnelles</li>
                    <li>Demander leur rectification</li>
                    <li>Demander leur suppression</li>
                    <li>Limiter leur traitement</li>
                    <li>Vous opposer à leur traitement</li>
                    <li>Demander la portabilité de vos données</li>
                </ul>
                <p>
                    Pour exercer ces droits, contactez-nous à <a href="mailto:contact@maelysimo.com" class="contact-link">contact@maelysimo.com</a>.
                </p>
            </section>
            
            <section class="privacy-section">
                <h2>7. Cookies</h2>
                <p>
                    Nous utilisons des cookies pour améliorer votre expérience. Vous pouvez les gérer via les paramètres de votre navigateur.
                </p>
            </section>
            
            <section class="privacy-section">
                <h2>8. Modifications</h2>
                <p>
                    Nous pouvons mettre à jour cette politique occasionnellement. Nous vous informerons des changements significatifs par email ou via notre plateforme.
                </p>
            </section>
            
            <section class="privacy-section">
                <h2>9. Nous contacter</h2>
                <p>
                    Pour toute question concernant cette politique :<br><br>
                    Email : <a href="mailto:contact@maelysimo.com" class="contact-link">contact@maelysimo.com</a><br>
                </p>
            </section>
        </div>
    </div>
</div>
@endsection