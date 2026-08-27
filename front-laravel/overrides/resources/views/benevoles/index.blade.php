@extends('layouts.app')

@section('title', 'Bénévoles')

@php
    $badges = [
        'en_attente' => ['bg-warning text-dark', 'En attente'],
        'valide' => ['bg-success', 'Validé'],
        'refuse' => ['bg-danger', 'Refusé'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Candidatures bénévoles') }}</h1>
        <a href="{{ route('benevoles.create') }}" class="btn btn-success">{{ __('+ Nouveau bénévole') }}</a>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Téléphone') }}</th>
                <th>{{ __('Capacités') }}</th>
                <th>{{ __('Disponibilités') }}</th>
                <th>{{ __('Statut') }}</th>
                <th>{{ __('Compte') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($benevoles as $benevole)
                @php
                    [$badgeClass, $badgeLabel] = $badges[$benevole['statut']] ?? ['bg-secondary', $benevole['statut']];
                @endphp
                <tr>
                    <td>{{ $benevole['nom'] }}</td>
                    <td>{{ $benevole['email'] }}</td>
                    <td>{{ $benevole['telephone'] }}</td>
                    <td>{{ $benevole['capacites'] }}</td>
                    <td>{{ $benevole['disponibilites'] }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                    <td>
                        @if ($benevole['user_id'])
                            <span class="badge bg-success">{{ __('Oui') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('Non') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if ($benevole['statut'] !== 'valide')
                            <form action="{{ route('benevoles.statut', $benevole['id']) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="statut" value="valide">
                                <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Valider') }}</button>
                            </form>
                        @endif
                        @if ($benevole['statut'] !== 'refuse')
                            <form action="{{ route('benevoles.statut', $benevole['id']) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="statut" value="refuse">
                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Refuser') }}</button>
                            </form>
                        @endif
                        <a href="{{ route('benevoles.edit', $benevole['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier') }}</a>
                        @if ($benevole['statut'] === 'valide')
                            <a href="{{ route('benevoles.planning', $benevole['id']) }}" class="btn btn-sm btn-outline-dark">{{ __('Planning') }}</a>
                        @endif
                        <form action="{{ route('benevoles.destroy', $benevole['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce bénévole ?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">{{ __('Aucune candidature.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
