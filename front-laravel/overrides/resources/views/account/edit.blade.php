@extends('layouts.app')

@section('title', 'Mon compte')

@section('content')
    <h1 class="h3 mb-3">{{ __('Mon compte') }}</h1>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <form action="{{ route('account.password') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">{{ __('Mot de passe actuel') }}</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Nouveau mot de passe') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Confirmer le nouveau mot de passe') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Mettre à jour le mot de passe') }}</button>
            </form>
        </div>
    </div>
@endsection
