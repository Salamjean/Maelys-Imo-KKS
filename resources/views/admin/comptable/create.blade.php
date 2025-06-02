@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Ajout d'un comptable pour votre agence</h4>
            <p class="card-description text-center">Pour l'ajout d'un comptable, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('accounting.store.admin') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations du comptable</legend>
                <!-- Section 1: Informations de base -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nom du comptable</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Nom du comptable" name="name">
                            @error('name')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Prénom du comptable</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Prénom du comptable" name="prenom">
                            @error('prenom')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Email" name="email">
                            @error('email')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Détails du bien -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Lieu de résidence</label>
                            <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Commune" name="commune">
                            @error('commune')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Contact du comptable</label>
                            <input type="number" style="border: 1px solid black; border-radius: 5px;" class="form-control" placeholder="Contact du comptable" name="contact">
                            @error('contact')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date de naissance</label>
                            <input type="date" style="border: 1px solid black; border-radius: 5px;" class="form-control" name="date_naissance">
                            @error('date_naissance')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Type d'agent</label>
                            <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="user_type">
                                <option value="">Sélectionnez un type</option>
                                <option value="Agent de recouvrement">Agent de recouvrement</option>
                                <option value="Comptable">Comptable</option>
                            </select>
                            @error('user_type')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</fieldset>
@endsection