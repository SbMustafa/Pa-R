@extends('layouts.app')

@section('title', 'Ma fiche adhérent')

@section('content')
    <h1 class="h3 mb-3">{{ __('Ma fiche adhérent') }}</h1>

    <form action="{{ route('commercants.mine.update') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('commercants._form', ['commercant' => $commercant])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
    </form>
@endsection
