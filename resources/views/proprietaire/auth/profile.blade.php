@extends('proprietaire.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="card-title mb-0">Modifier mon profil</h4>
                <a href="{{ route('owner.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Retour
                </a>
            </div>
            <p class="card-description text-muted mb-4">Mettez à jour vos informations</p>
            
            <form class="forms-sample" method="POST" action="{{ route('owner.update.profile') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Colonne gauche - Photo de profil -->
                    <div class="col-md-4">
                        <div class="form-group text-center" style="border: 1px solid #ddd; border-radius: 5px; padding: 15px;">
                            <div class="profile-image-container mb-4">
                                @if($proprietaire->profil_image)
                                    <img src="{{ asset('storage/'.$proprietaire->profil_image) }}" 
                                         class="profile-image-preview img-fluid rounded-circle shadow-sm mb-3" 
                                         alt="Photo profil"
                                         id="profile-preview">
                                @else
                                    <div class="default-avatar rounded-circle shadow-sm mb-3 d-flex align-items-center justify-content-center" id="default-avatar">
                                        <i class="fas fa-user fa-3x text-primary"></i>
                                    </div>
                                @endif
                                
                                <div class="file-upload-wrapper">
                                    <label for="profile_image" class="btn btn-outline-primary btn-sm btn-block">
                                        <i class="fas fa-camera mr-2"></i>
                                        {{ $proprietaire->profil_image ? 'Changer la photo' : 'Ajouter une photo' }}
                                    </label>
                                    <input type="file" class="d-none" id="profile_image" name="profile_image" accept="image/*">
                                </div>
                                @error('profile_image')
                                    <div class="invalid-feedback d-block text-center">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">Formats acceptés: JPG, PNG (max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite - Informations -->
                    <div class="col-md-8">
                        <div class="card" style="border: 1px solid #ddd;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Informations personnelles</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Nom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="{{ old('name', $proprietaire->name) }}">
                                            @error('name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="prenom">Prénom <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="prenom" name="prenom" 
                                                   value="{{ old('prenom', $proprietaire->prenom) }}">
                                            @error('prenom')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="{{ old('email', $proprietaire->email) }}" readonly>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact">Téléphone <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="contact" name="contact" 
                                                   value="{{ old('contact', $proprietaire->contact) }}">
                                            @error('contact')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="commune">Commune <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="commune" name="commune" 
                                                   value="{{ old('commune', $proprietaire->commune) }}">
                                            @error('commune')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section mot de passe -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card" style="border: 1px solid #ddd;">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Changer le mot de passe</h5>
                                <p class="text-muted small mb-4">Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe</p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nouveau mot de passe</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="password" 
                                                       placeholder="Saisissez votre nouveau mot de passe">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-eye toggle-password" style="cursor: pointer"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            @error('password') 
                                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Confirmation</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="password_confirm" 
                                                       placeholder="Confirmez votre nouveau mot de passe">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-eye toggle-password" style="cursor: pointer"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            @error('password_confirm') 
                                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .profile-image-container {
        padding: 20px;
        border-radius: 10px;
        background-color: #f8f9fa;
    }
    
    .profile-image-preview, .default-avatar {
        width: 200px;
        height: 200px;
        object-fit: cover;
        margin: 0 auto;
    }
    
    .default-avatar {
        background-color: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .card-title {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }
    
    .invalid-feedback {
        font-size: 0.85rem;
        color: #dc3545;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la prévisualisation de l'image
    const profileImageInput = document.getElementById('profile_image');
    const defaultAvatar = document.getElementById('default-avatar');
    
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    let preview = document.getElementById('profile-preview');
                    
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'profile-preview';
                        preview.className = 'profile-image-preview img-fluid rounded-circle shadow-sm mb-3';
                        preview.alt = 'Photo profil';
                        
                        if (defaultAvatar) {
                            defaultAvatar.parentNode.insertBefore(preview, defaultAvatar.nextSibling);
                            defaultAvatar.style.display = 'none';
                        }
                    }
                    
                    preview.src = event.target.result;
                };
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(icon) {
        icon.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    });
});
</script>
@endsection