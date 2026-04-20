@extends('admin.layouts.template')
@section('content')
    <div class="col-12 grid-margin stretch-card mb-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center">Ajout d'un nouveau commercial</h4>
                <p class="card-description text-center">Veuillez renseigner les informations ci-dessous</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form class="forms-sample" action="{{ route('admin.commercial.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <fieldset style="border: 2px solid #02245b; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <legend style="font-size: 1.2em; font-weight: bold; color: #02245b; padding: 0 10px;">Informations
                            Personnelles</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;"
                                        name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;"
                                        name="prenom" value="{{ old('prenom') }}" required>
                                    @error('prenom')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" style="border: 1px solid #ccc;"
                                        name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;"
                                        name="contact" value="{{ old('contact') }}" required>
                                    @error('contact')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Commune de résidence</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;"
                                        name="commune" value="{{ old('commune') }}" required>
                                    @error('commune')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de naissance</label>
                                    <input type="date" class="form-control" style="border: 1px solid #ccc;"
                                        name="date_naissance" value="{{ old('date_naissance') }}" required>
                                    @error('date_naissance')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Image de profil (Optionnel)</label>
                                    <input type="file" class="form-control" name="profile_image">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5">Enregistrer le Commercial</button>
                        <a href="{{ route('admin.commercial.index') }}" class="btn btn-light ml-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
