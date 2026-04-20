@extends($layout ?? 'admin.layouts.template')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- En-tête --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header text-white d-flex align-items-center justify-content-between"
                        style="background-color: #02245b;">
                        <div>
                            <h4 class="mb-0"><i class="mdi mdi-home-export-outline mr-2"></i>Déménagement —
                                {{ $locataire->name }} {{ $locataire->prenom }}</h4>
                            <small>Bien : {{ $locataire->bien->type ?? '' }} — {{ $locataire->bien->commune ?? '' }}</small>
                        </div>
                        <a href="{{ url()->previous() }}" class="btn btn-light btn-sm"><i class="mdi mdi-arrow-left"></i>
                            Retour</a>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Résumé location en cours --}}
                @if ($historiqueEnCours)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light"><strong>📋 Location en cours</strong></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3"><strong>Entrée
                                        :</strong><br>{{ $historiqueEnCours->date_entree->format('d/m/Y') }}</div>
                                <div class="col-md-3"><strong>Agence
                                        :</strong><br>{{ $historiqueEnCours->agence->name ?? '—' }}</div>
                                <div class="col-md-3"><strong>Propriétaire
                                        :</strong><br>{{ $historiqueEnCours->proprietaire ? $historiqueEnCours->proprietaire->name . ' ' . $historiqueEnCours->proprietaire->prenom : '—' }}
                                </div>
                                <div class="col-md-3"><strong>État d'entrée :</strong><br>
                                    @if ($historiqueEnCours->etatLieuEntree)
                                        <span class="badge badge-success">✔ Rempli</span>
                                    @else
                                        <span class="badge badge-warning">Non rempli</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Formulaire état de sortie + confirmation déménagement --}}
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <strong><i class="mdi mdi-clipboard-check-outline mr-1"></i>État des lieux de sortie & Confirmation
                            du déménagement</strong>
                    </div>
                    <div class="card-body">
                        <form action="{{ route($routePrefix . 'demenagement.confirmer', $locataire->id) }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Date de sortie *</label>
                                    <input type="date" name="date_sortie"
                                        class="form-control @error('date_sortie') is-invalid @enderror"
                                        value="{{ old('date_sortie', now()->toDateString()) }}" required>
                                    @error('date_sortie')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Motif du déménagement *</label>
                                    <select name="motif_sortie"
                                        class="form-control @error('motif_sortie') is-invalid @enderror" required>
                                        <option value="">-- Choisir --</option>
                                        <option value="Fin de contrat"
                                            {{ old('motif_sortie') == 'Fin de contrat' ? 'selected' : '' }}>Fin de contrat
                                        </option>
                                        <option value="Déménagement volontaire"
                                            {{ old('motif_sortie') == 'Déménagement volontaire' ? 'selected' : '' }}>
                                            Déménagement volontaire</option>
                                        <option value="Expulsion"
                                            {{ old('motif_sortie') == 'Expulsion' ? 'selected' : '' }}>Expulsion</option>
                                        <option value="Décès" {{ old('motif_sortie') == 'Décès' ? 'selected' : '' }}>Décès
                                        </option>
                                        <option value="Autre" {{ old('motif_sortie') == 'Autre' ? 'selected' : '' }}>Autre
                                        </option>
                                    </select>
                                    @error('motif_sortie')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="font-weight-bold">Présence des parties</label>
                                    <select name="presence_partie" class="form-control">
                                        <option value="oui" {{ old('presence_partie') == 'oui' ? 'selected' : '' }}>Oui
                                        </option>
                                        <option value="non" {{ old('presence_partie') == 'non' ? 'selected' : '' }}>Non
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Parties communes</h5>
                            @php
                                $elements = [
                                    'sol' => 'Sol',
                                    'murs' => 'Murs',
                                    'plafond' => 'Plafond',
                                    'porte_entre' => 'Porte d\'entrée',
                                    'interrupteur' => 'Interrupteurs',
                                    'robinet' => 'Robinets',
                                    'lavabo' => 'Lavabo',
                                    'douche' => 'Douche',
                                ];
                                $etats = ['Bon état', 'État moyen', 'Mauvais état', 'Absent'];
                            @endphp
                            <div class="row">
                                @foreach ($elements as $key => $label)
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-2">
                                            <label class="font-weight-bold">{{ $label }}</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <select name="parties_communes[{{ $key }}]"
                                                        class="form-control form-control-sm">
                                                        <option value="">-- État --</option>
                                                        @foreach ($etats as $etat)
                                                            <option value="{{ $etat }}"
                                                                {{ old("parties_communes.$key") == $etat ? 'selected' : '' }}>
                                                                {{ $etat }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <input type="text"
                                                        name="parties_communes[observation_{{ $key }}]"
                                                        class="form-control form-control-sm" placeholder="Observation"
                                                        value="{{ old("parties_communes.observation_$key") }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <hr>
                            <h5 class="mb-3">Chambres <small class="text-muted">(basées sur l'état d'entrée)</small></h5>
                            @php
                                $chambresBase = [];
                                if ($etatEntree && $etatEntree->chambres) {
                                    $chambresBase = $etatEntree->chambres;
                                } else {
                                    $chambresBase = [['nom' => 'Chambre 1']];
                                }
                            @endphp
                            <div id="chambres-container">
                                @foreach ($chambresBase as $i => $chambre)
                                    <div class="border rounded p-3 mb-3">
                                        <input type="hidden" name="chambres[{{ $i }}][nom]"
                                            value="{{ $chambre['nom'] }}">
                                        <h6 class="font-weight-bold">{{ $chambre['nom'] }}</h6>
                                        <div class="row">
                                            @foreach (['sol' => 'Sol', 'murs' => 'Murs', 'plafond' => 'Plafond'] as $ck => $cl)
                                                <div class="col-md-4 mb-2">
                                                    <label>{{ $cl }}</label>
                                                    <select name="chambres[{{ $i }}][{{ $ck }}]"
                                                        class="form-control form-control-sm">
                                                        <option value="">-- État --</option>
                                                        @foreach ($etats as $etat)
                                                            <option value="{{ $etat }}">{{ $etat }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text"
                                                        name="chambres[{{ $i }}][observation_{{ $ck }}]"
                                                        class="form-control form-control-sm mt-1" placeholder="Observation">
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="font-weight-bold">Nombre de clés remises *</label>
                                    <input type="number" name="nombre_cle" class="form-control"
                                        value="{{ old('nombre_cle', 0) }}" min="0" required>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <strong>⚠ Attention :</strong> Cette action est irréversible. Le bien sera remis en
                                <strong>Disponible</strong> et le locataire passera en <strong>Inactif</strong>.
                            </div>

                            <button type="submit" class="btn btn-danger btn-lg"
                                onclick="return confirm('Confirmer le déménagement de {{ $locataire->name }} {{ $locataire->prenom }} ?')">
                                <i class="mdi mdi-check-bold mr-1"></i>Confirmer le déménagement
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg ml-2">Annuler</a>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
