@extends('proprietaire.layouts.template')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header text-center" style="background-color: #02245b; color: white;">
                    <h4>État des lieux pour {{ $locataire->name.' '.$locataire->prenom }}</h4>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('locataire.etatstore.owner', $locataire->id) }}">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Informations générales</h5>
                                <hr style="background-color: #02245b; height: 2px;">
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="adresse_bien">Adresse du bien</label>
                                    <input type="text" class="form-control" id="adresse_bien" name="adresse_bien" value="{{ old('adresse_bien',$locataire->bien->commune) }}" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type_bien">Type de bien</label>
                                    <select class="form-control" id="type_bien" name="type_bien">
                                        <option value="">Sélectionnez</option>
                                        <option value="Appartement">Appartement</option>
                                        <option value="Maison">Maison</option>
                                        <option value="Bureau">Bureau</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lot">Etage / N° lot</label>
                                    <input type="text" class="form-control" id="lot" name="lot" value="{{ old('lot') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_etat">Date de l'état des lieux</label>
                                    <input type="date" class="form-control" id="date_etat" name="date_etat" value="{{ old('date_etat') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nature_etat">Nature de l'état</label>
                                    <select class="form-control" id="nature_etat" name="nature_etat">
                                        <option value="Entrée">Entrée</option>
                                        <option value="Sortie">Sortie</option>
                                        <option value="Intermédiaire">Intermédiaire</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nom_locataire">Nom du locataire</label>
                                    <input type="text" class="form-control" id="nom_locataire" name="nom_locataire" value="{{ old('nom_locataire', $locataire->name.' '.$locataire->prenom) }}" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nom_proprietaire">Nom du propriétaire</label>
                                    <div class="input-group">
                                       
                                        <input type="text" class="form-control" id="nom_proprietaire" name="nom_proprietaire" 
                                            value="{{ old('nom_proprietaire', 
                                                $locataire->proprietaire_id && $locataire->proprietaire->gestion != 'agence' 
                                                    ? $locataire->proprietaire->name.' '.$locataire->proprietaire->prenom 
                                                : 'Maelys-imo') }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="presence_partie">Présence des parties</label>
                                    <select class="form-control" id="presence_partie" name="presence_partie">
                                        <option value="Oui">Oui</option>
                                        <option value="Non">Non</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="etat_entre">État à l'entrée</label>
                                    <select class="form-control" id="etat_entre" name="etat_entre">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="etat_sorti">État à la sortie</label>
                                    <select class="form-control" id="etat_sorti" name="etat_sorti">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Relevés des compteurs</h5>
                                <hr style="background-color: #02245b; height: 2px;">
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="type_compteur">Type de compteur</label>
                                    <select class="form-control" id="type_compteur" name="type_compteur">
                                        <option value="Électricité">Électricité</option>
                                        <option value="Gaz">Gaz</option>
                                        <option value="Eau">Eau</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="numero_compteur">Numéro du compteur</label>
                                    <input type="text" class="form-control" id="numero_compteur" name="numero_compteur" value="{{ old('numero_compteur') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="releve_entre">Relevé à l'entrée</label>
                                    <input type="text" class="form-control" id="releve_entre" name="releve_entre" value="{{ old('releve_entre') }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="releve_sorti">Relevé à la sortie</label>
                                    <input type="text" class="form-control" id="releve_sorti" name="releve_sorti" value="{{ old('releve_sorti') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>État des lieux par pièce</h5>
                                <hr style="background-color: #02245b; height: 2px;">
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="sol">Sol</label>
                                    <select class="form-control" id="sol" name="sol">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="murs">Murs</label>
                                    <select class="form-control" id="murs" name="murs">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="plafond">Plafond</label>
                                    <select class="form-control" id="plafond" name="plafond">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="porte_entre">Porte</label>
                                    <select class="form-control" id="porte_entre" name="porte_entre">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="interrupteur">Interrupteurs</label>
                                    <select class="form-control" id="interrupteur" name="interrupteur">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="eclairage">Éclairage</label>
                                    <select class="form-control" id="eclairage" name="eclairage">
                                        <option value="Neuf">Neuf</option>
                                        <option value="Bon">Bon</option>
                                        <option value="Moyen">Moyen</option>
                                        <option value="Dégradé">Dégradé</option>
                                        <option value="Non Applicable">Non Applicable</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="remarque">Remarques</label>
                                    <textarea class="form-control" id="remarque" name="remarque" rows="3">{{ old('remarque') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    Enregistrer l'état des lieux
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection