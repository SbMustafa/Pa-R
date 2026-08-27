@extends('layouts.app')

@section('title', 'Collectes')

@php
    $badges = [
        'planifiee' => ['bg-secondary', 'Planifiée'],
        'en_cours' => ['bg-warning text-dark', 'En cours'],
        'terminee' => ['bg-success', 'Terminée'],
    ];
    $nomCommercants = collect($commercants)->keyBy('id');
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Collectes') }}</h1>
        <a href="{{ route('collectes.create') }}" class="btn btn-success">{{ __('+ Nouvelle collecte') }}</a>
    </div>

    <form action="{{ route('collectes.index') }}" method="GET" class="mb-3 d-flex gap-2">
        <select name="statut" class="form-select" style="max-width: 220px;">
            <option value="">{{ __('Tous les statuts') }}</option>
            <option value="planifiee" {{ $statut === 'planifiee' ? 'selected' : '' }}>{{ __('Planifiée') }}</option>
            <option value="en_cours" {{ $statut === 'en_cours' ? 'selected' : '' }}>{{ __('En cours') }}</option>
            <option value="terminee" {{ $statut === 'terminee' ? 'selected' : '' }}>{{ __('Terminée') }}</option>
        </select>
        <button type="submit" class="btn btn-outline-secondary">{{ __('Filtrer') }}</button>
    </form>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Provenance') }}</th>
                <th>{{ __('Statut') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($collectes as $collecte)
                @php
                    [$badgeClass, $badgeLabel] = $badges[$collecte['statut']] ?? ['bg-secondary', $collecte['statut']];
                    $commercant = $collecte['commercant_id'] ? $nomCommercants->get($collecte['commercant_id']) : null;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($collecte['date_collecte'])->format('d/m/Y') }}</td>
                    <td>
                        @if ($commercant)
                            {{ $commercant['nom'] }}
                            <span class="badge bg-light text-dark">{{ __('adhérent') }}</span>
                        @else
                            {{ $collecte['source_libre'] ?: '—' }}
                        @endif
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('collectes.show', $collecte['id']) }}" class="btn btn-sm btn-outline-success">{{ __('Détail / réception') }}</a>
                        <a href="{{ route('collectes.edit', $collecte['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier') }}</a>
                        <form action="{{ route('collectes.destroy', $collecte['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette collecte ? Les produits déjà entrés en stock seront conservés.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">{{ __('Aucune collecte.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
