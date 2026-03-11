<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport d'Activité - {{ $commercial->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #02245b; padding-bottom: 10px; }
        .title { color: #02245b; font-size: 24px; margin-bottom: 5px; }
        .meta { color: #666; font-size: 14px; }
        .stats-container { display: table; width: 100%; margin-bottom: 30px; }
        .stat-box { display: table-cell; width: 33.33%; padding: 15px; text-align: center; border: 1px solid #ddd; }
        .stat-value { font-size: 24px; font-weight: bold; color: #02245b; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f8fafc; color: #02245b; text-align: left; padding: 10px; border: 1px solid #ddd; font-size: 12px; }
        td { padding: 10px; border: 1px solid #ddd; font-size: 11px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Rapport d'Activité Commerciale</div>
        <div class="meta">Commercial : <strong>{{ $commercial->name }}</strong> | Généré le : {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <h3>Statistiques Globales</h3>
    <div class="stats-container">
        <div class="stat-box">
            <div class="stat-value">{{ $totalAgences }}</div>
            <div class="stat-label">Agences</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $totalProprietaires }}</div>
            <div class="stat-label">Propriétaires</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $totalBiens }}</div>
            <div class="stat-label">Biens</div>
        </div>
    </div>

    <h3>Historique d'Activité (30 derniers jours)</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Agences Ajoutées</th>
                <th>Propriétaires Ajoutés</th>
                <th>Biens Enregistrés</th>
            </tr>
        </thead>
        <tbody>
            @forelse($history as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td>{{ $day['agences'] }}</td>
                    <td>{{ $day['proprietaires'] }}</td>
                    <td>{{ $day['biens'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Aucune activité enregistrée sur cette période.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Maelys-Imo - Système de Gestion Immobilière &copy; {{ date('Y') }}
    </div>
</body>
</html>
