@php
    $collecte = $collecte ?? [];
@endphp

<div class="mb-3">
    <label class="form-label">{{ __('Commerçant adhérent') }}</label>
    <select name="commercant_id" id="collecte-commercant" class="form-select">
        <option value="">{{ __('— Aucun (collecte chez un particulier) —') }}</option>
        @foreach ($commercants as $c)
            <option value="{{ $c['id'] }}"
                {{ (string) old('commercant_id', $collecte['commercant_id'] ?? '') === (string) $c['id'] ? 'selected' : '' }}>
                {{ $c['nom'] }}@if ($c['ville']) — {{ $c['ville'] }}@endif
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3" id="collecte-source-libre-bloc">
    <label class="form-label">{{ __('Provenance (si pas un commerçant adhérent)') }}</label>
    <input type="text" name="source_libre" id="collecte-source-libre" class="form-control"
        placeholder="{{ __('Ex : particulier — M. Dupont, 12 rue de Paris') }}"
        value="{{ old('source_libre', $collecte['source_libre'] ?? '') }}">
    <div class="form-text">{{ __('Une collecte a une seule provenance : un commerçant adhérent ou une source libre.') }}</div>
</div>

<script>
    (function () {
        var select = document.getElementById('collecte-commercant');
        var bloc = document.getElementById('collecte-source-libre-bloc');
        var champ = document.getElementById('collecte-source-libre');

        function toggle() {
            var adherentChoisi = select.value !== '';
            bloc.style.display = adherentChoisi ? 'none' : '';
            if (adherentChoisi) {
                champ.value = '';
            }
        }

        select.addEventListener('change', toggle);
        toggle();
    })();
</script>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Date de la collecte') }}</label>
        <input type="date" name="date_collecte" class="form-control" required
            value="{{ old('date_collecte', isset($collecte['date_collecte']) ? substr($collecte['date_collecte'], 0, 10) : now()->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('Statut') }}</label>
        @php $statutActuel = old('statut', $collecte['statut'] ?? 'planifiee'); @endphp
        <select name="statut" class="form-select" required>
            <option value="planifiee" {{ $statutActuel === 'planifiee' ? 'selected' : '' }}>{{ __('Planifiée') }}</option>
            <option value="en_cours" {{ $statutActuel === 'en_cours' ? 'selected' : '' }}>{{ __('En cours') }}</option>
            <option value="terminee" {{ $statutActuel === 'terminee' ? 'selected' : '' }}>{{ __('Terminée') }}</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Bénévole affecté (chauffeur)') }}</label>
    <select name="benevole_id" class="form-select">
        <option value="">{{ __('— Non affecté —') }}</option>
        @foreach ($benevoles as $b)
            @if ($b['statut'] === 'valide')
                <option value="{{ $b['id'] }}"
                    {{ (string) old('benevole_id', $collecte['benevole_id'] ?? '') === (string) $b['id'] ? 'selected' : '' }}>
                    {{ $b['nom'] }}@if ($b['capacites']) — {{ $b['capacites'] }}@endif
                </option>
            @endif
        @endforeach
    </select>
    <div class="form-text">{{ __('Seuls les bénévoles dont la candidature est validée sont proposés.') }}</div>
</div>

<div class="mb-3">
    <label class="form-label">{{ __('Notes') }}</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $collecte['notes'] ?? '') }}</textarea>
</div>
