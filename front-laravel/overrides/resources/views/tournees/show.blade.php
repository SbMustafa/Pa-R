@extends('layouts.app')

@section('title', 'Tournée — ' . $tournee['destinataire'])

@php
    $badges = [
        'planifiee' => ['bg-secondary', 'Planifiée'],
        'en_cours' => ['bg-warning text-dark', 'En cours'],
        'livree' => ['bg-success', 'Livrée'],
    ];
    [$badgeClass, $badgeLabel] = $badges[$tournee['statut']] ?? ['bg-secondary', $tournee['statut']];
    $benevole = $tournee['benevole_id'] ? collect($benevoles)->firstWhere('id', $tournee['benevole_id']) : null;
    $disponibles = collect($produits)->filter(fn ($p) => $p['quantite'] > 0);
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">
            {{ $tournee['destinataire'] }}
            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </h1>
        <div>
            <a href="{{ route('tournees.recapitulatif', $tournee['id']) }}" class="btn btn-outline-dark">{{ __('Récapitulatif PDF') }}</a>
            <a href="{{ route('tournees.index') }}" class="btn btn-link">{{ __('← Toutes les tournées') }}</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="bg-white p-3 rounded shadow-sm mb-3">
                <h2 class="h6 text-muted">{{ __('Destinataire') }}</h2>
                <p class="mb-2">
                    <strong>{{ $tournee['destinataire'] }}</strong><br>
                    <span class="text-muted">
                        {{ $tournee['type_destinataire'] === 'association' ? 'Association caritative' : 'Particulier en détresse' }}
                    </span>
                    @if ($tournee['adresse'])
                        <br><span class="text-muted">{{ $tournee['adresse'] }}</span>
                    @endif
                </p>

                <h2 class="h6 text-muted">{{ __('Date') }}</h2>
                <p class="mb-2">{{ \Carbon\Carbon::parse($tournee['date_tournee'])->format('d/m/Y') }}</p>

                <h2 class="h6 text-muted">{{ __('Bénévole en charge') }}</h2>
                <p class="mb-2">{{ $benevole['nom'] ?? 'Non affecté' }}</p>

                @if ($tournee['notes'])
                    <h2 class="h6 text-muted">{{ __('Notes') }}</h2>
                    <p class="mb-2">{{ $tournee['notes'] }}</p>
                @endif

                <div class="d-flex gap-2 flex-wrap mb-2">
                    @if ($tournee['statut'] !== 'en_cours')
                        <form action="{{ route('tournees.statut', $tournee['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="en_cours">
                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('Marquer en cours') }}</button>
                        </form>
                    @endif
                    @if ($tournee['statut'] !== 'livree')
                        <form action="{{ route('tournees.statut', $tournee['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="livree">
                            <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Marquer livrée') }}</button>
                        </form>
                    @endif
                </div>
                <a href="{{ route('tournees.edit', $tournee['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier la tournée') }}</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="bg-white p-3 rounded shadow-sm mb-3">
                <h2 class="h5 mb-3">{{ __('Charger un produit du stock') }}</h2>
                <p class="text-muted small">{{ __('Le produit chargé est immédiatement sorti du stock.') }}</p>

                <form action="{{ route('tournees.lignes.store', $tournee['id']) }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-7">
                        <label class="form-label">{{ __('Produit disponible') }}</label>
                        <select name="produit_id" class="form-select" required>
                            @forelse ($disponibles as $p)
                                <option value="{{ $p['id'] }}">
                                    {{ $p['nom'] }} ({{ $p['code_barre'] }}) — {{ $p['quantite'] }} en stock
                                </option>
                            @empty
                                <option value="" disabled>{{ __('Aucun produit disponible en stock') }}</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Quantité') }}</label>
                        <input type="number" min="1" name="quantite" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100" {{ $disponibles->isEmpty() ? 'disabled' : '' }}>{{ __('Charger') }}</button>
                    </div>
                </form>
            </div>

            <h2 class="h5 mb-2">
                Produits chargés ({{ collect($lignes)->sum('quantite') }} article(s))
            </h2>
            <table class="table table-striped bg-white">
                <thead>
                    <tr>
                        <th>{{ __('Code-barre') }}</th>
                        <th>{{ __('Produit') }}</th>
                        <th>{{ __('Quantité') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lignes as $ligne)
                        <tr>
                            <td><code>{{ $ligne['code_barre'] }}</code></td>
                            <td>{{ $ligne['nom'] }}</td>
                            <td>{{ $ligne['quantite'] }}</td>
                            <td class="text-end">
                                <form action="{{ route('tournees.lignes.destroy', [$tournee['id'], $ligne['id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Retirer ce produit de la tournée ? Il sera remis en stock.') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Retirer') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">{{ __("Aucun produit chargé pour l'instant.") }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
