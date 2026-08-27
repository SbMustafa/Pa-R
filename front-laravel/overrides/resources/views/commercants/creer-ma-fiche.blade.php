@extends('layouts.app')

@section('title', __('Ma fiche'))

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">{{ __('Ma fiche adhérent') }}</h1>

            <div class="alert alert-info">
                {{ __("Aucune fiche commerçant n'est encore rattachée à votre compte. Complétez ce formulaire pour la créer.") }}
            </div>

            <form action="{{ route('commercants.mine.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Nom') }}</label>
                    <input type="text" name="nom" class="form-control" required
                        value="{{ old('nom', auth()->user()->name) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Adresse') }}</label>
                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">{{ __('Ville') }}</label>
                        <input type="text" name="ville" class="form-control" value="{{ old('ville') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ __('Code postal') }}</label>
                        <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('Téléphone') }}</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ __('SIRET') }}</label>
                        <input type="text" name="siret" class="form-control" value="{{ old('siret') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Créer ma fiche') }}</button>
            </form>
        </div>
    </div>
@endsection
