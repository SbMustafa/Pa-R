@extends('layouts.app')

@section('title', 'Collecte du ' . \Carbon\Carbon::parse($collecte['date_collecte'])->format('d/m/Y'))

@php
    $badges = [
        'planifiee' => ['bg-secondary', 'Planifiée'],
        'en_cours' => ['bg-warning text-dark', 'En cours'],
        'terminee' => ['bg-success', 'Terminée'],
    ];
    [$badgeClass, $badgeLabel] = $badges[$collecte['statut']] ?? ['bg-secondary', $collecte['statut']];
    $commercant = $collecte['commercant_id'] ? collect($commercants)->firstWhere('id', $collecte['commercant_id']) : null;
    $benevole = $collecte['benevole_id'] ? collect($benevoles)->firstWhere('id', $collecte['benevole_id']) : null;
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">
            Collecte du {{ \Carbon\Carbon::parse($collecte['date_collecte'])->format('d/m/Y') }}
            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </h1>
        <a href="{{ route('collectes.index') }}" class="btn btn-link">{{ __('← Toutes les collectes') }}</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="bg-white p-3 rounded shadow-sm mb-3">
                <h2 class="h6 text-muted">{{ __('Provenance') }}</h2>
                <p class="mb-2">
                    @if ($commercant)
                        <strong>{{ $commercant['nom'] }}</strong><br>
                        <span class="text-muted">{{ $commercant['adresse'] }} {{ $commercant['ville'] }}</span>
                    @else
                        {{ $collecte['source_libre'] ?: '—' }}
                    @endif
                </p>

                <h2 class="h6 text-muted">{{ __('Bénévole affecté') }}</h2>
                <p class="mb-2">{{ $benevole['nom'] ?? 'Non affecté' }}</p>

                @if ($collecte['notes'])
                    <h2 class="h6 text-muted">{{ __('Notes') }}</h2>
                    <p class="mb-2">{{ $collecte['notes'] }}</p>
                @endif

                <div class="d-flex gap-2 flex-wrap">
                    @if ($collecte['statut'] !== 'en_cours')
                        <form action="{{ route('collectes.statut', $collecte['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="en_cours">
                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('Marquer en cours') }}</button>
                        </form>
                    @endif
                    @if ($collecte['statut'] !== 'terminee')
                        <form action="{{ route('collectes.statut', $collecte['id']) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="terminee">
                            <button type="submit" class="btn btn-sm btn-outline-success">{{ __('Marquer terminée') }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="bg-white p-3 rounded shadow-sm mb-3">
                <h2 class="h5 mb-3">{{ __('Réceptionner un produit') }}</h2>
                <p class="text-muted small">
                    {{ __('Chaque produit rapporté au siège est référencé par son code-barre puis entre en stock.') }}
                </p>

                <form action="{{ route('collectes.produits.store', $collecte['id']) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Code-barre') }}</label>
                            <input type="text" name="code_barre" class="form-control" autofocus
                                placeholder="{{ __('Scanner ou saisir (vide = généré)') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Nom du produit') }}</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ __('Quantité') }}</label>
                            <input type="number" min="0" name="quantite" class="form-control" value="1" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">{{ __('Emplacement') }}</label>
                            <input type="text" name="emplacement" class="form-control" placeholder="{{ __('Ex : Entrepôt Paris, rayon 3') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Date limite') }}</label>
                            <input type="date" name="date_limite" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">{{ __('Réceptionner et entrer en stock') }}</button>
                </form>
            </div>

            <h2 class="h5 mb-2">Produits rapportés par cette collecte ({{ count($produits) }})</h2>
            <table class="table table-striped bg-white">
                <thead>
                    <tr>
                        <th>{{ __('Code-barre') }}</th>
                        <th>{{ __('Nom') }}</th>
                        <th>{{ __('Quantité') }}</th>
                        <th>{{ __('Emplacement') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produits as $produit)
                        <tr>
                            <td><code>{{ $produit['code_barre'] }}</code></td>
                            <td>{{ $produit['nom'] }}</td>
                            <td>{{ $produit['quantite'] }}</td>
                            <td>{{ $produit['emplacement'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">{{ __("Aucun produit réceptionné pour l'instant.") }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
