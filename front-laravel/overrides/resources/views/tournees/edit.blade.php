@extends('layouts.app')

@section('title', 'Modifier la tournée')

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier la tournée') }}</h1>

    <form action="{{ route('tournees.update', $tournee['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('tournees._form', ['tournee' => $tournee])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('tournees.show', $tournee['id']) }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
