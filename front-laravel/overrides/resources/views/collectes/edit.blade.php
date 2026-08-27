@extends('layouts.app')

@section('title', 'Modifier la collecte')

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier la collecte') }}</h1>

    <form action="{{ route('collectes.update', $collecte['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('collectes._form', ['collecte' => $collecte])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('collectes.show', $collecte['id']) }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
