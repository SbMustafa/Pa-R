@php
    $commercant = $commercant ?? [];
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Nom') }}</label>
    <input type="text" name="nom" class="form-control" value="{{ old('nom', $commercant['nom'] ?? '') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Adresse') }}</label>
    <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $commercant['adresse'] ?? '') }}">
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">{{ __('Ville') }}</label>
        <input type="text" name="ville" class="form-control" value="{{ old('ville', $commercant['ville'] ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('Code postal') }}</label>
        <input type="text" name="code_postal" class="form-control" value="{{ old('code_postal', $commercant['code_postal'] ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Email') }}</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $commercant['email'] ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Téléphone') }}</label>
        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $commercant['telephone'] ?? '') }}">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('SIRET') }}</label>
    <input type="text" name="siret" class="form-control" value="{{ old('siret', $commercant['siret'] ?? '') }}">
</div>
