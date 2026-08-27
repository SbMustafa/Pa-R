@extends('layouts.app')

@section('title', 'Stocks')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Stock') }}</h1>
        <a href="{{ route('stocks.create') }}" class="btn btn-success">{{ __('+ Nouveau produit') }}</a>
    </div>

    <form action="{{ route('stocks.index') }}" method="GET" class="mb-3 d-flex gap-2">
        <input type="text" name="q" class="form-control" placeholder="{{ __('Rechercher par code-barre ou nom') }}"
            value="{{ $recherche }}" style="max-width: 300px;">
        <button type="submit" class="btn btn-outline-secondary">{{ __('Rechercher') }}</button>
        @if ($recherche !== '')
            <a href="{{ route('stocks.index') }}" class="btn btn-link">{{ __('Réinitialiser') }}</a>
        @endif
    </form>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Code-barre') }}</th>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Quantité') }}</th>
                <th>{{ __('Emplacement') }}</th>
                <th>{{ __('Date limite') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produits as $produit)
                @php
                    $dlcProche = $produit['date_limite'] && \Carbon\Carbon::parse($produit['date_limite'])->isBefore(now()->addDays(3));
                @endphp
                <tr>
                    <td><code>{{ $produit['code_barre'] }}</code></td>
                    <td>{{ $produit['nom'] }}</td>
                    <td>{{ $produit['quantite'] }}</td>
                    <td>{{ $produit['emplacement'] }}</td>
                    <td>
                        @if ($produit['date_limite'])
                            <span class="{{ $dlcProche ? 'badge bg-danger' : '' }}">
                                {{ \Carbon\Carbon::parse($produit['date_limite'])->format('d/m/Y') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('stocks.edit', $produit['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier') }}</a>
                        <form action="{{ route('stocks.destroy', $produit['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Retirer ce produit du stock ?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">{{ __('Aucun produit en stock.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
