@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h3 mb-3">{{ __('Inscription') }}</h1>

            <form action="{{ route('register') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">{{ __('Nom') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Je suis un(e)') }}</label>
                    <select name="role" id="register-role" class="form-select" required>
                        <option value="benevole" {{ old('role') === 'benevole' ? 'selected' : '' }}>{{ __('Bénévole') }}</option>
                        <option value="commercant" {{ old('role') === 'commercant' ? 'selected' : '' }}>{{ __('Commerçant') }}</option>
                    </select>
                </div>

                @php
                    // old('capacites') est un tableau (cases à cocher) après un échec de validation.
                    $ancienesCapacites = old('capacites', []);
                    $capacitesSelectionnees = is_array($ancienesCapacites)
                        ? $ancienesCapacites
                        : array_filter(array_map('trim', explode(',', $ancienesCapacites)));
                    $capacitesDisponibles = ['Chauffeur', 'Cuisinier', 'Plombier', 'Électricien', 'Bricolage'];
                @endphp

                <div id="register-benevole-fields">
                    <div class="mb-3 border-top pt-3">
                        <label class="form-label d-block">{{ __('Vos capacités') }}</label>
                        @foreach ($capacitesDisponibles as $capacite)
                            <div class="form-check form-check-inline">
                                <input type="checkbox" name="capacites[]" class="form-check-input" value="{{ $capacite }}"
                                    id="reg-capacite-{{ $loop->index }}"
                                    {{ in_array($capacite, $capacitesSelectionnees) ? 'checked' : '' }}>
                                <label class="form-check-label" for="reg-capacite-{{ $loop->index }}">{{ $capacite }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Disponibilités') }}</label>
                        <input type="text" name="disponibilites" class="form-control"
                            placeholder="{{ __('Ex : lundi, mercredi soir, week-ends') }}"
                            value="{{ old('disponibilites') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Mot de passe') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('Confirmer le mot de passe') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">{{ __("S'inscrire") }}</button>
            </form>

            <p class="text-center mt-3">
                {{ __('Déjà un compte ?') }} <a href="{{ route('login') }}">{{ __('Se connecter') }}</a>
            </p>
        </div>
    </div>

    <script>
        (function () {
            var roleSelect = document.getElementById('register-role');
            var benevoleFields = document.getElementById('register-benevole-fields');

            function toggle() {
                benevoleFields.style.display = roleSelect.value === 'benevole' ? '' : 'none';
            }

            roleSelect.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endsection
