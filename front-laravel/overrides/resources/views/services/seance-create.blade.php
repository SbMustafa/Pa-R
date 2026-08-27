@extends('layouts.app')

@section('title', 'Nouvelle séance')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouvelle séance') }}</h1>

    @if (empty($services))
        <div class="alert alert-warning">
            {{ __('Aucun service actif :') }} <a href="{{ route('services.create') }}">{{ __("créez d'abord un service") }}</a>.
        </div>
    @else
        <form action="{{ route('services.seances.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
            @csrf
            @include('services._seance-form')
            <button type="submit" class="btn btn-success">{{ __('Ajouter au planning') }}</button>
            <a href="{{ route('services.planning') }}" class="btn btn-link">{{ __('Annuler') }}</a>
        </form>
    @endif
@endsection
