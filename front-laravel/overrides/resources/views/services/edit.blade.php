@extends('layouts.app')

@section('title', 'Modifier le service')

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier le service') }}</h1>

    <form action="{{ route('services.update', $service['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('services._form', ['service' => $service])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('services.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
