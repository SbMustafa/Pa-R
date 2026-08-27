@extends('layouts.app')

@section('title', 'Nouveau bénévole')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouveau bénévole') }}</h1>

    <form action="{{ route('benevoles.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('benevoles._form')

        <div class="border-top pt-3 mb-3">
            <div class="form-check mb-2">
                <input type="checkbox" name="creer_compte" value="1" class="form-check-input" id="creer_compte" {{ old('creer_compte') ? 'checked' : '' }}>
                <label class="form-check-label" for="creer_compte">{{ __('Créer un compte de connexion pour ce bénévole') }}</label>
            </div>
            <label class="form-label">{{ __('Email de connexion') }}</label>
            <input type="email" name="email_connexion" class="form-control" value="{{ old('email_connexion') }}">
            <div class="form-text">{{ __("Un lien d'activation sera envoyé à cette adresse : la personne choisira elle-même son mot de passe.") }}</div>
        </div>

        <button type="submit" class="btn btn-success">{{ __('Créer') }}</button>
        <a href="{{ route('benevoles.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
