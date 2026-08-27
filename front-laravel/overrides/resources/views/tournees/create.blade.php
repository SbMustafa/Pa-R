@extends('layouts.app')

@section('title', 'Nouvelle tournée')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouvelle tournée de distribution') }}</h1>

    <form action="{{ route('tournees.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('tournees._form')
        <button type="submit" class="btn btn-success">{{ __('Planifier la tournée') }}</button>
        <a href="{{ route('tournees.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
