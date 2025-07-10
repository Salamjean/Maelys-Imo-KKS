@extends('home.pages.layouts.template')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-gradient-primary py-4 text-white" style="background-color: #02245b ">
                    <div class="d-flex align-items-center justify-content-between" >
                        <h2 class="h4 mb-0 fw-bold text-white text-center" ><i class="fas fa-user-tie me-2 text-white"></i> Inscription d'un nouveau propriétaire</h2>
                    </div>
                </div>

                <div class="card-body p-5">
                    <form method="POST" action="{{ route('owner.store.home.register') }}" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf

                        <!-- Informations personnelles -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2">
                                <i class="fas fa-user-circle me-2"></i>Informations personnelles
                            </legend>
                            
                            <div class="row g-3">
                                <!-- Nom -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-signature text-muted"></i></span>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Dupont">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Prénom -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-signature text-muted"></i></span>
                                            <input type="text" class="form-control @error('prenom') is-invalid @enderror" 
                                                id="prenom" name="prenom" value="{{ old('prenom') }}" required placeholder="Ex: Jean">
                                            @error('prenom')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                id="email" name="email" value="{{ old('email') }}" required placeholder="Ex: jean.dupont@example.com">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="fas fa-phone-alt text-muted"></i></span>
                                            <input type="tel" class="form-control @error('contact') is-invalid @enderror" 
                                                id="contact" name="contact" value="{{ old('contact') }}" required placeholder="Ex: +225 XX XX XX XX">
                                            @error('contact')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Localisation -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2"><i class="fas fa-map-marker-alt me-2"></i>Localisation</legend>
                            
                            <div class="row g-3 mb-3">
                                <!-- Commune -->
                                <div class="col-md-6">
                                    <label for="commune" class="form-label">Commune <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-city text-muted"></i></span>
                                        <input type="text" class="form-control @error('commune') is-invalid @enderror" 
                                            id="commune" name="commune" value="{{ old('commune') }}" required placeholder="Ex: Cocody">
                                        @error('commune')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Adresse -->
                                <div class="col-md-6">
                                    <label for="adresse" class="form-label">Adresse</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-map-pin text-muted"></i></span>
                                        <input type="text" class="form-control @error('adresse') is-invalid @enderror" 
                                            id="adresse" name="adresse" value="{{ old('adresse') }}" placeholder="Ex: Rue des Jardins">
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
                                <!-- RIB -->
                                <div class="col-md-6">
                                    <label for="rib" class="form-label">RIB</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-file-pdf text-muted"></i></span>
                                        <input type="file" class="form-control @error('rib') is-invalid @enderror" 
                                            id="rib" name="rib" accept=".pdf">
                                        @error('rib')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Format PDF uniquement (max 2MB)</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="profile_image" class="form-label">Photo de profile</label>
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

                        <!-- Gestion des biens -->
                        <fieldset class="mb-4">
                            <legend class="h6 text-primary mb-3 border-bottom pb-2"><i class="fas fa-home me-2"></i>Gestion des biens</legend>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="diaspora" name="diaspora" value="1" {{ old('diaspora') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="diaspora">
                                    Le propriétaire a-t-il des agents de gestion ? (cocher si oui)
                                </label>
                                @error('diaspora')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
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
                    <p class="mb-0">Déjà inscrit? <a href="{{ route('owner.login') }}" class="fw-bold text-primary">Connectez-vous ici</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

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