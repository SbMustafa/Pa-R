@extends('layouts.app')

@section('title', 'Modifier produit')

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier produit') }}</h1>

    <form action="{{ route('stocks.update', $produit['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('stocks._form', ['produit' => $produit])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('stocks.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
@endsection
