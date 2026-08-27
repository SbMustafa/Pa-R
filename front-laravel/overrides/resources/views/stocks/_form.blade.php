@php
    $produit = $produit ?? [];
    $creation = ! isset($produit['id']);
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Code-barre') }}</label>
    <input type="text" name="code_barre" class="form-control"
        value="{{ old('code_barre', $produit['code_barre'] ?? '') }}"
        {{ $creation ? '' : 'required' }} autofocus>
    @if ($creation)
        <div class="form-text">
            {{ __('Scannez ou saisissez le code-barre du produit. Laissez vide pour les produits sans code-barre (vrac, dons de particuliers) : une référence interne sera générée automatiquement.') }}
        </div>
    @endif
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Nom du produit') }}</label>
    <input type="text" name="nom" class="form-control" value="{{ old('nom', $produit['nom'] ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Quantité') }}</label>
        <input type="number" min="0" name="quantite" class="form-control" value="{{ old('quantite', $produit['quantite'] ?? 0) }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Emplacement') }}</label>
        <input type="text" name="emplacement" class="form-control" placeholder="{{ __('Ex : Entrepôt Paris, rayon 3') }}"
            value="{{ old('emplacement', $produit['emplacement'] ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Date limite de consommation') }}</label>
    <input type="date" name="date_limite" class="form-control"
        value="{{ old('date_limite', isset($produit['date_limite']) ? substr($produit['date_limite'], 0, 10) : '') }}">
</div>
