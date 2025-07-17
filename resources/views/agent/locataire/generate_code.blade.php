@extends('comptable.layouts.template')
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
    :root {
        --primary-color: #02245b;
        --secondary-color: #02245b;
        --accent-color: #4895ef;
        --danger-color: red;
        --success-color: rgb(3, 141, 3);
        --light-bg: #f8f9fa;
        --card-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    body {
        background-color: var(--light-bg);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .card-title {
        font-weight: 700;
        margin-bottom: 0;
        font-size: 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .btn {
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 0.75rem 1.5rem;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        background: linear-gradient(to right, var(--primary-color), var(--accent-color));
    }
    
    .btn-primary:hover {
        background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
    }
    
    .btn-secondary {
        background-color: #6c757d;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-2px);
    }
    
    .btn-success {
        background-color: var(--success-color);
    }
    
    .btn-success:hover {
        background-color: #3aa8d5;
        transform: translateY(-2px);
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 1.25rem 1.5rem;
        border-color: rgba(0,0,0,0.05);
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .montant-due {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--danger-color);
        text-align: center;
        margin: 1.5rem 0;
        padding: 1rem;
        background-color: rgba(247, 37, 133, 0.1);
        border-radius: 10px;
    }
    
    .montant-due span {
        color: var(--primary-color);
    }
    
    .verification-section {
        background-color: rgba(67, 97, 238, 0.05);
        padding: 2rem;
        border-radius: 10px;
        margin-top: 2rem;
        border: 1px dashed rgba(67, 97, 238, 0.3);
        animation: fadeIn 0.5s ease;
    }
    
    /* Styles pour le scanner QR */
    #qr-scanner-container {
        position: relative;
        margin: 20px 0;
    }
    
    #qr-video {
        width: 100%;
        border-radius: 8px;
        border: 2px solid var(--primary-color);
    }
    
    #qr-canvas {
        display: none;
    }
    
    .scan-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        pointer-events: none;
    }
    
    .scan-frame {
        width: 70%;
        height: 70%;
        border: 3px solid var(--accent-color);
        border-radius: 8px;
        box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .form-control {
        padding: 0.75rem 1rem;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.25rem rgba(72, 149, 239, 0.25);
    }
    
    .icon-container {
        display: inline-block;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: rgba(67, 97, 238, 0.1);
        text-align: center;
        line-height: 50px;
        margin-right: 15px;
        color: var(--primary-color);
    }
    
    .section-title {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(to right, var(--primary-color), var(--accent-color));
        border-radius: 3px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100% !important;
            margin-bottom: 10px;
        }
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="card-title mb-0 text-white">
                        <i class="fas fa-money-bill-wave me-2 text-white"></i>Paiement en Espèces
                    </h3>
                </div>
                
                <div class="card-body">
                    <h4 class="section-title">Informations du locataire</h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="icon-container">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong>Nom complet :</strong> 
                                        <div class="text-primary">{{ $locataire->name }} {{ $locataire->prenom }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="icon-container">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div>
                                        <strong>Contact :</strong> 
                                        <div class="text-primary">{{ $locataire->contact }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="icon-container">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div>
                                        <strong>Email :</strong> 
                                        <div class="text-primary">{{ $locataire->email }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <div class="icon-container">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div>
                                        <strong>Adresse :</strong> 
                                        <div class="text-primary">{{ $locataire->adresse }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="montant-due">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Montant dû : <span>{{ $locataire->bien->montant_majore ?? $locataire->bien->prix }} FCFA</span>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary generate-cash-code" data-locataire-id="{{ $locataire->id }}" style="width: 48%;">
                            <i class="fas fa-money-bill-wave me-2"></i> Générer Code
                        </button>
                        <button class="btn btn-secondary show-verification-section" style="width: 48%;">
                            <i class="fas fa-key me-2"></i> Valider Paiement
                        </button>
                    </div>
                    
                    <div class="verification-section" id="verificationSection" style="display: none;">
                        <h4 class="section-title">Validation du paiement</h4>
                        
                        <div class="text-center mb-4">
                            <button id="startScannerBtn" class="btn btn-outline-primary mb-3">
                                <i class="fas fa-qrcode me-2"></i>Scanner QR Code
                            </button>
                            <p class="text-muted">OU</p>
                        </div>
                        
                        <div id="qr-scanner-container" style="display: none;">
                            <video id="qr-video"></video>
                            <canvas id="qr-canvas"></canvas>
                            <div class="scan-overlay">
                                <div class="scan-frame"></div>
                            </div>
                        </div>
                        
                        <form id="verifyCodeForm" method="POST" action="{{ route('paiements.verifyCashCodeAgent') }}">
                            @csrf
                            <input type="hidden" name="locataire_id" value="{{ $locataire->id }}">
                            
                            <div class="mb-4">
                                <label for="cashVerificationCode" class="form-label fw-bold">
                                    <i class="fas fa-qrcode me-2"></i>Code de vérification
                                </label>
                                <input type="text" class="form-control form-control-lg text-center" 
                                       id="cashVerificationCode" name="code" 
                                       maxlength="6" placeholder="Entrez le code à 6 caractères" required
                                       style="letter-spacing: 0.5em; font-weight: bold;">
                                <small class="text-muted">Le code a été envoyé au locataire</small>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 py-3">
                                <i class="fas fa-check-circle me-2"></i>Valider le Paiement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>

<script>
$(document).ready(function() {
    // CSRF Token pour AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Variables pour le scanner QR
    let qrScannerActive = false;
    let videoStream = null;
    
    // Afficher/masquer la section de vérification
    $('.show-verification-section').on('click', function() {
        $('#verificationSection').slideToggle();
        if (qrScannerActive) {
            stopQRScanner();
        }
    });
    
    // Gestion du clic sur "Générer Code"
    $('body').on('click', '.generate-cash-code', function() {
        const locataireId = $(this).data('locataire-id');
        const button = $(this);
        
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>Génération...');

        // D'abord demander le nombre de mois (ici fixé à 1)
        Swal.fire({
            title: 'Mois en cours',
            html: `
                <div class="mb-3">
                    <label for="nombreMois" class="form-label">Vous ne pouvez encaisser que le loyer du mois en cours</label>
                    <input type="number" class="form-control" id="nombreMois" readonly min="1" value="1">
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Générer le code',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                return { mois: 1 }; // Toujours 1 mois
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const nombreMois = result.value.mois;
                
                // Générer le code via AJAX
                $.ajax({
                    url: "{{ route('paiements.generateCashCode') }}",
                    type: 'POST',
                    data: { 
                        locataire_id: locataireId,
                        nombre_mois: nombreMois
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Code généré',
                                html: `
                                    <div class="alert alert-success">
                                        <p>${response.message}</p>
                                        <p class="mb-0">Mois payés: <strong>${response.mois_couverts}</strong></p>
                                    </div>
                                    <p class="mt-3">Le code a été envoyé au locataire.</p>
                                `,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Afficher la section de vérification
                                $('#verificationSection').show();
                            });
                        } else {
                            Swal.fire('Erreur', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erreur', xhr.responseJSON?.message || 'Erreur lors de la génération du code', 'error');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                        button.html('<i class="fas fa-money-bill-wave me-2"></i>Générer Code');
                    }
                });
            } else {
                button.prop('disabled', false);
                button.html('<i class="fas fa-money-bill-wave me-2"></i>Générer Code');
            }
        });
    });
    
    // Démarrer le scanner QR
    $('#startScannerBtn').on('click', function() {
        startQRScanner();
    });
    
    // Fonction pour démarrer le scanner QR
    function startQRScanner() {
        const videoElement = document.getElementById('qr-video');
        const qrContainer = document.getElementById('qr-scanner-container');
        const startBtn = $('#startScannerBtn');
        
        qrContainer.style.display = 'block';
        startBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Scanner en cours...');
        
        // Options pour la caméra
        const constraints = {
            video: {
                facingMode: "environment", // Préférer la caméra arrière
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        // Demander l'accès à la caméra
        navigator.mediaDevices.getUserMedia(constraints)
            .then(function(stream) {
                videoStream = stream;
                videoElement.srcObject = stream;
                videoElement.play();
                qrScannerActive = true;
                
                // Démarrer la détection QR
                requestAnimationFrame(scanQRCode);
            })
            .catch(function(err) {
                console.error("Erreur caméra:", err);
                startBtn.prop('disabled', false).html('<i class="fas fa-qrcode me-2"></i>Scanner QR Code');
                
                let errorMessage = "Erreur d'accès à la caméra";
                if (err.name === 'NotAllowedError') {
                    errorMessage = "Permission refusée. Veuillez autoriser l'accès à la caméra.";
                } else if (err.name === 'NotFoundError') {
                    errorMessage = "Aucune caméra trouvée.";
                }
                
                Swal.fire({
                    title: 'Erreur',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        
        // Fonction pour scanner le QR code
        function scanQRCode() {
            if (!qrScannerActive) return;
            
            const video = document.getElementById('qr-video');
            const canvas = document.getElementById('qr-canvas');
            const canvasContext = canvas.getContext('2d');
            
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                
                if (code) {
                    // Code QR détecté
                    stopQRScanner();
                    $('#cashVerificationCode').val(code.data);
                    
                    Swal.fire({
                        title: 'Code détecté!',
                        text: 'Le code a été automatiquement saisi.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            }
            
            if (qrScannerActive) {
                requestAnimationFrame(scanQRCode);
            }
        }
    }
    
    // Fonction pour arrêter le scanner QR
    function stopQRScanner() {
        qrScannerActive = false;
        const videoElement = document.getElementById('qr-video');
        const qrContainer = document.getElementById('qr-scanner-container');
        const startBtn = $('#startScannerBtn');
        
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
        
        if (videoElement.srcObject) {
            videoElement.srcObject = null;
        }
        
        qrContainer.style.display = 'none';
        startBtn.prop('disabled', false).html('<i class="fas fa-qrcode me-2"></i>Scanner QR Code');
    }
    
    // Vérification du code de paiement
    $('#verifyCodeForm').on('submit', function(event) {
        event.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const code = $('#cashVerificationCode').val().trim();
        
        if (code.length !== 6) {
            Swal.fire({
                title: 'Code invalide',
                text: 'Le code doit contenir exactement 6 caractères',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Validation...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Paiement validé',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                <h4>${response.message}</h4>
                                <div class="alert alert-success mt-3">
                                    <p class="mb-1">Mois payés: <strong>${response.mois_payes}</strong></p>
                                    <p class="mb-0">Montant total: <strong>${response.montant_total} FCFA</strong></p>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Terminer',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    }).then(() => {
                        window.location.href = "{{ route('accounting.agent.paid') }}";
                    });
                } else {
                    Swal.fire({
                        title: 'Erreur',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Erreur',
                    text: xhr.responseJSON?.message || 'Erreur lors de la vérification',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Valider le Paiement');
            }
        });
    });
    
    // Arrêter le scanner quand la section est masquée
    $('#verificationSection').on('hide.bs.collapse', function() {
        if (qrScannerActive) {
            stopQRScanner();
        }
    });
});
</script>
@endsection