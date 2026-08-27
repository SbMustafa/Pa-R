@extends('layouts.app')

@section('title', 'Ma candidature')

@php
    $badges = [
        'en_attente' => ['bg-warning text-dark', 'En attente de validation'],
        'valide' => ['bg-success', 'Candidature validée'],
        'refuse' => ['bg-danger', 'Candidature refusée'],
    ];
    [$badgeClass, $badgeLabel] = $badges[$benevole['statut']] ?? ['bg-secondary', $benevole['statut']];
@endphp

@section('content')
    <h1 class="h3 mb-3">{{ __('Ma candidature bénévole') }}</h1>

    <p><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></p>

    <form action="{{ route('benevoles.mine.update') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('benevoles._form', ['benevole' => $benevole])
        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
    </form>
@endsection
