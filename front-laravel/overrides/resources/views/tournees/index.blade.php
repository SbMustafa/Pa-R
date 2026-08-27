@extends('layouts.app')

@section('title', 'Tournées')

@php
    $badges = [
        'planifiee' => ['bg-secondary', 'Planifiée'],
        'en_cours' => ['bg-warning text-dark', 'En cours'],
        'livree' => ['bg-success', 'Livrée'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Tournées de distribution') }}</h1>
        <a href="{{ route('tournees.create') }}" class="btn btn-success">{{ __('+ Nouvelle tournée') }}</a>
    </div>

    <form action="{{ route('tournees.index') }}" method="GET" class="mb-3 d-flex gap-2">
        <select name="statut" class="form-select" style="max-width: 220px;">
            <option value="">{{ __('Tous les statuts') }}</option>
            <option value="planifiee" {{ $statut === 'planifiee' ? 'selected' : '' }}>{{ __('Planifiée') }}</option>
            <option value="en_cours" {{ $statut === 'en_cours' ? 'selected' : '' }}>{{ __('En cours') }}</option>
            <option value="livree" {{ $statut === 'livree' ? 'selected' : '' }}>{{ __('Livrée') }}</option>
        </select>
        <button type="submit" class="btn btn-outline-secondary">{{ __('Filtrer') }}</button>
    </form>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Destinataire') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Statut') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tournees as $tournee)
                @php
                    [$badgeClass, $badgeLabel] = $badges[$tournee['statut']] ?? ['bg-secondary', $tournee['statut']];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tournee['date_tournee'])->format('d/m/Y') }}</td>
                    <td>{{ $tournee['destinataire'] }}</td>
                    <td>{{ $tournee['type_destinataire'] === 'association' ? 'Association' : 'Particulier' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('tournees.show', $tournee['id']) }}" class="btn btn-sm btn-outline-success">{{ __('Détail / chargement') }}</a>
                        <a href="{{ route('tournees.recapitulatif', $tournee['id']) }}" class="btn btn-sm btn-outline-dark">{{ __('PDF') }}</a>
                        <form action="{{ route('tournees.destroy', $tournee['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette tournée ? Les produits chargés seront remis en stock.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">{{ __('Aucune tournée.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
