@extends('layouts.app')

@section('title', 'Nouvelle collecte')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouvelle collecte') }}</h1>

    <form action="{{ route('collectes.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('collectes._form')
        <button type="submit" class="btn btn-success">{{ __('Planifier la collecte') }}</button>
        <a href="{{ route('collectes.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
