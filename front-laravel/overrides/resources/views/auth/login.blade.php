@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h3 mb-3">{{ __('Connexion') }}</h1>

            <form action="{{ route('login') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Mot de passe') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">{{ __('Se souvenir de moi') }}</label>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Se connecter') }}</button>

                <p class="text-center mt-3 mb-0">
                    <a href="{{ route('password.request') }}">{{ __('Mot de passe oublié ?') }}</a>
                </p>
            </form>

            <p class="text-center mt-3">
                {{ __('Pas encore de compte ?') }} <a href="{{ route('register') }}">{{ __("S'inscrire") }}</a>
            </p>
        </div>
    </div>
@endsection
