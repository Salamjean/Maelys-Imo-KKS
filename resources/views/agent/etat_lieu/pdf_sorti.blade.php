<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État des lieux - {{ $etatLieuSorti->locataire->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 5px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 18px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            margin-bottom: 15px;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .section-title:before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 20px;
            background-color: #3498db;
            margin-right: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        table thead {
            background-color: #3498db;
            color: white;
        }
        
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
        }
        
        table td {
            padding: 10px 15px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table tr:hover {
            background-color: #f1f9ff;
        }
        
        .observation-cell {
            white-space: pre-wrap;
            line-height: 1.4;
        }
        
        .bad-state {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .good-state {
            color: #27ae60;
            font-weight: bold;
        }
        
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .signature-box {
            width: 45%;
            margin-bottom: 30px;
        }
        
        .signature-line {
            border-top: 1px solid #7f8c8d;
            margin-top: 60px;
            padding-top: 10px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #ecf0f1;
            padding-top: 15px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-good {
            background-color: #d5f5e3;
            color: #27ae60;
        }
        
        .badge-bad {
            background-color: #fadbd8;
            color: #e74c3c;
        }
        
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 4px 4px 0;
        }
        
        .info-box-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .signature {
            page-break-inside: avoid; /* Pour éviter que les signatures ne soient coupées entre deux pages */
            margin-top: 100px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Vous pouvez ajouter un logo ici si nécessaire -->
            <!-- <img src="logo.png" alt="Logo" class="logo"> -->
            <div class="title">ÉTAT DES LIEUX - SORTIE</div>
            <div class="subtitle">Fait le {{ $etatLieuSorti->created_at->format('d/m/Y') }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-box-title">Informations générales</div>
            <table>
                <tr>
                    <td width="30%"><strong>Locataire:</strong></td>
                    <td>{{ $etatLieuSorti->locataire->name }} {{ $etatLieuSorti->locataire->prenom }}</td>
                </tr>
                <tr>
                    <td><strong>Contact:</strong></td>
                    <td>{{ $etatLieuSorti->locataire->contact }}</td>
                </tr>
                <tr>
                    <td><strong>Bien:</strong></td>
                    <td>
                        {{ $etatLieuSorti->type_bien }}<br>
                        {{ $etatLieuSorti->bien->adresse ?? '' }}<br>
                        {{ $etatLieuSorti->bien->ville ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Présence des parties:</strong></td>
                    <td>{{ ucfirst($etatLieuSorti->presence_partie) }}</td>
                </tr>
                <tr>
                    <td><strong>Nombre de clés remises:</strong></td>
                    <td>{{ $etatLieuSorti->nombre_cle }}</td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <div class="section-title">PARTIES COMMUNES</div>
            <table>
                <thead>
                    <tr>
                        <th width="25%">Élément</th>
                        <th width="15%">État</th>
                        <th width="60%">Observations</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SOL</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['sol']) && $etatLieuSorti->parties_communes['sol'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['sol'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_sol'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>MURS</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['murs']) && $etatLieuSorti->parties_communes['murs'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['murs'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_murs'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PLAFONDS</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['plafond']) && $etatLieuSorti->parties_communes['plafond'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['plafond'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_plafond'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PORTES</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['porte_entre']) && $etatLieuSorti->parties_communes['porte_entre'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['porte_entre'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_porte_entre'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ELECTRICITE</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['interrupteur']) && $etatLieuSorti->parties_communes['interrupteur'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['interrupteur'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_interrupteur'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ROBINETTERIE</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['robinet']) && $etatLieuSorti->parties_communes['robinet'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['robinet'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_robinet'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>EVIER INOX DE LAVABO</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['lavabo']) && $etatLieuSorti->parties_communes['lavabo'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['lavabo'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_lavabo'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>DOUCHE ET SDB</td>
                        <td>
                            <span class="badge {{ isset($etatLieuSorti->parties_communes['douche']) && $etatLieuSorti->parties_communes['douche'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieuSorti->parties_communes['douche'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieuSorti->parties_communes['observation_douche'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @foreach($etatLieuSorti->chambres as $index => $chambre)
        <div class="section">
            <div class="section-title">CHAMBRE {{ $index + 1 }}</div>
            <table>
                <thead>
                    <tr>
                        <th width="25%">Élément</th>
                        <th width="15%">État</th>
                        <th width="60%">Observations</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>SOL</td>
                        <td>
                            <span class="badge {{ isset($chambre['sol']) && $chambre['sol'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['sol'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_sol'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>MURS</td>
                        <td>
                            <span class="badge {{ isset($chambre['murs']) && $chambre['murs'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['murs'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_murs'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PLAFONDS</td>
                        <td>
                            <span class="badge {{ isset($chambre['plafond']) && $chambre['plafond'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['plafond'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_plafond'] ?? '-' }}</td>
                    </tr>
                    {{-- <tr>
                        <td>Portes</td>
                        <td>
                            <span class="badge {{ isset($chambre['porte']) && $chambre['porte'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['porte'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_porte'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Fenêtres</td>
                        <td>
                            <span class="badge {{ isset($chambre['fenetre']) && $chambre['fenetre'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['fenetre'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_fenetre'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Interrupteurs</td>
                        <td>
                            <span class="badge {{ isset($chambre['interrupteur']) && $chambre['interrupteur'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['interrupteur'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_interrupteur'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Prises électriques</td>
                        <td>
                            <span class="badge {{ isset($chambre['prise']) && $chambre['prise'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($chambre['prise'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $chambre['observation_prise'] ?? '-' }}</td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
        @endforeach
        
         <div class="signature">
            <div style="width: 100%; display: flex; justify-content: space-between; margin-top: 80px;">
                <!-- Signature locataire -->
                <div style="width: 45%; text-align: center;">
                    <p style="margin-bottom: 60px;">Signature du locataire :</p>
                    <p style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">
                        {{ $etatLieuSorti->locataire->name }} {{ $etatLieuSorti->locataire->prenom }}
                    </p>
                    <p style="margin-top: 10px;">Date : {{ $etatLieuSorti->created_at->format('d/m/Y') }}</p>
                </div>
                
                <!-- Signature propriétaire/gestionnaire -->
                <div style="width: 45%; text-align: center;">
                    <p style="margin-bottom: 60px;">Signature du propriétaire/gestionnaire :</p>
                    <p style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">
                        {{ Auth::user()->name }}
                    </p>
                    <p style="margin-top: 10px;">Date : {{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            Document généré le {{ date('d/m/Y H:i') }} - © Côte d'ivoire
        </div>
    </div>
</body>
</html>