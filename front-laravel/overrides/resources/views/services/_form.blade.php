@php
    $service = $service ?? [];
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Nom du service') }}</label>
    <input type="text" name="nom" class="form-control" required autofocus
        placeholder="{{ __('Ex : Cours de cuisine anti-gaspi') }}"
        value="{{ old('nom', $service['nom'] ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Catégorie') }}</label>
    <input type="text" name="categorie" class="form-control" list="categories-services"
        placeholder="{{ __('Ex : Cours de cuisine') }}"
        value="{{ old('categorie', $service['categorie'] ?? '') }}">
    <datalist id="categories-services">
        <option value="Conseils anti-gaspi"></option>
        <option value="Cours de cuisine"></option>
        <option value="Partage de véhicules"></option>
        <option value="Échange de services entre particuliers"></option>
        <option value="Réparation"></option>
        <option value="Gardiennage"></option>
    </datalist>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Description') }}</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $service['description'] ?? '') }}</textarea>
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="actif" value="1" class="form-check-input" id="actif"
        {{ old('actif', $service['actif'] ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="actif">{{ __('Service actif (proposable aux adhérents)') }}</label>
</div>
