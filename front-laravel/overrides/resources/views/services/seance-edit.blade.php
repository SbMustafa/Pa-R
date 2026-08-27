@extends('layouts.app')

@section('title', 'Séance du ' . \Carbon\Carbon::parse($seance['date_debut'])->format('d/m/Y'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Séance du {{ \Carbon\Carbon::parse($seance['date_debut'])->format('d/m/Y à H:i') }}</h1>
        <a href="{{ route('services.planning') }}" class="btn btn-link">{{ __('← Retour au planning') }}</a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <form action="{{ route('services.seances.update', $seance['id']) }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf
                @method('PUT')
                @include('services._seance-form', ['seance' => $seance])
                <button type="submit" class="btn btn-success">{{ __('Mettre à jour') }}</button>
            </form>
        </div>

        <div class="col-md-5">
            <div class="bg-white p-4 rounded shadow-sm">
                <h2 class="h5 mb-3">
                    Participants inscrits ({{ count($inscriptions) }}/{{ $seance['places_max'] }})
                </h2>

                <table class="table table-sm">
                    <tbody>
                        @forelse ($inscriptions as $inscription)
                            <tr>
                                <td>
                                    {{ $inscription['nom'] }}<br>
                                    <span class="text-muted small">{{ $inscription['email'] }}</span>
                                </td>
                                <td class="text-end align-middle">
                                    <form action="{{ route('services.inscriptions.destroy', [$seance['id'], $inscription['id']]) }}" method="POST" onsubmit="return confirm('{{ __('Désinscrire ce participant ?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Retirer') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-muted">{{ __("Aucun inscrit pour l'instant.") }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
