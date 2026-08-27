@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">{{ __('Services proposés') }}</h1>
        <div>
            <a href="{{ route('services.planning') }}" class="btn btn-outline-success">{{ __('Planning des séances') }}</a>
            <a href="{{ route('services.create') }}" class="btn btn-success">{{ __('+ Nouveau service') }}</a>
        </div>
    </div>

    <table class="table table-striped bg-white">
        <thead>
            <tr>
                <th>{{ __('Nom') }}</th>
                <th>{{ __('Catégorie') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Actif') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($services as $service)
                <tr>
                    <td>{{ $service['nom'] }}</td>
                    <td>{{ $service['categorie'] }}</td>
                    <td class="text-muted">{{ \Illuminate\Support\Str::limit($service['description'], 80) }}</td>
                    <td>
                        @if ($service['actif'])
                            <span class="badge bg-success">{{ __('Oui') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('Non') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('services.edit', $service['id']) }}" class="btn btn-sm btn-outline-primary">{{ __('Modifier') }}</a>
                        <form action="{{ route('services.destroy', $service['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer ce service ? Ses séances et les inscriptions seront supprimées.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Supprimer') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">{{ __('Aucun service proposé.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
