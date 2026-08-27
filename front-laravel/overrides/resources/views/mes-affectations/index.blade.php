@extends('layouts.app')

@section('title', 'Mes affectations')

@php
    $couleurs = ['Service' => 'bg-primary', 'Collecte' => 'bg-info text-dark', 'Tournée' => 'bg-dark'];
    $aujourdhui = now()->startOfDay();
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Mes affectations') }}</h1>
        @if ($benevole['statut'] === 'valide')
            <a href="{{ route('affectations.planning') }}" class="btn btn-outline-dark">
                {{ __('Télécharger mon planning (Excel)') }}
            </a>
        @endif
    </div>

    @if ($benevole['statut'] !== 'valide')
        <div class="alert alert-warning">
            {{ __("Votre candidature n'est pas encore validée : vous ne pouvez pas encore être affecté(e) à des missions.") }} <a href="{{ route('benevoles.mine') }}">{{ __('Voir ma candidature') }}</a>
        </div>
    @else
        @if ($benevole['capacites'])
            <p class="text-muted mb-4">
                {{ __('Vos capacités déclarées :') }} <strong>{{ $benevole['capacites'] }}</strong>
                @if ($benevole['disponibilites'])
                    — disponibilités : {{ $benevole['disponibilites'] }}
                @endif
            </p>
        @endif

        <table class="table table-striped bg-white">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Mission') }}</th>
                    <th>{{ __('Lieu') }}</th>
                    <th>{{ __('Statut') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($affectations as $a)
                    @php $date = \Carbon\Carbon::parse($a['date']); @endphp
                    <tr class="{{ $date->lt($aujourdhui) ? 'text-muted' : '' }}">
                        <td>
                            {{ $date->format('d/m/Y H:i') }}
                            @if ($date->isToday())
                                <span class="badge bg-danger">{{ __("Aujourd'hui") }}</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $couleurs[$a['type']] ?? 'bg-secondary' }}">{{ $a['type'] }}</span></td>
                        <td>{{ $a['libelle'] }}</td>
                        <td>{{ $a['lieu'] }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $a['statut'])) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            {{ __('Aucune mission ne vous a encore été affectée.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
@endsection
