@extends('layouts.app')

@section('title', 'Mot de passe oublié')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h3 mb-3">{{ __('Mot de passe oublié') }}</h1>

            <form action="{{ route('password.email') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <p class="text-muted">
                    {{ __('Indiquez votre adresse email : vous recevrez un lien pour définir un nouveau mot de passe.') }}
                </p>

                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Envoyer le lien') }}</button>
            </form>

            <p class="text-center mt-3">
                <a href="{{ route('login') }}">{{ __('Retour à la connexion') }}</a>
            </p>
        </div>
    </div>
@endsection
