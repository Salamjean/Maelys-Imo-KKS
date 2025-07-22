<!-- resources/views/comptable/verification.blade.php -->

@extends('comptable.layouts.template')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Vérification d'accès</h6>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <div class="verification-code">{{ $code }}</div>
                <div class="qrcode-container">{!! $qrCode !!}</div>
                <p class="text-muted mt-3">Scannez ce QR code ou saisissez le code ci-dessous</p>
            </div>
            
            <form method="POST" action="{{ route('verification.verify') }}">
                @csrf
                <input type="hidden" name="locataire_id" value="{{ $locataireId }}">
                
                <div class="form-group mb-4">
                    <label for="verification_code">Code de vérification</label>
                    <input type="text" class="form-control verification-input" 
                           id="verification_code" name="verification_code" 
                           required autofocus>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check-circle mr-2"></i> Valider le code
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .verification-code {
        font-size: 2rem;
        letter-spacing: 0.5rem;
        font-weight: bold;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        display: inline-block;
        margin-bottom: 1.5rem;
    }
    
    .qrcode-container {
        display: inline-block;
        padding: 1rem;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
    }
    
    .verification-input {
        font-size: 1.2rem;
        letter-spacing: 0.3rem;
        text-align: center;
        padding: 0.75rem;
    }
</style>
@endsection