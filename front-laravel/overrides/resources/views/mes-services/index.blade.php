@extends('layouts.app')

@section('title', 'Services aux adhérents')

@section('content')
    <h1 class="h3 mb-3">{{ __('Services aux adhérents') }}</h1>
    <p class="text-muted mb-4">
        {{ __("Séances à venir proposées par l'association. Inscrivez-vous en un clic.") }}
    </p>

    <div class="row">
        @forelse ($seances as $seance)
            @php
                $service = $services[$seance['service_id']] ?? null;
                $inscrit = $mesInscriptions->has($seance['id']);
                $complet = $seance['nb_inscrits'] >= $seance['places_max'];
                $ouverte = $seance['statut'] === 'ouverte';
            @endphp
            <div class="col-md-6 mb-3">
                <div class="bg-white p-3 rounded shadow-sm h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h2 class="h5 mb-1">{{ $service['nom'] ?? 'Service' }}</h2>
                        @if ($inscrit)
                            <span class="badge bg-success">{{ __('Inscrit') }}</span>
                        @elseif (! $ouverte)
                            <span class="badge bg-secondary">{{ __('Fermée') }}</span>
                        @elseif ($complet)
                            <span class="badge bg-warning text-dark">{{ __('Complet') }}</span>
                        @endif
                    </div>

                    @if ($service && $service['categorie'])
                        <div class="text-muted small mb-2">{{ $service['categorie'] }}</div>
                    @endif

                    @if ($service && $service['description'])
                        <p class="mb-2">{{ $service['description'] }}</p>
                    @endif

                    <ul class="list-unstyled text-muted small mb-3">
                        <li><strong>{{ __('Quand :') }}</strong> {{ \Carbon\Carbon::parse($seance['date_debut'])->format('d/m/Y à H:i') }}</li>
                        @if ($seance['lieu'])
                            <li><strong>{{ __('Où :') }}</strong> {{ $seance['lieu'] }}</li>
                        @endif
                        <li><strong>{{ __('Places :') }}</strong> {{ $seance['nb_inscrits'] }}/{{ $seance['places_max'] }}</li>
                    </ul>

                    <div class="mt-auto">
                        @if ($inscrit)
                            <form action="{{ route('mes-services.destroy', $seance['id']) }}" method="POST" onsubmit="return confirm('{{ __('Annuler votre inscription ?') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Me désinscrire') }}</button>
                            </form>
                        @elseif ($ouverte && ! $complet)
                            <form action="{{ route('mes-services.store', $seance['id']) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">{{ __("S'inscrire") }}</button>
                            </form>
                        @else
                            <button class="btn btn-secondary btn-sm" disabled>{{ __('Inscription indisponible') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-muted">{{ __('Aucune séance à venir pour le moment.') }}</p>
            </div>
        @endforelse
    </div>
@endsection
