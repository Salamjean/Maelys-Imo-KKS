@extends('locataire.layouts.template')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .property-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: none;
        transition: transform 0.3s ease;
    }

    .agency-popup .form-group {
    margin-bottom: 1rem;
    }

    .agency-popup label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #02245b;
    }

    .agency-popup .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .agency-popup textarea.form-control {
        min-height: 120px;
    }
    
    .property-card:hover {
        transform: translateY(-5px);
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #02245b 0%, #0066cc 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .property-detail {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .property-detail:last-child {
        border-bottom: none;
    }
    
    .detail-label {
        font-weight: 600;
        color: #02245b;
        display: inline-block;
        min-width: 180px;
    }
    
    .detail-value {
        color: #555;
    }
    
    .property-image {
        border-radius: 10px;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .property-image:hover {
        transform: scale(1.03);
    }
    
    .image-container {
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 10px;
        text-align: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .image-container:hover .image-overlay {
        opacity: 1;
    }
    
    .section-title {
        position: relative;
        margin-bottom: 2rem;
        color: #02245b;
    }
    
    .section-title:after {
        content: "";
        position: absolute;
        left: 0;
        bottom: -10px;
        width: 60px;
        height: 3px;
        background: #02245b;
    }
    
    .no-property {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        border-radius: 4px;
    }
    
    .price-badge {
        background: #02245b;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 1.1rem;
        display: inline-block;
        margin-top: 10px;
    }
    
    /* Styles pour le popup */
    .agency-popup {
        border-radius: 15px;
        border-left: 5px solid #02245b;
    }
    
    .agency-popup .swal2-title {
        color: #02245b;
        font-weight: 600;
    }
    
    .agency-popup .swal2-html-container {
        font-size: 1.1rem;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="section-title mb-4">Mon Bien Immobilier</h1>
            
            @if($locataire->bien)
                <div class="card property-card mb-5">
                    <div class="card-header card-header-custom">
                        <h2 class="mb-0">
                            <i class="mdi mdi-home-city mr-2"></i>
                            {{ $locataire->bien->type }} à {{ $locataire->bien->commune }}
                        </h2>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Colonne de gauche - Détails du bien -->
                            <div class="col-md-6">
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-format-list-bulleted-type mr-2"></i>Type:</span>
                                    <span class="detail-value">{{ $locataire->bien->type }}</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-account-card-details mr-2"></i>Description:</span>
                                    <span class="detail-value">{{ $locataire->bien->description }}</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-border-all mr-2"></i>Superficie:</span>
                                    <span class="detail-value">{{ $locataire->bien->superficie }} m²</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-hotel mr-2"></i>Chambres:</span>
                                    <span class="detail-value">{{ $locataire->bien->nombre_de_chambres }}</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-shower mr-2"></i>Toilettes:</span>
                                    <span class="detail-value">{{ $locataire->bien->nombre_de_toilettes }}</span>
                                </div>
                            </div>
                            
                            <!-- Colonne de droite - Détails supplémentaires -->
                            <div class="col-md-6">
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-garage mr-2"></i>Garage:</span>
                                    <span class="detail-value">{{ $locataire->bien->garage ? 'Oui' : 'Non' }}</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-cash-multiple mr-2"></i>Loyer mensuel:</span>
                                    <span class="detail-value">{{ number_format($locataire->bien->prix, 0, ',', ' ') }} FCFA</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-map-marker mr-2"></i>Localisation:</span>
                                    <span class="detail-value">{{ $locataire->bien->commune }}</span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-check-circle mr-2"></i>Statut:</span>
                                    <span class="detail-value badge 
                                        {{ $locataire->bien->status == 'disponible' ? 'badge-success' : 
                                           ($locataire->bien->status == 'loué' ? 'badge-primary' : 'badge-warning') }}">
                                        {{ $locataire->bien->status }}
                                    </span>
                                </div>
                                
                                <div class="property-detail">
                                    <span class="detail-label"><i class="mdi mdi-calendar-clock mr-2"></i>Occupé depuis:</span>
                                    <span class="detail-value">{{ $locataire->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section Images -->
                        <div class="mt-5">
                            <h3 class="section-title"><i class="mdi mdi-image-multiple mr-2"></i>Galerie du Bien</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="image-container">
                                        <img src="  {{ asset('storage/' . $locataire->bien->image) }}" 
                                             alt="Photo principale" 
                                             class="img-fluid property-image"  style="width: 100%">
                                        <div class="image-overlay">Photo principale</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="image-container">
                                        <img src="{{ asset('storage/' . $locataire->bien->image1) }}" 
                                             alt="Photo secondaire" 
                                             class="img-fluid property-image" style="width: 100%">
                                        <div class="image-overlay">Photo secondaire</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <span class="price-badge">
                                <i class="mdi mdi-currency-sign mr-1"></i>
                                {{ number_format($locataire->bien->prix, 0, ',', ' ') }} FCFA/mois
                            </span>
                        </div>
                        <div>
                            <button class="btn btn-primary mr-2">
                                <a href="{{ route('locataires.downloadContrat', $locataire->id) }}" target="blank_" class="text-white"><i class="mdi mdi-download mr-1"></i> Télecharger le contrat</a>
                            </button>
                           <button class="btn btn-outline-secondary" id="contact-agency-btn"
                                data-agence-name="@if($locataire->agence_id)
                                    Agence: {{ $locataire->agence->name ?? 'ecole' }}
                                @elseif($locataire->proprietaire_id)
                                    @if($locataire->proprietaire->gestion == 'agence')
                                        Agence: Maelys-imo
                                    @else
                                        Propriétaire: {{ $locataire->proprietaire->name.' '.$locataire->proprietaire->prenom ?? 'Maelys-imo' }}
                                    @endif
                                @else
                                    Agence: Maelys-imo
                                @endif"
                                data-agence-email="@if($locataire->agence_id)
                                    {{ $locataire->agence->email ?? 'contact@maelysimo.com' }}
                                @elseif($locataire->proprietaire_id)
                                    @if($locataire->proprietaire->gestion == 'agence')
                                        contact@maelysimo.com
                                    @else
                                        {{ $locataire->proprietaire->email ?? 'contact@maelysimo.com' }}
                                    @endif
                                @else
                                    contact@maelysimo.com
                                @endif"
                                data-agence-phone="@if($locataire->agence_id)
                                    {{ $locataire->agence->contact ?? '+225 27 22 36 50 27' }}
                                @elseif($locataire->proprietaire_id)
                                    {{ $locataire->proprietaire->contact ?? '+225 27 22 36 50 27' }}
                                @else
                                    +225 27 22 36 50 27
                                @endif"
                                data-agence-address="@if($locataire->agence_id)
                                    {{ $locataire->agence->adresse ?? 'Cocody - Angré, Abidjan' }}
                                @elseif($locataire->proprietaire_id)
                                    {{ $locataire->proprietaire->commune ?? 'Cocody - Angré, Abidjan' }}
                                @else
                                    Cocody - Angré, Abidjan
                                @endif">
                                <i class="mdi mdi-message-text-outline mr-1"></i> Contacter l'agence
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert no-property alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="mdi mdi-alert-outline mr-2"></i>Aucun bien associé</h4>
                    <p>Vous n'avez actuellement aucun bien immobilier associé à votre compte locataire.</p>
                    <hr>
                    <p class="mb-0">Si vous pensez qu'il s'agit d'une erreur, veuillez contacter votre agence immobilière.</p>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation au chargement
    $('.property-card').hide().fadeIn(800);
    
    // Tooltip pour les icônes
    $('[data-toggle="tooltip"]').tooltip();
    
    // Zoom sur les images au clic
    $('.property-image').click(function() {
        const imgSrc = $(this).attr('src');
        const imgAlt = $(this).attr('alt');
        
        Swal.fire({
            imageUrl: imgSrc,
            imageAlt: imgAlt,
            showConfirmButton: false,
            background: 'transparent',
            backdrop: `
                rgba(0,0,0,0.8)
                url("/images/zoom-icon.png")
                center center
                no-repeat
            `,
            showCloseButton: true,
            width: '80%'
        });
    });

// Gestion du clic sur le bouton "Contacter l'agence"
document.getElementById('contact-agency-btn').addEventListener('click', function() {
    // Récupérer les données de l'agence depuis les attributs data
    const agenceName = this.getAttribute('data-agence-name');
    const agenceEmail = this.getAttribute('data-agence-email');
    const agencePhone = this.getAttribute('data-agence-phone');
    const agenceAddress = this.getAttribute('data-agence-address');
    // Déterminer l'icône en fonction du type (Agence ou Propriétaire)
    const isAgency = agenceName.includes('Agence:');
    const iconClass = isAgency ? 'mdi-office-building' : 'mdi-account';
    
    Swal.fire({
        title: 'Contacter ' + (isAgency ? 'l\'agence' : 'le propriétaire'),
        html: `
            <div style="text-align: left;">
                <p><strong><i class="mdi ${iconClass} mr-2"></i></strong> ${agenceName || 'Non spécifié'}</p>
                <p><strong><i class="mdi mdi-email mr-2"></i>Email:</strong> ${agenceEmail || 'Non spécifié'}</p>
                <p><strong><i class="mdi mdi-phone mr-2"></i>Téléphone:</strong> ${agencePhone || 'Non spécifié'}</p>
                <p><strong><i class="mdi mdi-map-marker mr-2"></i>Adresse:</strong> ${agenceAddress || 'Non spécifié'}</p>
                <hr>
                <form id="contact-agency-form">
                    <div class="form-group">
                        <label for="message-subject">Objet</label>
                        <input type="text" class="form-control" id="message-subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message-content">Message</label>
                        <textarea class="form-control" id="message-content" rows="5" required></textarea>
                    </div>
                </form>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Envoyer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#02245b',
        customClass: {
            popup: 'agency-popup'
        },
       preConfirm: () => {
    const subject = document.getElementById('message-subject').value;
    const content = document.getElementById('message-content').value;
    
    if (!subject || !content) {
        Swal.showValidationMessage('Veuillez remplir tous les champs');
        return false;
    }
    
    // Préparer les données au format JSON
    const data = {
        subject: subject,
        content: content,
        agency_email: agenceEmail, // Notez le changement de recipient_email à agency_email
        _token: '{{ csrf_token() }}'
    };
    
    return fetch('{{ route("locataire.sendEmailToAgency") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Erreur serveur');
        }
        return response.json();
    })
    .catch(error => {
        Swal.showValidationMessage(`Erreur lors de l'envoi: ${error.message}`);
    });
}
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Envoyé!',
                'Votre message a été envoyé à l\'agence.',
                'success'
            );
        }
    });
});
});
</script>
@endsection