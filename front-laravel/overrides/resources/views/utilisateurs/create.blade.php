@extends('layouts.app')

@section('title', 'Nouvel administrateur')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">{{ __('Nouvel administrateur') }}</h1>

            <form action="{{ route('utilisateurs.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Nom') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <p class="text-muted" style="font-size:.87rem">
                    {{ __("Aucun mot de passe n'est saisi ici : la personne reçoit par email un lien à usage unique et choisit elle-même son mot de passe.") }}
                </p>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">{{ __('Créer le compte') }}</button>
                    <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>

            <p class="text-muted mt-3" style="font-size:.87rem">
                {{ __('Pour créer un commerçant ou un bénévole, passez par leur page respective : le compte y est créé en même temps que sa fiche.') }}
            </p>
        </div>
    </div>
@endsection
