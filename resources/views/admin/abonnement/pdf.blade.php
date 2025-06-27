<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrat d'Abonnement Propriétaire #{{ $abonnement->id }}</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 12pt;
            background-color: #fff;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #02245b;
        }
        .logo { 
            height: 80px;
            margin-bottom: 15px;
        }
        .title { 
            color: #02245b; 
            font-size: 20pt;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .subtitle { 
            color: #666; 
            font-size: 12pt;
            font-weight: 400;
        }
        .section { 
            margin-bottom: 30px;
        }
        .section-title {
            color: #02245b;
            font-size: 14pt;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e6ed;
            text-transform: uppercase;
        }
        .contract-clause {
            margin-bottom: 20px;
            text-align: justify;
            padding-left: 10px;
            border-left: 3px solid #f0f0f0;
        }
        .clause-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: #02245b;
        }
        .clause-number {
            font-weight: 600;
            margin-right: 5px;
        }
        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            border-top: 1px solid #333;
            padding-top: 15px;
            text-align: center;
            font-size: 10pt;
            margin-top: 40px;
        }
        .footer { 
            margin-top: 50px; 
            padding-top: 15px;
            text-align: center; 
            font-size: 10pt;
            color: #666;
            border-top: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #e0e6ed;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #02245b;
        }
        .important {
            font-weight: 600;
            color: #02245b;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-active {
            background-color: #e6ffed;
            color: #2d9737;
            border: 1px solid #2d9737;
        }
        .badge-expired {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }
        .company-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #02245b;
        }
        .client-info {
            background-color: #f5f9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3a7bd5;
        }
        .preamble {
            font-style: italic;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Contrat d'Abonnement pour les Propriétaires de Biens</div>
        <div class="subtitle">Référence : MAE-ABN-{{ str_pad($abonnement->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="section">
        <div class="section-title">Entre les soussignés</div>
        
        <div class="client-info">
            <p><strong>{{ $abonneName }}</strong>,</p>
            <p>Adresse : {{ $abonnement->proprietaire->adresse ?? 'N/A' }}</p>
            <p>Numéro d'identification : {{ $abonnement->proprietaire->code_id ?? 'N/A' }}</p>
            <p>(ci-après dénommé "Le Propriétaire")</p>
        </div>
        
        <p style="text-align: center; font-weight: 600; margin: 20px 0;">ET</p>
        
        <div class="company-info">
            <p><strong>Maelys Immobilier</strong>,</p>
            <p>Adresse : Cocody - Angré 8ème tranche - Abidjan</p>
            <p>Représentée par M. KADIO KOUAME SERGE ARISTIDE AUGUSTE</p>
            <p>(ci-après dénommée "La Plateforme")</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Préambule</div>
        <div class="preamble">
            La Plateforme Maelys Immobilier est une solution complète de gestion locative, permettant aux propriétaires de biens immobiliers de gérer la mise en location de leurs propriétés, de collecter les loyers, de gérer les impayés, et d'assurer un suivi administratif et juridique. La plateforme propose également une application mobile pour les locataires permettant le paiement des loyers via Mobile Money ou en espèces auprès du propriétaire ou de tout agent de recouvrement mandaté.
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 1 - Objet du Contrat</div>
        <div class="contract-clause">
            Le présent contrat a pour objet de définir les modalités et conditions selon lesquelles le Propriétaire souscrit à l'abonnement de gestion locative proposé par la Plateforme Maelys Immobilier pour la gestion de son bien immobilier, incluant la collecte des loyers, le suivi des paiements, et la gestion des impayés.
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 2 - Durée de l'Abonnement</div>
        <div class="contract-clause">
            Le présent contrat est conclu pour une durée initiale de <span class="important">12 mois</span> à compter de la date de signature. Il est renouvelable tacitement pour des périodes successives de <span class="important">12 mois</span>, sauf dénonciation par l'une des parties, par lettre recommandée avec accusé de réception, au moins <span class="important">3 mois avant</span> la fin de la période en cours.
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 3 - Obligations de la Plateforme</div>
        
        <div class="contract-clause">
            <div class="clause-title">3.1</div>
            <p>Mettre à disposition du Propriétaire une interface de gestion de son bien immobilier, permettant la publication, la gestion des contrats de location, la collecte des loyers et le suivi des impayés.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">3.2</div>
            <p>Assurer la gestion des paiements des loyers par les locataires via l'application mobile, incluant les options de paiement par Mobile Money ou en espèces.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">3.3</div>
            <p>Proposer des services de recouvrement en cas de non-paiement du loyer, y compris la possibilité d'affecter un agent de recouvrement mandaté par le Propriétaire.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">3.4</div>
            <p>Fournir un support technique et juridique pour résoudre tout litige relatif à la gestion locative ou au recouvrement des loyers impayés.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 4 - Obligations du Propriétaire</div>
        
        <div class="contract-clause">
            <div class="clause-title">4.1</div>
            <p>Fournir des informations exactes sur le bien immobilier (adresse, surface, type de location, montant du loyer, etc.) et les locataires.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">4.2</div>
            <p>Mettre à jour les informations relatives au bien et à la location dans l'interface de la Plateforme en temps réel.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">4.3</div>
            <p>Payer la cotisation d'abonnement à la Plateforme selon le tarif défini dans les conditions financières.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">4.4</div>
            <p>Assurer le bon entretien du bien immobilier et respecter toutes les obligations légales liées à la location, notamment en matière de sécurité, de salubrité et d'hygiène.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">4.5</div>
            <p>Assurer une réponse rapide aux demandes de la Plateforme et aux réclamations des locataires.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 5 - Gestion des Loyers et Paiements</div>
        
        <div class="contract-clause">
            <div class="clause-title">5.1</div>
            <p>Les paiements des loyers sont effectués par les locataires via l'application mobile de Maelys Immobilier, par Mobile Money ou en espèces auprès du Propriétaire ou de tout agent de recouvrement désigné par celui-ci.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">5.2</div>
            <p>La Plateforme procédera au transfert des loyers collectés au Propriétaire selon la périodicité choisie (mensuelle, trimestrielle, etc.), déduction faite des frais de gestion éventuels.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">5.3</div>
            <p>En cas d'impayé, la Plateforme s'engage à mettre en œuvre les actions nécessaires pour recouvrer les loyers dus, incluant l'activation d'un agent de recouvrement mandaté par le Propriétaire.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 6 - Conditions Financières</div>
        
        <table>
            <tr>
                <th width="30%">Type d'abonnement</th>
                <td>
                    <span class="important">ABONNEMENT PROPRIÉTAIRE</span>
                </td>
            </tr>
            <tr>
                <th>Période</th>
                <td>
                    Du <strong>{{ \Carbon\Carbon::parse($abonnement->date_debut)->translatedFormat('d/m/Y') }}</strong><br>
                    Au <strong>{{ \Carbon\Carbon::parse($abonnement->date_fin)->translatedFormat('d/m/Y') }}</strong><br>
                    <em>({{ $joursRestants > 0 ? $joursRestants.' jours restants' : 'Expiré' }})</em>
                </td>
            </tr>
            <tr>
                <th>Montant</th>
                <td class="important">{{ number_format($abonnement->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <th>Mode de paiement</th>
                <td>{{ $abonnement->mode_paiement }}</td>
            </tr>
            <tr>
                <th>Référence paiement</th>
                <td>{{ $abonnement->reference_paiement ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Statut</th>
                <td>
                    @if($abonnement->statut == 'actif')
                        <span class="badge badge-active">ACTIF</span>
                    @else
                        <span class="badge badge-expired">EXPIRÉ</span>
                    @endif
                </td>
            </tr>
        </table>
        
        <div class="contract-clause">
            <div class="clause-title">6.2</div>
            <p>Des frais supplémentaires peuvent être appliqués pour des services spécifiques (par exemple, la gestion de recouvrement ou la rédaction de documents juridiques). Ces frais seront définis dans un avenant au contrat.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">6.3</div>
            <p>Le paiement de l'abonnement se fait par virement bancaire, Mobile Money, ou tout autre moyen convenu entre les parties.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 7 - Responsabilité</div>
        
        <div class="contract-clause">
            <div class="clause-title">7.1</div>
            <p>La Plateforme ne pourra être tenue responsable des défauts de paiement des locataires, à moins que ces défauts soient directement liés à une défaillance de la Plateforme dans la gestion des paiements ou des données.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">7.2</div>
            <p>Le Propriétaire reste responsable de l'état du bien immobilier, de sa conformité aux normes légales, et de la gestion des relations avec les locataires.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 8 - Confidentialité</div>
        <div class="contract-clause">
            Les parties s'engagent à préserver la confidentialité des informations échangées dans le cadre du présent contrat, notamment les données personnelles des locataires et des propriétaires, conformément à la législation en vigueur.
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 9 - Résiliation du Contrat</div>
        
        <div class="contract-clause">
            <div class="clause-title">9.1</div>
            <p>Non-paiement des abonnements par le Propriétaire après un délai de <span class="important">30 jours</span>.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">9.2</div>
            <p>Manquement grave aux obligations prévues par le contrat.</p>
        </div>
        
        <div class="contract-clause">
            <div class="clause-title">9.3</div>
            <p>Résiliation sur demande du Propriétaire ou de la Plateforme avec un préavis de <span class="important">3 mois</span>.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Article 10 - Litiges</div>
        <div class="contract-clause">
            Tout litige relatif à l'exécution ou à l'interprétation du présent contrat sera soumis à la juridiction des tribunaux compétents du lieu du siège de la Plateforme.
        </div>
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <p> Pour  MAELYS-IMO 
                <br>M. KADIO KOUAME SERGE
                <br>Directeur de KKS-TECHNOLOGIES</p>
            <p>Fait à Abidjan, le {{ now()->translatedFormat('d F Y') }}</p>
            <p>Signature et cachet</p>
            <p><strong>MAELYS IMMOBILIER</strong></p>
        </div>
        
        <div class="signature-box">
            <p>Pour M./ Mme {{ $abonneName }} </p>
            <p>Fait à Abidjan, le {{ \Carbon\Carbon::parse($abonnement->created_at)->translatedFormat('d F Y') }}</p>
            <p>Signature</p>
            <p><strong>{{ $abonneName }}</strong></p>
        </div>
    </div>

    <div class="footer">
        <p>Document généré électroniquement le {{ now()->translatedFormat('d F Y \à H\hi') }}</p>
        <p>Maelys Immobilier - contact@maelys-imo.com</p>
        <p>© {{ date('Y') }} Tous droits réservés</p>
    </div>
</body>
</html>