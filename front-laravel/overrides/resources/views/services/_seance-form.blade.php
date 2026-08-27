@php
    $seance = $seance ?? [];
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Service') }}</label>
    <select name="service_id" class="form-select" required>
        @foreach ($services as $s)
            <option value="{{ $s['id'] }}"
                {{ (string) old('service_id', $seance['service_id'] ?? '') === (string) $s['id'] ? 'selected' : '' }}>
                {{ $s['nom'] }}@if ($s['categorie']) — {{ $s['categorie'] }}@endif
            </option>
        @endforeach
    </select>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Date et heure') }}</label>
        <input type="datetime-local" name="date_debut" class="form-control" required
            value="{{ old('date_debut', isset($seance['date_debut']) ? \Carbon\Carbon::parse($seance['date_debut'])->format('Y-m-d\TH:i') : now()->addDay()->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Places') }}</label>
        <input type="number" min="1" name="places_max" class="form-control" required
            value="{{ old('places_max', $seance['places_max'] ?? 10) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ __('Statut') }}</label>
        @php $statutActuel = old('statut', $seance['statut'] ?? 'ouverte'); @endphp
        <select name="statut" class="form-select" required>
            <option value="ouverte" {{ $statutActuel === 'ouverte' ? 'selected' : '' }}>{{ __('Ouverte') }}</option>
            <option value="fermee" {{ $statutActuel === 'fermee' ? 'selected' : '' }}>{{ __('Fermée') }}</option>
            <option value="annulee" {{ $statutActuel === 'annulee' ? 'selected' : '' }}>{{ __('Annulée') }}</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Lieu') }}</label>
    <input type="text" name="lieu" class="form-control" placeholder="{{ __('Ex : Siège Paris, salle 2') }}"
        value="{{ old('lieu', $seance['lieu'] ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Bénévole affecté') }}</label>
    <select name="benevole_id" class="form-select">
        <option value="">{{ __('— Non affecté —') }}</option>
        @foreach ($benevoles as $b)
            @if ($b['statut'] === 'valide')
                <option value="{{ $b['id'] }}"
                    {{ (string) old('benevole_id', $seance['benevole_id'] ?? '') === (string) $b['id'] ? 'selected' : '' }}>
                    {{ $b['nom'] }}@if ($b['capacites']) — {{ $b['capacites'] }}@endif
                </option>
            @endif
        @endforeach
    </select>
    <div class="form-text">{{ __('Seuls les bénévoles dont la candidature est validée sont proposés.') }}</div>
</div>
