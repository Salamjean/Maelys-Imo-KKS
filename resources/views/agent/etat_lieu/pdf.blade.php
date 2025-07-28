<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État des lieux - {{ $etatLieu->locataire->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f7f9fc;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            border-radius: 8px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 4px solid #3498db;
            padding-bottom: 20px;
        }
        
        .title {
            font-size: 26px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
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
            margin-bottom: 40px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 20px;
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            margin-bottom: 15px;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .section-title:before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 25px;
            background-color: #3498db;
            margin-right: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 15px;
        }
        
        table thead {
            background-color: #3498db;
            color: white;
        }
        
        table th {
            padding: 10px 15px;
            text-align: left;
            font-weight: 700;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        table tr:hover {
            background-color: #e1f5fe;
        }
        
        .observation-cell {
            white-space: pre-wrap;
            line-height: 1.5;
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
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 14px;
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
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 0 4px 4px 0;
        }
        
        .info-box-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .signature {
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            margin-top: 80px;
            font-size: 10px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Logo optional -->
            <div class="title">ÉTAT DES LIEUX - ENTRÉE</div>
            <div class="subtitle">Fait le {{ $etatLieu->created_at->format('d/m/Y') }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-box-title">Informations générales</div>
            <table>
                <tr>
                    <td width="30%"><strong>Locataire:</strong></td>
                    <td>{{ $etatLieu->locataire->name }} {{ $etatLieu->locataire->prenom }}</td>
                </tr>
                <tr>
                    <td><strong>Contact:</strong></td>
                    <td>{{ $etatLieu->locataire->contact }}</td>
                </tr>
                <tr>
                    <td><strong>Bien:</strong></td>
                    <td>
                        {{ $etatLieu->type_bien }}<br>
                        {{ $etatLieu->bien->adresse ?? '' }}<br>
                        {{ $etatLieu->bien->ville ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td><strong>Présence des parties:</strong></td>
                    <td>{{ ucfirst($etatLieu->presence_partie) }}</td>
                </tr>
                <tr>
                    <td><strong>Nombre de clés remises:</strong></td>
                    <td>{{ $etatLieu->nombre_cle }}</td>
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
                            <span class="badge {{ isset($etatLieu->parties_communes['sol']) && $etatLieu->parties_communes['sol'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['sol'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_sol'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>MURS</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['murs']) && $etatLieu->parties_communes['murs'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['murs'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_murs'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PLAFONDS</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['plafond']) && $etatLieu->parties_communes['plafond'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['plafond'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_plafond'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>PORTES</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['porte_entre']) && $etatLieu->parties_communes['porte_entre'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['porte_entre'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_porte_entre'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ELECTRICITE</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['interrupteur']) && $etatLieu->parties_communes['interrupteur'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['interrupteur'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_interrupteur'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ROBINETTERIE</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['robinet']) && $etatLieu->parties_communes['robinet'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['robinet'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_robinet'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>EVIER INOX DE LAVABO</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['lavabo']) && $etatLieu->parties_communes['lavabo'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['lavabo'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_lavabo'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>DOUCHE ET SDB</td>
                        <td>
                            <span class="badge {{ isset($etatLieu->parties_communes['douche']) && $etatLieu->parties_communes['douche'] === 'bon' ? 'badge-good' : 'badge-bad' }}">
                                {{ ucfirst($etatLieu->parties_communes['douche'] ?? 'Non renseigné') }}
                            </span>
                        </td>
                        <td class="observation-cell">{{ $etatLieu->parties_communes['observation_douche'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @foreach($etatLieu->chambres as $index => $chambre)
        <div class="section">
            <div class="section-title">PIECE {{ $index + 1 }}</div>
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
                </tbody>
            </table>
        </div>
        @endforeach
        
        <div class="signature" style="display: flex; justify-content: space-between; margin-top: 80px;">
            <!-- Signature locataire -->
            <div style="flex: 1; text-align: center;">
                <p style="margin-bottom: 60px;">Signature du locataire :</p>
                <p style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">
                    {{ $etatLieu->locataire->name }} {{ $etatLieu->locataire->prenom }}
                </p>
                <p style="margin-top: 10px;">Date : {{ $etatLieu->created_at->format('d/m/Y') }}</p>
            </div>

            <!-- Signature propriétaire/gestionnaire -->
            <div style="flex: 1; text-align: center;">
                <p style="margin-bottom: 60px;">Signature du propriétaire/gestionnaire :</p>
                <p style="border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 5px;">
                    {{ Auth::user()->name }} {{ Auth::user()->prenom }}
                </p>
                <p style="margin-top: 10px;">Date : {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
        
        <div class="footer">
            Document généré le {{ date('d/m/Y H:i') }} - © Côte d'ivoire
        </div>
    </div>
</body>
</html>