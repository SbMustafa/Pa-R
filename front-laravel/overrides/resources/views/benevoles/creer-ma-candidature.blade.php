@extends('layouts.app')

@section('title', __('Ma candidature'))

@php
    $ancienesCapacites = old('capacites', []);
    $capacitesSelectionnees = is_array($ancienesCapacites) ? $ancienesCapacites : [];
    $capacitesDisponibles = ['Chauffeur', 'Cuisinier', 'Plombier', 'Électricien', 'Bricolage'];
@endphp

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">{{ __('Ma candidature bénévole') }}</h1>

            <div class="alert alert-info">
                {{ __("Aucune candidature n'est encore rattachée à votre compte. Complétez ce formulaire pour proposer vos services à l'association.") }}
            </div>

            <form action="{{ route('benevoles.mine.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Téléphone') }}</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">{{ __('Vos capacités') }}</label>
                    @foreach ($capacitesDisponibles as $capacite)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="capacites[]" class="form-check-input" value="{{ $capacite }}"
                                id="cap-{{ $loop->index }}"
                                {{ in_array($capacite, $capacitesSelectionnees) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cap-{{ $loop->index }}">{{ __($capacite) }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Disponibilités') }}</label>
                    <input type="text" name="disponibilites" class="form-control"
                        placeholder="{{ __('Ex : lundi, mercredi soir, week-ends') }}"
                        value="{{ old('disponibilites') }}">
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Déposer ma candidature') }}</button>
            </form>
        </div>
    </div>
@endsection
