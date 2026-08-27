@extends('layouts.app')

@section('title', 'Modifier commerçant')

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier commerçant') }}</h1>

    <form action="{{ route('commercants.update', $commercant['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('commercants._form', ['commercant' => $commercant])

        <div class="border-top pt-3 mb-3">
            @if ($commercant['user_id'])
                <p class="text-muted mb-0">{{ __('Ce commerçant a déjà un compte de connexion.') }}</p>
            @else
                <div class="form-check mb-2">
                    <input type="checkbox" name="creer_compte" value="1" class="form-check-input" id="creer_compte" {{ old('creer_compte') ? 'checked' : '' }}>
                    <label class="form-check-label" for="creer_compte">{{ __('Créer un compte de connexion pour ce commerçant') }}</label>
                </div>
                <label class="form-label">{{ __('Email de connexion') }}</label>
                <input type="email" name="email_connexion" class="form-control" value="{{ old('email_connexion') }}">
                <div class="form-text">{{ __("Un lien d'activation sera envoyé à cette adresse : la personne choisira elle-même son mot de passe.") }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('commercants.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
