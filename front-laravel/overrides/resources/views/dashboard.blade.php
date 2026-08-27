@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">{{ __('Bonjour') }} {{ auth()->user()->name }}</h1>
        <p class="text-muted mb-0">
            {{ __('Vous êtes connecté(e) en tant que') }} <strong>{{ __(auth()->user()->role) }}</strong>.
        </p>
    </div>

    @if (auth()->user()->isAdmin() && $chiffres)
        @php
            $tuiles = [
                [__('Commerçants actifs'), $chiffres['commercants'], __(':n adhésion(s) à relancer', ['n' => $chiffres['a_relancer']]), $chiffres['a_relancer'] > 0, route('commercants.index')],
                [__('Bénévoles validés'), $chiffres['benevoles_valides'], __(':n candidature(s) en attente', ['n' => $chiffres['benevoles_en_attente']]), $chiffres['benevoles_en_attente'] > 0, route('benevoles.index')],
                [__('Produits en stock'), $chiffres['produits'], __(':n unité(s)', ['n' => $chiffres['unites']]), false, route('stocks.index')],
                [__('Collectes en cours'), $chiffres['collectes'], __(':n tournée(s) à livrer', ['n' => $chiffres['tournees']]), false, route('collectes.index')],
            ];
        @endphp

        <div class="row g-3 mb-4">
            @foreach ($tuiles as [$libelle, $valeur, $detail, $alerte, $url])
                <div class="col-6 col-xl-3">
                    <a href="{{ $url }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="text-uppercase text-muted" style="font-size:.72rem;letter-spacing:.05em;font-weight:600">{{ $libelle }}</div>
                            <div class="fw-semibold" style="font-size:2rem;line-height:1.2;color:#16211c">{{ $valeur }}</div>
                            <div class="{{ $alerte ? 'text-warning-emphasis' : 'text-muted' }}" style="font-size:.82rem">{{ $detail }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @php
        $raccourcis = [];

        if (auth()->user()->isAdmin()) {
            $raccourcis = [
                [__('Collectes'), __('Planifier un ramassage et réceptionner les produits.'), route('collectes.index')],
                [__('Stock'), __('Retrouver un produit par son code-barre.'), route('stocks.index')],
                [__('Tournées'), __('Charger une tournée et éditer le récapitulatif PDF.'), route('tournees.index')],
                [__('Commerçants'), __('Gérer les adhésions et les renouvellements.'), route('commercants.index')],
                [__('Bénévoles'), __('Valider les candidatures et envoyer les plannings.'), route('benevoles.index')],
                [__('Services'), __('Catalogue, planning des séances et inscriptions.'), route('services.index')],
            ];
        }

        if (auth()->user()->isCommercant()) {
            $raccourcis[] = [__('Ma fiche'), __('Consulter et mettre à jour mes informations d\'adhérent.'), route('commercants.mine')];
        }

        if (auth()->user()->isBenevole()) {
            $raccourcis[] = [__('Ma candidature'), __('Mes capacités et mes disponibilités.'), route('benevoles.mine')];
            $raccourcis[] = [__('Mes affectations'), __('Mes missions à venir et mon planning Excel.'), route('affectations.index')];
        }

        if (auth()->user()->isCommercant() || auth()->user()->isBenevole()) {
            $raccourcis[] = [__('Services aux adhérents'), __('S\'inscrire aux séances proposées par l\'association.'), route('mes-services.index')];
        }
    @endphp

    <div class="row g-3">
        @foreach ($raccourcis as [$titre, $description, $url])
            <div class="col-md-6 col-xl-4">
                <a href="{{ $url }}" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <div class="fw-semibold mb-1" style="color:#16211c">{{ $titre }} <span class="text-success">→</span></div>
                        <div class="text-muted" style="font-size:.87rem">{{ $description }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
