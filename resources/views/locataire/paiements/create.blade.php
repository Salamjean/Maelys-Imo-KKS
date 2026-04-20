@extends('locataire.layouts.template')

@section('content')
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Paiement du loyer -
                        {{ $mois_couvert_display }}</h4>
                    <span class="badge bg-light text-dark" style="font-size: 18px">Montant :
                        <strong>{{ number_format($montant, 0, ',', ' ') }} FCFA</strong></span>
                </div>
            </div>

            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Info paiement --}}
                <div class="alert alert-info border-start border-5 border-info p-4 mb-4">
                    <div class="w-100">
                        <h5 class="alert-heading fw-bold mb-3"><i class="fas fa-info-circle text-info me-2"></i>Informations
                            de paiement</h5>
                        <div class="row justify-content-center">
                            <div class="col-md-5 mb-2 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                                    <div><strong class="d-block">Période</strong><span>{{ $mois_couvert_display }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-wallet me-2 text-info"></i>
                                    <div><strong
                                            class="d-block">Montant</strong><span>{{ number_format($montant, 0, ',', ' ') }}
                                            FCFA</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- ÉTAPE 1 : Choix méthode de paiement --}}
                {{-- ============================================================ --}}
                <h5 class="fw-bold mb-3"><i class="fas fa-credit-card me-2 text-primary"></i>Choisissez votre méthode de
                    paiement</h5>
                <div class="row g-3 mb-4" id="methodCards">

                    <div class="col-12 col-md-6">
                        <div class="method-card" data-method="mobile_money" onclick="selectMethod(this)">
                            <div class="method-icon" style="background: linear-gradient(135deg, #1a8cff, #0057e0);">
                                <i class="fas fa-mobile-alt fa-2x text-white"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-title">Mobile Money</div>
                                <div class="method-sub">Wave, Orange, Moov, MTN</div>
                            </div>
                            <i class="fas fa-chevron-right method-arrow"></i>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="method-card" data-method="virement" onclick="selectMethod(this)">
                            <div class="method-icon" style="background: linear-gradient(135deg, #02245b, #0066cc);">
                                <i class="fas fa-university fa-2x text-white"></i>
                            </div>
                            <div class="method-info">
                                <div class="method-title">Virement Bancaire</div>
                                <div class="method-sub">Envoyer une preuve de virement</div>
                            </div>
                            <i class="fas fa-chevron-right method-arrow"></i>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- SECTION MOBILE MONEY : Choix réseau --}}
                {{-- ============================================================ --}}
                <div id="mobileMoneySection" style="display:none;">
                    <div class="text-center mb-4">
                        <button class="btn btn-outline-secondary btn-back-method" onclick="resetMethod()">
                            <i class="fas fa-arrow-left me-2"></i> Changer de méthode
                        </button>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="fas fa-signal me-2 text-primary"></i>Choisissez votre réseau</h5>

                    <div class="row g-3 mb-4">

                        {{-- WAVE (actif) --}}
                        <div class="col-6 col-md-3">
                            <div class="network-card active-network" data-network="wave" onclick="selectNetwork(this)">
                                <img src="{{ asset('assets/images/wave.png') }}" alt="Wave" class="network-img">
                                <div class="network-name">Wave CI</div>
                                <div class="network-badge available">Disponible</div>
                            </div>
                        </div>

                        {{-- ORANGE MONEY (grisé) --}}
                        <div class="col-6 col-md-3">
                            <div class="network-card disabled-network" title="Bientôt disponible">
                                <img src="{{ asset('assets/images/orange.png') }}" alt="Orange Money" class="network-img">
                                <div class="network-name">Orange Money</div>
                                <div class="network-badge coming-soon">Bientôt</div>
                            </div>
                        </div>

                        {{-- MOOV MONEY (grisé) --}}
                        <div class="col-6 col-md-3">
                            <div class="network-card disabled-network" title="Bientôt disponible">
                                <img src="{{ asset('assets/images/moov.png') }}" alt="Moov Money" class="network-img">
                                <div class="network-name">Moov Money</div>
                                <div class="network-badge coming-soon">Bientôt</div>
                            </div>
                        </div>

                        {{-- MTN MONEY (grisé) --}}
                        <div class="col-6 col-md-3">
                            <div class="network-card disabled-network" title="Bientôt disponible">
                                <img src="{{ asset('assets/images/mtn.png') }}" alt="MTN Money" class="network-img">
                                <div class="network-name">MTN Money</div>
                                <div class="network-badge coming-soon">Bientôt</div>
                            </div>
                        </div>
                    </div>

                    {{-- Bouton payer Wave (apparaît après sélection réseau) --}}
                    <div id="wavePaySection" style="display:none;">
                        <button id="wavePayBtn" class="btn btn-lg w-100"
                            style="background: linear-gradient(135deg, #1a8cff 0%, #0057e0 100%); color: white; font-weight: 600;">
                            <img src="{{ asset('assets/images/wave.png') }}" alt="Wave"
                                style="height:24px; margin-right:8px; border-radius:4px; object-fit:contain;">
                            Payer {{ number_format($montant, 0, ',', ' ') }} FCFA avec Wave
                        </button>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- SECTION VIREMENT BANCAIRE --}}
                {{-- ============================================================ --}}
                <div id="bankTransferSection" style="display:none;">
                    <div class="text-center mb-4">
                        <button class="btn btn-outline-secondary btn-back-method" onclick="resetMethod()">
                            <i class="fas fa-arrow-left me-2"></i> Changer de méthode
                        </button>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="fas fa-file-upload me-2 text-secondary"></i>Preuve de virement</h5>
                    <form id="bankTransferForm" method="POST"
                        action="{{ route('locataire.paiements.store', $locataire) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="mois_couvert" value="{{ $mois_couvert }}">
                        <input type="hidden" name="methode_paiement" value="virement">
                        <input type="hidden" name="transaction_id" value="VIR_{{ uniqid() }}">

                        <div class="mb-3">
                            <label for="proofFile" class="form-label fw-bold">Joindre votre preuve (PDF ou image)</label>
                            <input class="form-control form-control-lg" type="file" id="proofFile" name="proof_file"
                                accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Taille maximale : 2MB — PDF, JPG, JPEG, PNG</div>
                        </div>

                        <button type="submit" class="btn btn-lg w-100"
                            style="background: linear-gradient(135deg, #02245b 0%, #0066cc 100%); color: white; font-weight: 600;">
                            <i class="fas fa-paper-plane me-2"></i> Envoyer la preuve
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function selectMethod(card) {
            document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected-method'));
            card.classList.add('selected-method');
            document.getElementById('methodCards').style.display = 'none';
            const method = card.dataset.method;
            if (method === 'mobile_money') {
                document.getElementById('mobileMoneySection').style.display = 'block';
            } else {
                document.getElementById('bankTransferSection').style.display = 'block';
            }
        }

        function resetMethod() {
            document.getElementById('mobileMoneySection').style.display = 'none';
            document.getElementById('bankTransferSection').style.display = 'none';
            document.getElementById('wavePaySection').style.display = 'none';
            document.querySelectorAll('.network-card').forEach(c => c.classList.remove('selected-network'));
            document.querySelectorAll('.method-card').forEach(c => c.classList.remove('selected-method'));
            document.getElementById('methodCards').style.display = 'flex';
        }

        function selectNetwork(card) {
            if (card.classList.contains('disabled-network')) return;
            document.querySelectorAll('.network-card').forEach(c => c.classList.remove('selected-network'));
            card.classList.add('selected-network');
            const network = card.dataset.network;
            document.getElementById('wavePaySection').style.display = (network === 'wave') ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('wavePayBtn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Redirection vers Wave...',
                    html: 'Veuillez patienter.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('{{ route('wave.initiate', $locataire) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            mois_couvert: '{{ $mois_couvert }}'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.close();
                        if (data.success && data.wave_launch_url) {
                            window.open(data.wave_launch_url, '_blank');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: data.error ??
                                    'Impossible d\'initialiser le paiement Wave.',
                                confirmButtonColor: '#02245b'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur réseau',
                            text: 'Veuillez réessayer.',
                            confirmButtonColor: '#02245b'
                        });
                    });
            });

            document.getElementById('bankTransferForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    title: 'Confirmation',
                    text: 'Envoyer cette preuve de virement ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#02245b',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, envoyer',
                    cancelButtonText: 'Annuler'
                }).then(result => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Envoi en cours...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                setTimeout(() => form.submit(), 500);
                            }
                        });
                    }
                });
            });
        });
    </script>

    <style>
        .card {
            border-radius: 15px;
            overflow: hidden;
            border: none;
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        /* Cartes méthode */
        .method-card {
            display: flex;
            align-items: center;
            gap: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 18px 20px;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
        }

        .method-card:hover,
        .method-card.selected-method {
            border-color: #1a8cff;
            box-shadow: 0 0 0 3px rgba(26, 140, 255, 0.18);
            transform: translateY(-2px);
        }

        .method-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .method-info {
            flex: 1;
        }

        .method-title {
            font-weight: 700;
            font-size: 16px;
            color: #222;
        }

        .method-sub {
            font-size: 12px;
            color: #888;
        }

        .method-arrow {
            color: #bbb;
        }

        /* Cartes réseau */
        .network-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 18px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
        }

        .network-card.active-network:hover,
        .network-card.selected-network {
            border-color: #1a8cff;
            box-shadow: 0 0 0 3px rgba(26, 140, 255, 0.2);
            transform: translateY(-3px);
        }

        .network-card.disabled-network {
            opacity: 0.42;
            cursor: not-allowed;
            filter: grayscale(80%);
        }

        .network-img {
            width: 55px;
            height: 55px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .network-name {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 5px;
        }

        .network-badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 20px;
            display: inline-block;
        }

        .network-badge.available {
            background: #d4edda;
            color: #155724;
        }

        .network-badge.coming-soon {
            background: #f0f0f0;
            color: #777;
        }

        /* Bouton retour méthode */
        .btn-back-method {
            border: 2px solid #adb5bd;
            border-radius: 25px;
            padding: 8px 24px;
            font-weight: 600;
            font-size: 14px;
            color: #555;
            background: #fff;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-back-method:hover {
            background: #f0f4ff;
            border-color: #1a8cff;
            color: #1a8cff;
            transform: translateX(-2px);
        }
    </style>
@endsection
