@extends('layouts.app')

@section('title', 'Modifier bénévole')

@php
    $couleurs = ['Service' => 'bg-primary', 'Collecte' => 'bg-info text-dark', 'Tournée' => 'bg-dark'];
@endphp

@section('content')
    <h1 class="h3 mb-3">{{ __('Modifier bénévole') }}</h1>

    <div class="row">
    <div class="col-md-7">
    <form action="{{ route('benevoles.update', $benevole['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
        @csrf
        @method('PUT')
        @include('benevoles._form', ['benevole' => $benevole])

        <div class="border-top pt-3 mb-3">
            @if ($benevole['user_id'])
                <p class="text-muted mb-0">{{ __('Ce bénévole a déjà un compte de connexion.') }}</p>
            @else
                <div class="form-check mb-2">
                    <input type="checkbox" name="creer_compte" value="1" class="form-check-input" id="creer_compte" {{ old('creer_compte') ? 'checked' : '' }}>
                    <label class="form-check-label" for="creer_compte">{{ __('Créer un compte de connexion pour ce bénévole') }}</label>
                </div>
                <label class="form-label">{{ __('Email de connexion') }}</label>
                <input type="email" name="email_connexion" class="form-control" value="{{ old('email_connexion') }}">
                <div class="form-text">{{ __("Un lien d'activation sera envoyé à cette adresse : la personne choisira elle-même son mot de passe.") }}</div>
            @endif
        </div>

        <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
        <a href="{{ route('benevoles.index') }}" class="btn btn-link">{{ __('Annuler') }}</a>
    </form>
    </div>

    <div class="col-md-5">
        <div class="bg-white p-4 rounded shadow-sm">
            <h2 class="h5 mb-3">Affectations ({{ count($affectations) }})</h2>

            @if ($benevole['statut'] !== 'valide')
                <p class="text-muted">
                    {{ __('Candidature non validée : ce bénévole ne peut pas être affecté à des missions.') }}
                </p>
            @endif

            <table class="table table-sm">
                <tbody>
                    @forelse ($affectations as $a)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($a['date'])->format('d/m/Y') }}</td>
                            <td><span class="badge {{ $couleurs[$a['type']] ?? 'bg-secondary' }}">{{ $a['type'] }}</span></td>
                            <td><a href="{{ $a['url'] }}">{{ \Illuminate\Support\Str::limit($a['libelle'], 30) }}</a></td>
                        </tr>
                    @empty
                        <tr><td class="text-muted">{{ __('Aucune mission affectée.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>

            <a href="{{ route('benevoles.planning', $benevole['id']) }}" class="btn btn-sm btn-outline-dark mb-3">
                {{ __('Télécharger son planning (Excel)') }}
            </a>

            <p class="text-muted small mb-0">
                {{ __('Les affectations se font depuis le planning des séances, les collectes et les tournées.') }}
            </p>
        </div>
    </div>
    </div>
@endsection
