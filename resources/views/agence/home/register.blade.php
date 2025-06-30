@extends('home.pages.layouts.template')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-gradient-primary py-4 text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h4 mb-0 fw-bold text-white text-center"><i class="fas fa-building me-2 text-white"></i> Inscription d'une nouvelle agence</h2>
                    </div>
                </div>

                <div class="card-body p-5">
                    <form method="POST" action="{{ route('agence.store.home.register') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf

                        <!-- Informations de base -->
                        <fieldset class="mb-4">
                    <legend class="h6 text-primary mb-3 border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i>Informations de base
                    </legend>
                    
                    <div class="row g-3">
                        <!-- Colonne 1 -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom de l'agence <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-signature text-muted"></i></span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                        id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Agence XYZ">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Le nom officiel de votre agence</small>
                            </div>
                        </div>

                        <!-- Colonne 2 -->
                            

                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                            id="email" name="email" value="{{ old('email') }}" required placeholder="Ex: contact@agence.ci">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Email professionnel</small>
                                </div>
                            </div>

                            <!-- Colonne 3 -->
                            <div class="col-md-4">
                                    <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                                    <input type="tel" class="form-control @error('contact') is-invalid @enderror" 
                                            id="contact" name="contact" value="{{ old('contact') }}" required placeholder="Ex: +225 XX XX XX XX">
                                    @error('contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Contact de l'agence</small>
                            </div>
                        </div>
                    </fieldset>

                        <!-- Localisation -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2"><i class="fas fa-map-marker-alt me-2"></i>Localisation</legend>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="commune" class="form-label">Commune <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-city text-muted"></i></span>
                                        <select class="form-select @error('commune') is-invalid @enderror" 
                                                id="commune" name="commune" required>
                                            <option value="" disabled selected>Sélectionnez une commune</option>
                                            <option value="Abobo">Abobo</option>
                                            <option value="Cocody">Cocody</option>
                                            <option value="Yopougon">Yopougon</option>
                                            <!-- Ajoutez d'autres communes au besoin -->
                                        </select>
                                        @error('commune')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="adresse" class="form-label">Adresse complète</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-pin text-muted"></i></span>
                                        <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                                               id="adresse" name="adresse" value="{{ old('adresse') }}" placeholder="Ex: Rue des Jardins, Immeuble ABC">
                                        @error('adresse')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Informations financières -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2"><i class="fas fa-wallet me-2"></i>Informations financières</legend>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="rib" class="form-label">RIB</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('rib') is-invalid @enderror" 
                                               id="rib" name="rib" value="{{ old('rib') }}" >
                                        @error('rib')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Relevé d'Identité Bancaire de format PDF </small>
                                </div>

                                <div class="col-md-6">
                                    <label for="profile_image" class="form-label">Logo de l'agence</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control @error('profile_image') is-invalid @enderror" 
                                               id="profile_image" name="profile_image" accept="image/*">
                                        @error('profile_image')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Format: JPG, PNG (max 2MB)</small>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Mot de passe -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2"><i class="fas fa-lock me-2"></i>Sécurité</legend>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted">Le mot de passe doit contenir au moins 8 caractères</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirmation <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                                        <input type="password" class="form-control" 
                                               id="password_confirmation" name="password_confirmation" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold rounded-pill">
                                <i class="fas fa-user-plus me-2"></i> Finaliser l'inscription
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0">Déjà inscrit? <a href="{{ route('agence.login') }}" class="fw-bold text-primary">Connectez-vous ici</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .bg-gradient-primary {
        background: linear-gradient(135deg, #02245b 0%, #02245b 100%);
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    .input-group-text {
        transition: all 0.3s;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        border-color: #86b7fe;
    }
    .rounded-4 {
        border-radius: 1rem !important;
    }
    fieldset {
        border: 1px solid #e9ecef;
        border-radius: 0.75rem;
        padding: 1.25rem;
        background-color: #f8f9fa;
    }
    legend {
        width: auto;
        padding: 0 10px;
        font-size: 0.95rem;
    }
    .toggle-password {
        cursor: pointer;
    }
</style>

<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password strength indicator
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.querySelector('.progress-bar');
        let strength = 0;
        
        if (password.length > 0) strength += 20;
        if (password.length >= 8) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        strengthBar.style.width = strength + '%';
        
        if (strength < 40) {
            strengthBar.className = 'progress-bar bg-danger';
        } else if (strength < 80) {
            strengthBar.className = 'progress-bar bg-warning';
        } else {
            strengthBar.className = 'progress-bar bg-success';
        }
    });
</script>
@endsection