@extends('locataire.layouts.template')

@section('content')
<div class="container py-5"  >
    <div class="row ">
        <div class="col-md-12" >
            <h2>Historique des Paiements</h2>
            
            <div class="card" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); width: 100%;">
                <div class="card-header d-flex justify-content-between align-items-center" style="background-color:#02245b; color: white; border-radius: 10px 10px 0 0;">
                    <span>Mes paiements de loyer</span>
                    <a href="{{ route('locataire.paiements.create', $locataire->id) }}" class="btn" style="background-color: white; color:  #02245b;">
                        <i class="fas fa-plus"></i> Nouveau Paiement
                    </a>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr class="text-center">
                                    <th>Mois couvert</th>
                                    <th>Montant</th>
                                    <th>Date Paiement</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locataire->paiements as $paiement)
                                <tr class="text-center">
                                    <td class="text-center">{{ \Carbon\Carbon::parse($paiement->mois_couvert)->translatedFormat('F Y') }}</td>
                                    <td class="text-center">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                    <td class="text-center">{{ $paiement->methode_paiement }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-{{ $paiement->statut == 'payé' ? 'success' : ($paiement->statut == 'échoué' ? 'danger' : 'warning') }}">
                                            {{ $paiement->statut }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($paiement->statut == 'payé')
                                        <a href="{{ route('locataire.paiements.receipt', $paiement->id) }}" 
                                           class="btn btn-sm btn-info"
                                           target="_blank">
                                            <i class="fas fa-receipt"></i> Reçu
                                        </a>
                                        @else
                                        <span class="text-muted">Non disponible</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Aucun paiement effectué.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection