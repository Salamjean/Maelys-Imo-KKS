@extends('commercial.layouts.template')

@section('content')
<div class="content-wrapper p-4" style="background: #f8fafc !important;">
    <!-- Light Premium Styles -->
    <style>
        :root {
            --page-bg: #f8fafc;
            --surface-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --accent-primary: #02245b;
            --accent-secondary: #02245b;
            --accent-success: #10b981;
            --accent-danger: #f43f5e;
            --accent-amber: #f59e0b;
        }

        .premium-card {
            background: var(--surface-card);
            border-radius: 20px;
            color: var(--text-main);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Vibrant Metric Cards */
        .metric-card-vibrant {
            padding: 28px;
            color: white !important;
            border: none;
        }

        .vibrant-indigo { 
            background: linear-gradient(135deg, #02245b 0%, #02245b 100%); 
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }
        .vibrant-emerald { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }
        .vibrant-rose { 
            background: linear-gradient(135deg, #ff5e14 0%, #ff5e14 100%);
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.2);
        }

        .metric-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
        }

        .metric-value {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .metric-label {
            font-size: 0.95rem;
            font-weight: 600;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Quick Action Tiles */
        .action-tile {
            padding: 24px 16px;
            text-align: center;
            text-decoration: none !important;
            color: var(--text-main) !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .action-tile:hover {
            background: var(--accent-primary);
            color: white !important;
            border-color: var(--accent-primary);
        }

        .action-tile i {
            font-size: 2.2rem;
            color: var(--accent-primary);
            transition: color 0.2s;
        }

        .action-tile:hover i {
            color: white;
        }

        /* Modern Table */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table th {
            padding: 16px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            background: #f1f5f9;
        }

        .modern-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .partner-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }

        .type-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .badge-soft-indigo { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
        .badge-soft-emerald { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeInDown 0.6s ease-out forwards;
        }
    </style>

    <!-- Header Section -->
    <div class="row align-items-center mb-5 animate-fade">
        <div class="col-12 text-center text-md-left">
            <h1 class="display-4 font-weight-bold mb-1" style="color: #0f172a;">Bonjour 👋, {{ $commercial->prenom }}</h1>
            <p class="h5 text-muted font-weight-normal">Voici vos performances et vos dernières activités immobilières.</p>
        </div>
    </div>

    <div class="row">
        <!-- Dashboard Main Grid -->
        <div class="col-lg-8">
            <div class="row">
                <!-- Metrics -->
                <div class="col-md-6 mb-4 animate-fade" style="animation-delay: 0.1s">
                    <div class="premium-card metric-card-vibrant vibrant-indigo">
                        <div class="metric-icon-box">
                            <i class="mdi mdi-office-building"></i>
                        </div>
                        <div class="metric-value">{{ $totalAgences }}</div>
                        <div class="metric-label">Agences Partenaires</div>
                    </div>
                </div>
                <div class="col-md-6 mb-4 animate-fade" style="animation-delay: 0.2s">
                    <div class="premium-card metric-card-vibrant vibrant-emerald">
                        <div class="metric-icon-box">
                            <i class="mdi mdi-account-group"></i>
                        </div>
                        <div class="metric-value">{{ $totalProprietaires }}</div>
                        <div class="metric-label">Propriétaires</div>
                    </div>
                </div>

                <!-- Activities -->
                <div class="col-12 mb-4 animate-fade" style="animation-delay: 0.4s">
                    <div class="premium-card">
                        <div class="p-4 d-flex justify-content-between align-items-center border-bottom">
                            <h4 class="font-weight-bold mb-0">Activités Récentes</h4>
                            <span class="badge badge-light p-2">Dernières 5 entrées</span>
                        </div>
                        <div class="table-responsive">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Partenaire</th>
                                        <th>Type</th>
                                        <th>Date d'inscription</th>
                                        <th>ID Support</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivities as $activity)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="font-weight-bold" style="font-size: 1.05rem;">{{ $activity->name }} {{ $activity->prenom ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="type-badge {{ $activity->type_label == 'Agence' ? 'badge-soft-indigo' : 'badge-soft-emerald' }}">
                                                {{ $activity->type_label }}
                                            </span>
                                        </td>
                                        <td class="text-muted font-weight-medium">{{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y') }}</td>
                                        <td><code class="p-1 px-2 rounded" style="background: #f1f5f9; color: #475569;">{{ $activity->code_id }}</code></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="mdi mdi-database-off opacity-20 d-block mb-2" style="font-size: 3rem;"></i>
                                            <span class="text-muted">Aucune activité enregistrée</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Grid -->
        <div class="col-lg-4">
            <div class="row">
                <div class="col-12 mb-4 animate-fade" style="animation-delay: 0.3s">
                    <div class="premium-card metric-card-vibrant vibrant-rose">
                        <div class="metric-icon-box">
                            <i class="mdi mdi-home-modern"></i>
                        </div>
                        <div class="metric-value">{{ $totalBiens }}</div>
                        <div class="metric-label">Biens Immobiliers</div>
                    </div>
                </div>

                <div class="col-12 animate-fade" style="animation-delay: 0.5s">
                    <div class="premium-card p-4">
                        <h4 class="font-weight-bold mb-4">Actions Rapides</h4>
                        <div class="row no-gutters">
                            <div class="col-6 p-1">
                                <a href="{{ route('commercial.agences.create') }}" class="premium-card action-tile">
                                    <i class="mdi mdi-office-building"></i>
                                    <span>Agence</span>
                                </a>
                            </div>
                            <div class="col-6 p-1">
                                <a href="{{ route('commercial.proprietaires.create') }}" class="premium-card action-tile">
                                    <i class="mdi mdi-account-plus"></i>
                                    <span>Proprio</span>
                                </a>
                            </div>
                            <div class="col-12 p-1 mt-2">
                                <a href="{{ route('commercial.biens.choice') }}" class="premium-card action-tile" style="background: #0f172a; color: white !important;">
                                    <i class="mdi mdi-plus-circle-outline" style="color: white;"></i>
                                    <span>AJOUTER UN BIEN</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection