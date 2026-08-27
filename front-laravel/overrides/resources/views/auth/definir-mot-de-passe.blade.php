@extends('layouts.app')

@section('title', 'Définir mon mot de passe')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h3 mb-3">{{ __('Définir mon mot de passe') }}</h1>

            <form action="{{ route('password.update') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Nouveau mot de passe') }}</label>
                    <input type="password" name="password" class="form-control" required autofocus>
                    <div class="form-text">{{ __('8 caractères minimum.') }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Confirmer le mot de passe') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __('Enregistrer') }}</button>
            </form>
        </div>
    </div>
@endsection
