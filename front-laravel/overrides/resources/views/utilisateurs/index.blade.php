@extends('layouts.app')

@section('title', 'Utilisateurs')

@php
    $badges = [
        'admin' => ['bg-success', __('Administrateur')],
        'commercant' => ['bg-secondary', __('Commerçant')],
        'benevole' => ['bg-info text-dark', __('Bénévole')],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Comptes de connexion') }}</h1>
        <a href="{{ route('utilisateurs.create') }}" class="btn btn-success">{{ __('+ Nouvel administrateur') }}</a>
    </div>

    <table class="table bg-white">
        <thead>
            <tr>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Rôle') }}</th>
                <th>{{ __('Fiche liée') }}</th>
                <th>{{ __('Créé le') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($utilisateurs as $utilisateur)
                @php
                    [$couleur, $libelleRole] = $badges[$utilisateur->role] ?? ['bg-light text-dark', $utilisateur->role];
                    $fiche = $fiches[$utilisateur->id] ?? null;
                    $moi = $utilisateur->id === auth()->id();
                @endphp
                <tr>
                    <td>
                        {{ $utilisateur->name }}
                        @if ($moi)
                            <span class="text-muted small">({{ __('vous') }})</span>
                        @endif
                    </td>
                    <td>{{ $utilisateur->email }}</td>
                    <td><span class="badge {{ $couleur }}">{{ $libelleRole }}</span></td>
                    <td>
                        @if ($fiche)
                            <span class="text-muted">{{ __('Fiche') }} {{ $fiche['libelle'] }} #{{ $fiche['id'] }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $utilisateur->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            {{-- Changement de rôle : impossible sur soi-même (garde-fou anti-verrouillage)
                                 et sur un compte rattaché à une fiche commerçant/bénévole. --}}
                            @if (! $moi && ! $fiche)
                                <form action="{{ route('utilisateurs.role', $utilisateur) }}" method="POST"
                                    class="d-flex gap-1 m-0">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width:auto">
                                        @foreach ($badges as $code => [$c, $libelle])
                                            <option value="{{ $code }}" @selected($utilisateur->role === $code)>{{ $libelle }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Changer') }}</button>
                                </form>
                            @endif

                            <form action="{{ route('utilisateurs.lien', $utilisateur) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success"
                                    title="{{ __('Envoyer un lien pour définir le mot de passe') }}">{{ __('Renvoyer le lien') }}</button>
                            </form>

                            @if (! $moi && ! $fiche)
                                <form action="{{ route('utilisateurs.destroy', $utilisateur) }}" method="POST" class="m-0"
                                    onsubmit="return confirm('{{ __('Supprimer ce compte ?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="text-muted" style="font-size:.87rem">
        {{ __('Un compte rattaché à une fiche commerçant ou bénévole se gère depuis sa propre page : supprimer la fiche supprime aussi le compte.') }}
        {{ __('Vous ne pouvez ni modifier ni supprimer votre propre compte, ni le dernier administrateur.') }}
    </p>
@endsection
