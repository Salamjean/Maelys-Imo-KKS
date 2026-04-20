@extends('agence.layouts.template')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header text-white" style="background-color: #02245b;">
                        <h4 class="mb-0">Nouveau Reversement</h4>
                    </div>

                    <div class="card-body">
                        <!-- Carte du solde disponible -->
                        <div
                            class="card mb-4 border-left-0 border-right-0 border-top-0 border-bottom-3 border-primary rounded-0">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Solde disponible</h6>
                                        <h3 class="font-weight-bold mb-0" style="color: #02245b">
                                            <span
                                                id="solde-disponible">{{ number_format($soldeDisponible, 0, ',', ' ') }}</span>
                                            FCFA
                                        </h3>
                                    </div>
                                    <div class="bg-primary-light rounded-circle p-3">
                                        <i class="mdi mdi-cash-multiple" style="color: #02245b"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('reversement.store.agence') }}" method="POST" id="reversement-form"
                            class="needs-validation" novalidate>
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Choix du canal de retrait --}}
                            <div class="mb-4">
                                <label class="font-weight-bold d-block mb-2">Mode de retrait</label>
                                <div class="d-flex gap-3">
                                    <div class="canal-card p-3 border rounded text-center flex-fill" id="card-virement"
                                        onclick="selectCanal('virement')"
                                        style="cursor:pointer; border-color:#02245b !important; background:#eef2ff;">
                                        <i class="mdi mdi-bank" style="font-size:2rem;color:#02245b"></i>
                                        <p class="mb-0 mt-1 font-weight-bold" style="color:#02245b">Virement bancaire</p>
                                        <small class="text-muted">Sous 24-48h</small>
                                    </div>
                                    <div class="canal-card p-3 border rounded text-center flex-fill" id="card-mobile"
                                        onclick="selectCanal('mobile_money')" style="cursor:pointer;">
                                        <i class="mdi mdi-cellphone" style="font-size:2rem;color:#6c757d"></i>
                                        <p class="mb-0 mt-1 font-weight-bold text-muted">Mobile Money</p>
                                        <small class="text-muted">Wave instantané</small>
                                    </div>
                                </div>
                                <input type="hidden" name="type_retrait" id="type_retrait" value="virement">
                            </div>

                            {{-- Section Virement bancaire --}}
                            <div id="section-virement">
                                <div class="form-row">
                                    <div class="col-md-6 mb-3">
                                        <label for="banque" class="font-weight-bold">Banque</label>
                                        <select class="form-control form-control-lg rounded-pill" id="banque"
                                            name="banque">
                                            <option value="">Sélectionnez une banque</option>
                                            @foreach ($ribs as $rib)
                                                <option value="{{ $rib->id }}" data-rib="{{ $rib->rib }}">
                                                    {{ $rib->banque }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="rib" class="font-weight-bold">RIB</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-lg rounded-pill"
                                                id="rib" name="rib" readonly>
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white rounded-pill"><i
                                                        class="mdi mdi-cards" style="color:#02245b"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section Mobile Money --}}
                            <div id="section-mobile" style="display:none;">
                                <div class="mobile-money-wrapper p-3 rounded mb-3"
                                    style="background:#f0f8ff;border:1.5px solid #bee3f8;">

                                    <p class="font-weight-bold mb-3" style="color:#02245b;font-size:.95rem;">
                                        <i class="mdi mdi-cellphone-wireless mr-1" style="color:#1AA3E8"></i> Choisissez
                                        votre réseau
                                    </p>

                                    <div class="row no-gutters">
                                        {{-- Wave --}}
                                        <div class="col-6 col-sm-3 pr-2 pb-2">
                                            <div class="reseau-card border rounded text-center py-3 px-2 h-100"
                                                id="card-wave" onclick="selectReseau('Wave')"
                                                style="cursor:pointer;border-color:#1AA3E8 !important;background:#e8f7fd;position:relative;">
                                                <span id="check-wave"
                                                    style="position:absolute;top:6px;right:8px;color:#1AA3E8;font-size:1rem;"><i
                                                        class="mdi mdi-check-circle"></i></span>
                                                <img src="{{ asset('assets/images/wave.png') }}" alt="Wave"
                                                    style="width:52px;height:52px;object-fit:contain;">
                                                <p class="mb-0 mt-2 font-weight-bold"
                                                    style="color:#1AA3E8;font-size:.85rem;">Wave</p>
                                                <span class="badge mt-1"
                                                    style="background:#1AA3E8;color:#fff;font-size:.65rem;">Sous 24h</span>
                                            </div>
                                        </div>
                                        {{-- Orange --}}
                                        <div class="col-6 col-sm-3 pr-2 pb-2">
                                            <div class="reseau-card border rounded text-center py-3 px-2 h-100"
                                                style="cursor:not-allowed;opacity:.45;">
                                                <img src="{{ asset('assets/images/orange.png') }}" alt="Orange"
                                                    style="width:52px;height:52px;object-fit:contain;">
                                                <p class="mb-0 mt-2 font-weight-bold text-muted"
                                                    style="font-size:.85rem;">Orange</p>
                                                <span class="badge badge-secondary mt-1"
                                                    style="font-size:.65rem;">Bientôt</span>
                                            </div>
                                        </div>
                                        {{-- Moov --}}
                                        <div class="col-6 col-sm-3 pr-2 pb-2">
                                            <div class="reseau-card border rounded text-center py-3 px-2 h-100"
                                                style="cursor:not-allowed;opacity:.45;">
                                                <img src="{{ asset('assets/images/moov.png') }}" alt="Moov"
                                                    style="width:52px;height:52px;object-fit:contain;">
                                                <p class="mb-0 mt-2 font-weight-bold text-muted"
                                                    style="font-size:.85rem;">Moov</p>
                                                <span class="badge badge-secondary mt-1"
                                                    style="font-size:.65rem;">Bientôt</span>
                                            </div>
                                        </div>
                                        {{-- MTN --}}
                                        <div class="col-6 col-sm-3 pb-2">
                                            <div class="reseau-card border rounded text-center py-3 px-2 h-100"
                                                style="cursor:not-allowed;opacity:.45;">
                                                <img src="{{ asset('assets/images/mtn.png') }}" alt="MTN"
                                                    style="width:52px;height:52px;object-fit:contain;">
                                                <p class="mb-0 mt-2 font-weight-bold text-muted"
                                                    style="font-size:.85rem;">MTN</p>
                                                <span class="badge badge-secondary mt-1"
                                                    style="font-size:.65rem;">Bientôt</span>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="reseau_mobile" id="reseau_mobile" value="Wave">

                                    {{-- Numéro --}}
                                    <div class="mt-3">
                                        <label for="numero_mobile" class="font-weight-bold" style="font-size:.9rem;">
                                            <i class="mdi mdi-cellphone mr-1" style="color:#1AA3E8"></i>
                                            Numéro <span id="label-reseau">Wave</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"
                                                    style="background:#1AA3E8;color:#fff;border-color:#1AA3E8;border-radius:.5rem 0 0 .5rem;">
                                                    <i class="mdi mdi-phone"></i>
                                                </span>
                                            </div>
                                            <input type="tel" class="form-control"
                                                style="border-radius:0 .5rem .5rem 0;border-color:#bee3f8;"
                                                id="numero_mobile" name="numero_mobile" placeholder="07 XX XX XX XX"
                                                maxlength="15" value="{{ old('numero_mobile') }}">
                                        </div>
                                        @error('numero_mobile')
                                            <small class="text-danger"><i
                                                    class="mdi mdi-alert-circle mr-1"></i>{{ $message }}</small>
                                        @enderror
                                        <small class="text-muted">Le montant sera envoyé sur ce numéro Wave sous
                                            24h.</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Montant + date communs --}}
                            <div class="form-row mt-3">
                                <div class="col-md-6 mb-3">
                                    <label for="montant" class="font-weight-bold">Montant (FCFA)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text rounded-left-pill">FCFA</span>
                                        </div>
                                        <input type="number" step="1" class="form-control rounded-right-pill"
                                            id="montant" name="montant" required min="100"
                                            max="{{ $soldeDisponible }}" value="{{ old('montant') }}"
                                            placeholder="5000...">
                                        @error('montant')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_reversement" class="font-weight-bold">Date souhaitée</label>
                                    <input type="date" class="form-control form-control-lg rounded-pill"
                                        id="date_reversement" name="date_reversement" required
                                        value="{{ old('date_reversement', date('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-lg rounded-pill px-5 py-3 text-white shadow-sm"
                                    style="background-color: #02245b">
                                    <i class="mdi mdi-check-circle mr-2"></i> Retirer mon argent
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section des 3 derniers reversements -->
                <div class="col-12 shadow-sm border-0 mt-4">
                    <div class="card-header text-white" style="background-color: #02245b;">
                        <h5 class="mb-0">Derniers Reversements</h5>
                    </div>
                    <div class="card-body">
                        @if ($lastReversements->count() > 0)
                            <div class="list-group">
                                @foreach ($lastReversements as $reversement)
                                    <div
                                        class="list-group-item list-group-item-action flex-column align-items-start mb-2 border-0 shadow-sm rounded-lg">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1 font-weight-bold" style="color: #02245b">
                                                {{ $reversement->rib->banque ?? 'Banque non renseignée' }}</h6>
                                            <small
                                                class="text-muted">{{ $reversement->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div>
                                                <span class="badge badge-primary">{{ $reversement->reference }}</span>
                                                <small
                                                    class="text-muted ml-2">{{ $reversement->rib->rib ?? 'Rib non renseignée' }}</small>
                                            </div>
                                            <h5 class="mb-0 font-weight-bold" style="color: #02245b">
                                                {{ number_format($reversement->montant, 0, ',', ' ') }} FCFA</h5>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('reversement.index') }}" class="btn btn-sm text-white"
                                    style="background-color: #02245b">
                                    Voir tout l'historique <i class="mdi mdi-chevron-right"></i>
                                </a>
                            </div>
                        @else
                            <div class="alert alert-info mb-0 text-center">
                                Aucun reversement effectué pour le moment.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles additionnels -->
    <style>
        .bg-primary-light {
            background-color: rgba(2, 36, 91, 0.1);
        }

        .rounded-left-pill {
            border-top-left-radius: 50rem !important;
            border-bottom-left-radius: 50rem !important;
        }

        .rounded-right-pill {
            border-top-right-radius: 50rem !important;
            border-bottom-right-radius: 50rem !important;
        }

        .card {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-control-lg {
            height: calc(2.5em + 1rem + 2px);
        }

        .list-group-item {
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            transform: translateX(5px);
        }

        .badge {
            background-color: #02245b;
            font-size: 0.8em;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Sélection du canal de retrait
        function selectCanal(canal) {
            document.getElementById('type_retrait').value = canal;
            const cardVirement = document.getElementById('card-virement');
            const cardMobile = document.getElementById('card-mobile');
            const secVirement = document.getElementById('section-virement');
            const secMobile = document.getElementById('section-mobile');
            if (canal === 'virement') {
                cardVirement.style.background = '#eef2ff';
                cardVirement.style.borderColor = '#02245b';
                cardMobile.style.background = '#fff';
                cardMobile.style.borderColor = '#dee2e6';
                secVirement.style.display = '';
                secMobile.style.display = 'none';
            } else {
                cardMobile.style.background = '#e8f7fd';
                cardMobile.style.borderColor = '#1AA3E8';
                cardVirement.style.background = '#fff';
                cardVirement.style.borderColor = '#dee2e6';
                secVirement.style.display = 'none';
                secMobile.style.display = '';
            }
        }

        // Sélection réseau mobile (seul Wave cliquable pour l'instant)
        function selectReseau(reseau) {
            document.getElementById('reseau_mobile').value = reseau;
            document.getElementById('card-wave').style.borderColor = '#1AA3E8';
            document.getElementById('card-wave').style.background = '#e8f7fd';
        }

        // RIB auto-fill
        document.getElementById('banque').addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            document.getElementById('rib').value = opt.getAttribute('data-rib') || '';
        });

        // Flash messages
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: '{!! addslashes(session('success')) !!}',
                    confirmButtonColor: '#02245b',
                });
            @endif
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: '{!! addslashes(session('error')) !!}',
                    confirmButtonColor: '#02245b',
                });
            @endif

            // Confirmation avant soumission
            const form = document.getElementById('reversement-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!form.checkValidity()) {
                        form.classList.add('was-validated');
                        return;
                    }
                    const canal = document.getElementById('type_retrait').value;
                    const label = canal === 'mobile_money' ? 'retrait Mobile Money' : 'virement bancaire';
                    Swal.fire({
                        title: 'Confirmer le retrait',
                        text: 'Confirmer la demande de ' + label + ' ?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#02245b',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Oui, confirmer',
                        cancelButtonText: 'Annuler'
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            }
        });
    </script>
@endsection
