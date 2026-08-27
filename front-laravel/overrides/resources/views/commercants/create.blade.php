@extends('layouts.app')

@section('title', 'Nouveau commerçant')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouveau commerçant') }}</h1>

    <form action="{{ route('commercants.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('commercants._form')

        <div class="border-top pt-3 mb-3">
            <div class="form-check mb-2">
                <input type="checkbox" name="creer_compte" value="1" class="form-check-input" id="creer_compte" {{ old('creer_compte') ? 'checked' : '' }}>
                <label class="form-check-label" for="creer_compte">{{ __('Créer un compte de connexion pour ce commerçant') }}</label>
            </div>
            <label class="form-label">{{ __('Email de connexion') }}</label>
            <input type="email" name="email_connexion" class="form-control" value="{{ old('email_connexion') }}">
            <div class="form-text">{{ __("Un lien d'activation sera envoyé à cette adresse : la personne choisira elle-même son mot de passe.") }}</div>
        </div>

        <button type="submit" class="btn btn-success">{{ __('Créer') }}</button>
        <a href="{{ route('commercants.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
