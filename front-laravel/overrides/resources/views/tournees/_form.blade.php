@php
    $tournee = $tournee ?? [];
@endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">{{ __('Destinataire') }}</label>
        <input type="text" name="destinataire" class="form-control" required
            placeholder="{{ __('Ex : Restos du Cœur — antenne Paris 11e') }}"
            value="{{ old('destinataire', $tournee['destinataire'] ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ __('Type') }}</label>
        @php $type = old('type_destinataire', $tournee['type_destinataire'] ?? 'association'); @endphp
        <select name="type_destinataire" class="form-select" required>
            <option value="association" {{ $type === 'association' ? 'selected' : '' }}>{{ __('Association caritative') }}</option>
            <option value="particulier" {{ $type === 'particulier' ? 'selected' : '' }}>{{ __('Particulier en détresse') }}</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Adresse de livraison') }}</label>
    <input type="text" name="adresse" class="form-control"
        value="{{ old('adresse', $tournee['adresse'] ?? '') }}">
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Date de la tournée') }}</label>
        <input type="date" name="date_tournee" class="form-control" required
            value="{{ old('date_tournee', isset($tournee['date_tournee']) ? substr($tournee['date_tournee'], 0, 10) : now()->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Statut') }}</label>
        @php $statutActuel = old('statut', $tournee['statut'] ?? 'planifiee'); @endphp
        <select name="statut" class="form-select" required>
            <option value="planifiee" {{ $statutActuel === 'planifiee' ? 'selected' : '' }}>{{ __('Planifiée') }}</option>
            <option value="en_cours" {{ $statutActuel === 'en_cours' ? 'selected' : '' }}>{{ __('En cours') }}</option>
            <option value="livree" {{ $statutActuel === 'livree' ? 'selected' : '' }}>{{ __('Livrée') }}</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Bénévole en charge (chauffeur)') }}</label>
    <select name="benevole_id" class="form-select">
        <option value="">{{ __('— Non affecté —') }}</option>
        @foreach ($benevoles as $b)
            @if ($b['statut'] === 'valide')
                <option value="{{ $b['id'] }}"
                    {{ (string) old('benevole_id', $tournee['benevole_id'] ?? '') === (string) $b['id'] ? 'selected' : '' }}>
                    {{ $b['nom'] }}@if ($b['capacites']) — {{ $b['capacites'] }}@endif
                </option>
            @endif
        @endforeach
    </select>
    <div class="form-text">{{ __('Seuls les bénévoles dont la candidature est validée sont proposés.') }}</div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Notes') }}</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $tournee['notes'] ?? '') }}</textarea>
</div>
