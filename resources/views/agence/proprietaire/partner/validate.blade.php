@extends('agence.layouts.template')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Valider le paiement par chèque</h6>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5>Détails du paiement</h5>
                <p><strong>Propriétaire:</strong> {{ $payment->proprietaire->prenom }} {{ $payment->proprietaire->name }}</p>
                <p><strong>Bénéficiaire:</strong> {{ $payment->beneficiaire_prenom }} {{ $payment->beneficiaire_nom }}</p>
                <p><strong>Montant:</strong> {{ $payment->montant }}</p>
            </div>

            <form method="POST" action="{{ route('partner.payment.validate', $payment->id) }}">
                @csrf
                
                <div class="form-group">
                    <label for="verification_code">Code de vérification</label>
                    <input type="text" class="form-control @error('verification_code') is-invalid @enderror" 
                           id="verification_code" name="verification_code" required maxlength="6" 
                           placeholder="Entrez le code à 6 caractères">
                    @error('verification_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Le code a été envoyé par email au propriétaire.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Valider le paiement</button>
                <a href="{{ route('partner.payment.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection