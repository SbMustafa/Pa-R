@extends('layouts.app')

@section('title', 'Nouveau produit')

@section('content')
    <h1 class="h3 mb-3">{{ __('Nouveau produit') }}</h1>

    <form action="{{ route('stocks.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @include('stocks._form')
        <button type="submit" class="btn btn-success">{{ __('Ajouter au stock') }}</button>
        <a href="{{ route('stocks.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
