<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrat de Location</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.6;
            margin: 0;
            padding: 20px 40px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .title { 
            font-size: 20px; 
            font-weight: bold; 
            margin-bottom: 15px;
            text-decoration: underline;
        }
        .section { 
            margin-bottom: 25px;
            text-align: justify;
        }
        .signature { 
            margin-top: 60px;
        }
        .footer { 
            margin-top: 50px; 
            font-size: 12px; 
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .parties {
            margin-left: 30px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
        .signature-table td {
            width: 50%;
            padding-top: 50px;
        }
        .underline {
            display: inline-block;
            min-width: 200px;
            border-bottom: 1px solid #000;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE LOCATION</h1>
        <p>Fait à <strong>{{ $agence->commune ?? 'abidjan' }}</strong>, le <strong>{{ $date_creation }}</strong></p>
    </div>

    <div class="section">
        <p><strong>ENTRE LES SOUSSIGNES :</strong></p>
        <div class="parties">
            <p><strong>{{ $agence->name ?? 'Agence non spécifiée' }}</strong>, agence immobilière, représentée par son gérant,</p>
            <p>D'une part,</p>
            <br>
            <p>ET</p>
            <br>
            <p><strong>M./Mme {{ $locataire->name }} {{ $locataire->prenom }}</strong>,</p>
            <p>Né(e) le : <span class="underline"></span></p>
            <p>De nationalité : <span class="underline"></span></p>
            <p>Demeurant à : {{ $locataire->adresse }},</p>
            <p>D'autre part,</p>
        </div>
    </div>

    <div class="section">
        <p><strong>IL A ETE CONVENU ET ARRETE CE QUI SUIT :</strong></p>
    </div>

    <div class="section">
        <h3 class="title">ARTICLE 1 - OBJET</h3>
        <p>Le présent contrat a pour objet la location d'un bien immobilier situé à {{ $bien->commune ?? 'Commune non spécifiée' }}, précisément à {{ $bien->adresse ?? 'Adresse non spécifiée' }}, composé de : {{ $bien->description ?? 'Description non disponible' }}.</p>
        <p>Le bien est loué vide/non meublé (rayer la mention inutile) et en bon état de fonctionnement.</p>
    </div>

    <div class="section">
        <h3 class="title">ARTICLE 2 - DUREE</h3>
        <p>Le présent contrat est conclu pour une durée déterminée de <strong>{{ $duree_mois ?? 'X' }} mois</strong> à compter du <strong>{{ \Carbon\Carbon::parse($contrat->date_debut)->format('d/m/Y') }}</strong> jusqu'au <strong>{{ \Carbon\Carbon::parse($contrat->date_fin)->format('d/m/Y') }}</strong>.</p>
        <p>Il pourra être renouvelé par tacite reconduction pour des périodes successives d'un an, sauf résiliation par l'une ou l'autre des parties avec un préavis de trois mois.</p>
    </div>

    <div class="section">
        <h3 class="title">ARTICLE 3 - LOYER ET CHARGES</h3>
        <p>Le loyer mensuel est fixé à la somme de <strong>{{ number_format($contrat->loyer_mensuel, 0, ',', ' ') }} /Mois ({{ $montant_lettres ?? 'Montant en lettres' }})</strong>, payable d'avance le <strong>{{ $bien->date_fixe ?? '5' }} de chaque mois</strong>.</p>
        <p>Un dépôt de garantie (caution) de <strong>{{ number_format($contrat->caution, 0, ',', ' ') }} MOIS</strong> a été versé par le locataire.</p>
        <p>Une avance sur loyer de <strong>{{ number_format($contrat->avance, 0, ',', ' ') }} MOIS</strong> a été versée par le locataire.</p>
        <p>Le total des versements effectués s'élève donc à <strong>{{ number_format($bien->montant_total, 0, ',', ' ') }} FCFA</strong>.</p>
    </div>

    <div class="section">
        <h3 class="title">ARTICLE 4 - OBLIGATIONS DES PARTIES</h3>
        <p><strong>4.1 Obligations du locataire :</strong></p>
        <ul>
            <li>Payer le loyer en temps et en heure</li>
            <li>Utiliser le bien conformément à sa destination</li>
            <li>Effectuer les petites réparations d'entretien</li>
            <li>Ne pas effectuer de modifications sans accord écrit</li>
        </ul>
        
        <p><strong>4.2 Obligations du bailleur :</strong></p>
        <ul>
            <li>Livrer le bien en bon état</li>
            <li>Effectuer les grosses réparations</li>
            <li>Respecter la vie privée du locataire</li>
        </ul>
    </div>

    <div class="section">
        <h3 class="title">ARTICLE 5 - RESILIATION</h3>
        <p>En cas de résiliation anticipée, un préavis de trois mois est exigé. Le locataire perd le bénéfice du dépôt de garantie en cas de départ avant le terme du contrat.</p>
    </div>

    <div class="signature">
        <p>Fait à {{ $locataire->agence->commune ?? 'Abidjan' }}, le {{ $contrat->created_at->format('d/m/Y') }}</span></p>
        <p>En deux exemplaires originaux, chacun des parties reconnaissant avoir reçu le sien.</p>
        
        <table class="signature-table">
            <tr>
                <td align="center">
                    <p><strong>LE BAILLEUR</strong></p>
                    <p>Signature précédée de la mention "Lu et approuvé"</p>
                    <br><br>
                    <p>Agence : {{ $locataire->agence->name ?? 'Maelys-Imo' }}</p>
                </td>
                <td align="center">
                    <p><strong>LE LOCATAIRE</strong></p>
                    <p>Signature précédée de la mention "Lu et approuvé"</p>
                    <br><br>
                    <p>Nom et prénom : {{ $locataire->name }} {{ $locataire->prenom }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Document généré automatiquement par <strong>{{ $agence->name ?? 'Agence' }}</strong> - {{ $agence->contact ?? 'Contact non disponible' }} - {{ $agence->email ?? '' }}</p>
    </div>
</body>
</html>