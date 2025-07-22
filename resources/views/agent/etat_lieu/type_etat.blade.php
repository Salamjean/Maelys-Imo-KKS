@extends('comptable.layouts.template')
@section('content')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ $title }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr class="text-center">
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($locataires as $locataire)
                        <tr class="text-center">
                            <td>{{ $locataire->name }}</td>
                            <td>{{ $locataire->prenom }}</td>
                            <td>{{ $locataire->email }}</td>
                            <td>{{ $locataire->contact }}</td>
                            <td>{{ $locataire->adresse }}</td>
                           <td>
                                @php
                                    $etatLieu = $locataire->etatLieu()->where('status_etat_entre', 'Oui')->first();
                                @endphp
                                
                                @if(!empty($etatLieu))
                                    <a href="{{ route('etat-lieux.download', $etatLieu->id) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-download me-1"></i> Télécharger/Entrée
                                    </a>
                                @else
                                    <a href="#" 
                                        class="btn btn-primary btn-sm btn-etat-entree"
                                        data-locataire-id="{{ $locataire->id }}"
                                        data-target-url="{{ route('etat.entree', $locataire->id) }}">
                                        <i class="fas fa-clipboard-check me-1"></i> État à l'entrée
                                    </a>
                                @endif
                                
                                @if(!empty($etatLieu))
                                    @php
                                        $etatLieuSortie = $locataire->etatLieuSorti()->where('status_sorti', 'Oui')->first();
                                    @endphp
                                    
                                    @if(!empty($etatLieuSortie))
                                        <a href="{{ route('etat-lieux.sortie.download', $etatLieuSortie->id) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-download me-1"></i> Télécharger/Sortie
                                        </a>
                                    @else
                                        <a href="#" 
                                            class="btn btn-primary btn-sm btn-etat-entree"
                                            data-locataire-id="{{ $locataire->id }}"
                                            data-target-url="{{ route('etat.sortie', $locataire->id) }}">
                                            <i class="fas fa-clipboard-check me-1"></i> État à la sortie
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de vérification -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Vérification d'accès</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    Scanner le QR Code
                                </div>
                                <div class="card-body text-center">
                                    <div id="qrCodeContainer" style="min-height: 200px;">
                                        <!-- Le QR code sera affiché ici -->
                                    </div>
                                    <div id="reader" style="width: 100%;"></div>
                                    <button id="startScanner" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-camera me-1"></i> Démarrer le scanner
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    Ou entrer le code manuellement
                                </div>
                                <div class="card-body">
                                    <form id="verificationForm">
                                        @csrf
                                        <input type="hidden" name="locataire_id" id="locataire_id">
                                        <input type="hidden" name="generated_code" id="hiddenGeneratedCode">
                                        
                                        <div class="mb-3">
                                            <label for="inputCode" class="form-label">Code de vérification</label>
                                            <input type="text" class="form-control text-center" id="inputCode" 
                                                   name="verification_code" required 
                                                   style="letter-spacing: 2px; font-size: 1.2rem;">
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-check-circle me-2"></i> Vérifier
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclure SweetAlert2 et Html5Qrcode -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
$(document).ready(function() {
    let html5QrcodeScanner = null;
    let currentLocataireId = null;
    let currentTargetUrl = null;
    let generatedCode = null;

    // Gestion du clic sur le bouton "État à l'entrée"
    $('.btn-etat-entree').on('click', function(e) {
        e.preventDefault();
        currentLocataireId = $(this).data('locataire-id');
        currentTargetUrl = $(this).data('target-url');
        
        Swal.fire({
            title: 'Accès état des lieux',
            text: "Voulez-vous générer un code de vérification ?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, continuer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                generateVerificationCode();
            }
        });
    });

    function generateVerificationCode() {
    $.ajax({
        url: '{{ route("generate.verification.code") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            locataire_id: currentLocataireId
        },
        success: function(response) {
            if(response.success) {
                generatedCode = response.code;
                Swal.fire({
                    title: 'Code envoyé',
                    text: 'Le code de vérification a été envoyé par email au locataire.',
                    icon: 'success'
                }).then(() => {
                    showVerificationDialog(response.qr_code_base64);
                });
            } else {
                Swal.fire('Erreur', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Erreur', 'Une erreur est survenue', 'error');
        }
    });
}

    function showVerificationDialog(qrCodeBase64) {
        Swal.fire({
            title: 'Vérification d\'accès',
            html: `
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="text-center">
                            <div id="qrCodeContainer" class="my-3" style="display:none">
                                <img src="data:image/png;base64,${qrCodeBase64}" 
                                     alt="QR Code" class="img-fluid" style="max-width: 200px;">
                            </div>
                            <button id="startScannerBtn" class="btn btn-primary btn-sm">
                                <i class="fas fa-camera me-1"></i> Scanner le QR Code
                            </button>
                            <div id="scannerContainer" style="width: 100%; display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="text-center">
                            <p class="text-muted">Ou entrer le code manuellement</p>
                            <input type="text" id="manualCodeInput" class="form-control text-center" 
                                   style="letter-spacing: 2px; font-size: 1.2rem;" 
                                   placeholder="Entrez le code">
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Valider',
            cancelButtonText: 'Annuler',
            focusConfirm: false,
            preConfirm: () => {
                const code = $('#manualCodeInput').val();
                if (!code) {
                    Swal.showValidationMessage('Veuillez entrer ou scanner le code');
                    return false;
                }
                return code;
            },
            didOpen: () => {
                $('#startScannerBtn').on('click', startScanner);
            },
            willClose: () => {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().catch(err => {
                        console.error("Erreur lors de l'arrêt du scanner:", err);
                    });
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                verifyCode(result.value);
            }
        });
    }

    function startScanner() {
        $('#startScannerBtn').hide();
        $('#scannerContainer').show();
        
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                html5QrcodeScanner = new Html5Qrcode("scannerContainer");
                
                html5QrcodeScanner.start(
                    devices[0].id,
                    {
                        fps: 10,
                        qrbox: {width: 250, height: 250}
                    },
                    qrCodeMessage => {
                        // Code scanné avec succès
                        $('#manualCodeInput').val(qrCodeMessage);
                        html5QrcodeScanner.stop();
                        verifyCode(qrCodeMessage);
                    },
                    errorMessage => {
                        console.error(errorMessage);
                    }
                ).catch(err => {
                    console.error(err);
                    Swal.fire('Erreur', 'Impossible de démarrer le scanner', 'error');
                });
            } else {
                Swal.fire('Erreur', 'Aucune caméra trouvée', 'error');
            }
        });
    }

    function verifyCode(code) {
        if (code !== generatedCode) {
            Swal.fire('Erreur', 'Le code saisi est incorrect', 'error');
            return;
        }
        
        $.ajax({
            url: '{{ route("verify.code") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                locataire_id: currentLocataireId,
                verification_code: code,
                generated_code: generatedCode
            },
            success: function(response) {
                if(response.success) {
                    window.location.href = currentTargetUrl;
                } else {
                    Swal.fire('Erreur', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Erreur', 'Une erreur est survenue', 'error');
            }
        });
    }
});
</script>
@endsection