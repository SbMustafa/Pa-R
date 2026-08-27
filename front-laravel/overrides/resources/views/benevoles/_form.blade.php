@php
    $benevole = $benevole ?? [];
    // old('capacites') est un tableau (cases à cocher) après un échec de validation,
    // alors que l'API renvoie une chaîne « Chauffeur, Cuisinier ».
    $ancienesCapacites = old('capacites', $benevole['capacites'] ?? '');
    $capacitesSelectionnees = is_array($ancienesCapacites)
        ? $ancienesCapacites
        : array_filter(array_map('trim', explode(',', $ancienesCapacites)));
    $capacitesDisponibles = ['Chauffeur', 'Cuisinier', 'Plombier', 'Électricien', 'Bricolage'];
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Nom') }}</label>
    <input type="text" name="nom" class="form-control" value="{{ old('nom', $benevole['nom'] ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Email') }}</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $benevole['email'] ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Téléphone') }}</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $benevole['telephone'] ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label d-block">{{ __('Capacités') }}</label>
    @foreach ($capacitesDisponibles as $capacite)
        <div class="form-check form-check-inline">
            <input type="checkbox" name="capacites[]" class="form-check-input" value="{{ $capacite }}"
                id="capacite-{{ $loop->index }}"
                {{ in_array($capacite, $capacitesSelectionnees) ? 'checked' : '' }}>
            <label class="form-check-label" for="capacite-{{ $loop->index }}">{{ $capacite }}</label>
        </div>
    @endforeach
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Disponibilités') }}</label>
    <input type="text" name="disponibilites" class="form-control"
        placeholder="{{ __('Ex : lundi, mercredi soir, week-ends') }}"
        value="{{ old('disponibilites', $benevole['disponibilites'] ?? '') }}">
</div>
