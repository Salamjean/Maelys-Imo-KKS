@extends('home.pages.layouts.template')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Connexion requise</h3>
                    <p class="text-center">Veuillez vous connecter pour souscrire à notre service</p>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{route('owner.suscribe.authenticate')}}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email">Adresse Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password">Mot de passe</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary w-100">
                                Se connecter
                            </button>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-center mt-3">
                                <a href="{{ route('password.request') }}">
                                    Mot de passe oublié?
                                </a>
                            </div>
                        @endif
                    </form>

                    <div class="text-center mt-4">
                        <p>Vous n'avez pas de compte? <a href="#">S'inscrire</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection