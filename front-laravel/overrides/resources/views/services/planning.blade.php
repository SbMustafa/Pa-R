@extends('layouts.app')

@section('title', 'Planning des séances')

@php
    $badges = [
        'ouverte' => ['bg-success', 'Ouverte'],
        'fermee' => ['bg-secondary', 'Fermée'],
        'annulee' => ['bg-danger', 'Annulée'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Planning des séances') }}</h1>
        <div>
            <a href="{{ route('services.index') }}" class="btn btn-outline-success">{{ __('Catalogue des services') }}</a>
            <a href="{{ route('services.seances.create') }}" class="btn btn-success">{{ __('+ Nouvelle séance') }}</a>
        </div>
    </div>

    <form action="{{ route('services.planning') }}" method="GET" class="mb-3 d-flex gap-2">
        <select name="service_id" class="form-select" style="max-width: 320px;">
            <option value="">{{ __('Tous les services') }}</option>
            @foreach ($services as $s)
                <option value="{{ $s['id'] }}" {{ (string) $serviceId === (string) $s['id'] ? 'selected' : '' }}>
                    {{ $s['nom'] }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-secondary">{{ __('Filtrer') }}</button>
    </form>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Service') }}</th>
                <th>{{ __('Lieu') }}</th>
                <th>{{ __('Bénévole') }}</th>
                <th>{{ __('Inscrits') }}</th>
                <th>{{ __('Statut') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($seances as $seance)
                @php
                    [$badgeClass, $badgeLabel] = $badges[$seance['statut']] ?? ['bg-secondary', $seance['statut']];
                    $complet = $seance['nb_inscrits'] >= $seance['places_max'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($seance['date_debut'])->format('d/m/Y H:i') }}</td>
                    <td>{{ $services[$seance['service_id']]['nom'] ?? '—' }}</td>
                    <td>{{ $seance['lieu'] }}</td>
                    <td>{{ $seance['benevole_id'] ? ($benevoles[$seance['benevole_id']]['nom'] ?? '—') : 'Non affecté' }}</td>
                    <td>
                        <span class="{{ $complet ? 'badge bg-warning text-dark' : '' }}">
                            {{ $seance['nb_inscrits'] }}/{{ $seance['places_max'] }}
                        </span>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('services.seances.edit', $seance['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Détail / inscrits') }}</a>
                        <form action="{{ route('services.seances.destroy', $seance['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette séance ? Les inscriptions seront supprimées.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">{{ __('Aucune séance planifiée.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
