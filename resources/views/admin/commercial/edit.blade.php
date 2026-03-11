@extends('admin.layouts.template')
@section('content')
    <div class="col-12 grid-margin stretch-card mb-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center">Modifier le commercial : {{ $commercial->name }}</h4>

                <form class="forms-sample" action="{{ route('admin.commercial.update', $commercial->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <fieldset style="border: 2px solid #02245b; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <legend style="font-size: 1.2em; font-weight: bold; color: #02245b; padding: 0 10px;">Informations
                            Personnelles</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;" name="name"
                                        value="{{ $commercial->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;" name="prenom"
                                        value="{{ $commercial->prenom }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" style="border: 1px solid #ccc;" name="email"
                                        value="{{ $commercial->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;" name="contact"
                                        value="{{ $commercial->contact }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Commune de résidence</label>
                                    <input type="text" class="form-control" style="border: 1px solid #ccc;" name="commune"
                                        value="{{ $commercial->commune }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date de naissance</label>
                                    <input type="date" class="form-control" style="border: 1px solid #ccc;"
                                        name="date_naissance" value="{{ $commercial->date_naissance }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Image de profil</label>
                                    @if($commercial->profile_image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $commercial->profile_image) }}" width="100"
                                                class="rounded">
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" name="profile_image">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5">Mettre à jour</button>
                        <a href="{{ route('admin.commercial.index') }}" class="btn btn-light ml-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection