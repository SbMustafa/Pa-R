@extends('layouts.app')

@section('title', 'Commerçants')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Commerçants adhérents') }}</h1>
        <a href="{{ route('commercants.create') }}" class="btn btn-success">{{ __('+ Nouveau commerçant') }}</a>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Ville') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Téléphone') }}</th>
                <th>{{ __('Actif') }}</th>
                <th>{{ __('Adhésion') }}</th>
                <th>{{ __('Compte') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($commercants as $commercant)
                <tr>
                    <td>{{ $commercant['nom'] }}</td>
                    <td>{{ $commercant['ville'] }}</td>
                    <td>{{ $commercant['email'] }}</td>
                    <td>{{ $commercant['telephone'] }}</td>
                    <td>{{ $commercant['actif'] ? 'Oui' : 'Non' }}</td>
                    <td>
                        @if ($commercant['date_renouvellement'])
                            @php
                                $echeance = \Carbon\Carbon::parse($commercant['date_renouvellement'])->startOfDay();
                                $joursRestants = (int) now()->startOfDay()->diffInDays($echeance, false);
                            @endphp
                            @if ($joursRestants < 0)
                                <span class="badge bg-danger">Expirée le {{ $echeance->format('d/m/Y') }}</span>
                            @elseif ($joursRestants <= 30)
                                <span class="badge bg-warning text-dark">Dans {{ $joursRestants }} j</span>
                            @else
                                {{ $echeance->format('d/m/Y') }}
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($commercant['user_id'])
                            <span class="badge bg-success">{{ __('Oui') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('Non') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('commercants.edit', $commercant['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier') }}</a>
                        <form action="{{ route('commercants.destroy', $commercant['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce commerçant ?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">{{ __('Aucun commerçant enregistré.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
