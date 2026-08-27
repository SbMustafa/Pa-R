@extends('layouts.app')

@section('title', 'Nouveau service')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouveau service') }}</h1>

    <form action="{{ route('services.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('services._form')
        <button type="submit" class="btn btn-success">{{ __('Créer') }}</button>
        <a href="{{ route('services.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
