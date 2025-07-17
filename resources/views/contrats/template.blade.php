<!DOCTYPE html>
<html>
<head>
    <title>Bail à Usage d'Habitation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.5; font-size: 14px; }
        .header { margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .ministere { font-weight: bold; text-transform: uppercase; }
        .republique { font-size: 16px; font-weight: bold; text-align: right; }
        .devise { font-style: italic; margin-bottom: 10px; }
        .numero { text-align: right; margin-bottom: 20px; margin-top: 10px; }
        .titre-contract { text-align: center; font-weight: bold; font-size: 18px; text-decoration: underline; margin: 20px 0; }
        .parties { margin: 30px 0; }
        .partie-title { font-weight: bold; text-decoration: underline; }
        .signature { margin-top: 50px; }
        .footer { margin-top: 30px; font-size: 12px; text-align: center; }
        .underline { border-bottom: 1px solid #000; display: inline-block; min-width: 300px; }
        .page-break { page-break-after: always; }
        .header img { max-width: 100px; margin: 0 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="ministere">MINISTÈRE DE LA CONSTRUCTION <br> DU LOGEMENT ET DE L'URBANISME</div>
        <img src="public/assets/images/embleme.png" alt="Logo" />
        <div class="republique">RÉPUBLIQUE DE CÔTE D'IVOIRE <br> <div class="devise">Union - Discipline - Travail</div> <br><div class="numero">N°A </span></div></div>
    </div>

    <div class="titre-contract">BAIL À USAGE D'HABITATION</div>
    
    <div>
        <strong>Texte de référence :</strong><br>
        Loi N°2015-57 du 26 juin 2023 Instituant Code de la Construction et de l'Habitat
    </div>

    <div class="parties">
        <div class="partie-title">ENTRE</div>
        
        <p><strong>LE PROPRIÉTAIRE :</strong></p>
        <p>
            Référence identité (CNI-RCCM) N° : <span class="underline"></span> établie le <span class="underline"></span><br>
            Domicile ou siège social : <span class="underline"></span><br>
            Tél : <span class="underline"></span> BP : <span class="underline"></span><br>
            Email : <span class="underline"></span><br>
            Compte contribuable n° : <span class="underline"></span>
        </p>
        <p>Dénommé au titre du présent acte « LE BAILLEUR » ou « LE PROPRIÉTAIRE »</p>
        <p><strong>D'une part</strong></p>

        <p><strong>ET</strong></p>
        
        <p><strong>LE LOCATAIRE :</strong></p>
        <p>
            Référence identité (CNI) N° : <span class="underline">{{ $locataire['cni'] ?? '______' }}</span> établie le <span class="underline">{{ $locataire['cni_date'] ?? '______' }}</span><br>
            Profession : <span class="underline">{{ $locataire['profession'] }}</span><br>
            Tél : <span class="underline">{{ $locataire['contact'] }}</span><br>
            BP : <span class="underline">{{ $locataire['bp'] ?? '______' }}</span><br>
            Email : <span class="underline">{{ $locataire['email'] }}</span>
        </p>
        <p>Dénommé au titre du présent acte « LE PRENEUR » ou « LE LOCATAIRE »</p>
        <p><strong>D'autre part</strong></p>
    </div>

    <div>
        <p><strong>LESQUELS ont convenu et arrêté le contrat qui suit :</strong></p>
        
        <div class="titre-contract">BAIL</div>
        
        <p>
            Le BAILLEUR donne à bail à titre d'habitation, pour une durée, sous les conditions et le prix ci-après indiqués au PRENEUR qui accepte, les biens immobiliers dont la désignation suit :
        </p>
        
        <div class="titre-contract">DÉSIGNATION</div>
        
        <p>
            <strong>Adresse du bien :</strong> {{ $bien->adresse }}<br>
            <strong>Type :</strong> {{ $bien->type }}<br>
            <strong>Description :</strong> {{ $bien->description }}<br>
            <strong>Superficie :</strong> {{ $bien->superficie ?? '______' }} m²<br>
            <strong>Composition :</strong> {{ $bien->composition ?? '______' }}
        </p>
        
        <p>
            Le PRENEUR déclare connaître parfaitement le bien loué ou l'avoir vu et visité en vue du présent bail.
        </p>
    </div>

    <div class="page-break"></div>

    <div class="titre-contract">COMPOSITION DU CONTRAT</div>
    
    <ol>
        <li>Contrat de location</li>
        <li>Conclusion du bail et fixation du loyer</li>
        <li>Obligations des parties</li>
        <li>Fin du bail</li>
        <li>Copropriété - Élection de domicile</li>
        <li>Etat des lieux contradictoire</li>
    </ol>

    <div class="titre-contract">ÉTAT DES LIEUX</div>
    
    <p>
        Le PRENEUR prend les lieux loués dans l'état où ils se trouvent lors de l'entrée en jouissance et les rendra en fin de bail tels qu'il les aura reçus suivant l'état des lieux dressé par les parties.
    </p>
    
    <p>
        Un état des lieux contradictoire intermédiaire pourra être réalisé en cours d'exécution du contrat, afin de permettre au BAILLEUR de s'assurer que le LOCATAIRE jouit des lieux en bon père de famille. Dans le cas contraire, le BAILLEUR pourra mettre le LOCATAIRE en demeure de procéder aux réparations que le dépôt de garantie ne pourrait couvrir.
    </p>
    
    <p>
        À l'expiration du bail, un état des lieux contradictoire, en présence des parties, est également fait par les parties ou leurs représentants dûment mandatés. Le PRENEUR veillera à la remise des lieux dans leur état primitif (agencement, enduit, peinture intérieure, etc.).
    </p>

    <div class="signature">
        <p>Fait en deux exemplaires originaux,</p>
        <p>Le {{ now()->format('d/m/Y') }} à Marcory</p>
        
        <table width="100%" style="margin-top: 50px;">
            <tr>
                <td width="50%" style="text-align: center;">
                    <p>Pour le BAILLEUR,</p>
                    <p><span class="underline"></span></p>
                    <p>Signature</p>
                </td>
                <td width="50%" style="text-align: center;">
                    <p>Le PRENEUR,</p>
                    <p><span class="underline">{{ $locataire['prenom'] }} {{ $locataire['nom'] }}</span></p>
                    <p>Signature</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Contrat généré automatiquement par le système de gestion 
    </div>
</body>
</html>