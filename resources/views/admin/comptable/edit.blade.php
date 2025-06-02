@extends('admin.layouts.template')
@section('content')
<div class="col-12 grid-margin stretch-card mb-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-center">Modifier les informations de {{ $comptable->name.' '.$comptable->prenom }}</h4>
            <p class="card-description text-center">Pour modifier cet utilisateur, veuillez renseigner toutes les informations demandées ci-dessous</p>
            
            <form class="forms-sample" action="{{ route('accounting.update.admin', $comptable->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <fieldset style="border: 2px solid black; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                    <legend style="font-size: 1.5em; font-weight: bold;">Informations de l'utilisateur</legend>
                    <!-- Section 1: Informations de base -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->name }}" placeholder="Nom" name="name">
                                @error('name')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->prenom }}" placeholder="Prénom" name="prenom">
                                @error('prenom')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->email }}" placeholder="Email" name="email">
                                @error('email')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Autres informations -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Lieu de résidence</label>
                                <input type="text" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->commune }}" placeholder="Commune" name="commune">
                                @error('commune')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Contact</label>
                                <input type="number" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->contact }}" placeholder="Contact" name="contact">
                                @error('contact')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date de naissance</label>
                                <input type="date" style="border: 1px solid black; border-radius: 5px;" class="form-control" value="{{ $comptable->date_naissance }}" name="date_naissance">
                                @error('date_naissance')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type d'agent</label>
                                <select class="form-control" style="border: 1px solid black; border-radius: 5px;" name="user_type">
                                    <option value="Agent de recouvrement" {{ $comptable->user_type == 'Agent de recouvrement' ? 'selected' : '' }}>Agent de recouvrement</option>
                                    <option value="Comptable" {{ $comptable->user_type == 'Comptable' ? 'selected' : '' }}>Comptable</option>
                                </select>
                                @error('user_type')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </fieldset>
                
                <!-- Boutons de soumission -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" style="border: 1px solid black; border-radius: 5px;" class="btn btn-primary mr-2">Mêttre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection