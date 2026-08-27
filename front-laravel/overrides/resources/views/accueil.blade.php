@extends('layouts.public')

@section('title', __('Association de lutte contre le gaspillage'))

@section('content')
    {{-- ------------------------------------------------------------------
         Front office : la seule page visible sans compte. Elle présente
         l'association, son fonctionnement et ses services, puis renvoie
         vers l'inscription (bénévole / commerçant) et la connexion.
         ------------------------------------------------------------------ --}}
    <section class="nmw-pub-hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="nmw-pub-pastille">{{ __('Association humanitaire — depuis 2013') }}</span>
                    <h1>{{ __('Les invendus d’aujourd’hui, les repas de demain.') }}</h1>
                    <p class="lead">{{ __("NO MORE WASTE récupère chaque jour les invendus des commerçants et les produits proches de leur date limite chez les particuliers, puis les redistribue par des tournées de distribution auprès des associations caritatives et des personnes en difficulté.") }}</p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('register') }}" class="btn btn-success">{{ __('Devenir bénévole') }}</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-success">{{ __('Devenir commerçant partenaire') }}</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="row g-4 text-center">
                                <div class="col-6">
                                    <p class="nmw-pub-chiffre mb-1">2013</p>
                                    <p class="nmw-pub-chiffre-libelle mb-0">{{ __('Création à Paris') }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="nmw-pub-chiffre mb-1">7</p>
                                    <p class="nmw-pub-chiffre-libelle mb-0">{{ __('Villes en France et à l’étranger') }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="nmw-pub-chiffre mb-1">14</p>
                                    <p class="nmw-pub-chiffre-libelle mb-0">{{ __('Salariés en CDI') }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="nmw-pub-chiffre mb-1">200+</p>
                                    <p class="nmw-pub-chiffre-libelle mb-0">{{ __('Bénévoles engagés') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="association" class="nmw-pub-section nmw-pub-section-claire">
        <div class="container">
            <h2 class="nmw-pub-titre-section">{{ __("L'association") }}</h2>
            <p class="nmw-pub-chapo">{{ __("Créée en 2013 à Paris, NO MORE WASTE lutte contre le gaspillage alimentaire et se développe en province comme à l'international. Nos équipes s'appuient sur un réseau de commerçants adhérents et de bénévoles présents sur chaque site.") }}</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <h3 class="h6">{{ __('En France') }}</h3>
                            <p class="text-muted mb-0">{{ __('Paris (siège), Nantes, Marseille, Limoges') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <h3 class="h6">{{ __("À l'international") }}</h3>
                            <p class="text-muted mb-0">{{ __('Naples, Porto, Dublin') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="fonctionnement" class="nmw-pub-section">
        <div class="container">
            <h2 class="nmw-pub-titre-section">{{ __('Notre fonctionnement') }}</h2>
            <p class="nmw-pub-chapo">{{ __('De la collecte chez le commerçant jusqu’à la distribution, chaque produit est tracé.') }}</p>

            <div class="row g-4">
                @foreach ([
                    ['1', __('La collecte'), __("Sur demande d'un commerçant ou d'un particulier, un camion part du siège récupérer les invendus et les produits proches de la date limite de consommation.")],
                    ['2', __("L'entreposage"), __('Chaque produit rapporté est référencé par son code-barres et entreposé, pour être retrouvé et réattribué très rapidement.')],
                    ['3', __('La distribution'), __("Des tournées sont organisées pour redistribuer les produits aux associations caritatives et aux particuliers en détresse, avec un récapitulatif de livraison.")],
                ] as [$numero, $titre, $texte])
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body p-4">
                                <div class="nmw-pub-etape-num">{{ $numero }}</div>
                                <h3 class="h6">{{ $titre }}</h3>
                                <p class="text-muted mb-0 small">{{ $texte }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="services" class="nmw-pub-section nmw-pub-section-claire">
        <div class="container">
            <h2 class="nmw-pub-titre-section">{{ __('Nos services aux adhérents') }}</h2>
            <p class="nmw-pub-chapo">{{ __("Au-delà des collectes, l'association propose un ensemble de services pour éviter le gaspillage, partager et économiser. Ils sont accessibles aux adhérents, moyennant une faible cotisation annuelle.") }}</p>

            <div class="row g-3">
                @foreach ($services as $service)
                    <div class="col-sm-6 col-lg-4">
                        <div class="card nmw-pub-service">
                            <div class="card-body">
                                @if (! empty($service['categorie']))
                                    <p class="nmw-pub-service-cat mb-1">{{ $service['categorie'] }}</p>
                                @endif
                                <h3 class="h6 mb-1">{{ $service['nom'] }}</h3>
                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($service['description'] ?? '', 110) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-muted small mt-3 mb-0">{{ __("Le planning des séances et les inscriptions sont accessibles depuis votre espace adhérent, une fois connecté.") }}</p>
        </div>
    </section>

    <section id="adherer" class="nmw-pub-section">
        <div class="container">
            <h2 class="nmw-pub-titre-section">{{ __('Nous rejoindre') }}</h2>
            <p class="nmw-pub-chapo">{{ __("Deux façons de participer, chacune avec son espace dédié sur le site.") }}</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="h5">{{ __('Bénévoles') }}</h3>
                            <p class="text-muted">{{ __("Vous proposez vos compétences — conduite, cuisine, plomberie, bricolage, électricité — et nous vous affectons aux collectes, aux tournées et aux services. Votre planning vous est envoyé au format Excel.") }}</p>
                            <a href="{{ route('register') }}" class="btn btn-success mt-auto align-self-start">{{ __('Déposer ma candidature') }}</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <h3 class="h5">{{ __('Commerçants') }}</h3>
                            <p class="text-muted">{{ __("Vous adhérez à l'association et nous venons récupérer vos invendus. Votre adhésion est suivie en ligne et un rappel automatique vous est envoyé avant son échéance.") }}</p>
                            <a href="{{ route('register') }}" class="btn btn-outline-success mt-auto align-self-start">{{ __('Adhérer comme commerçant') }}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nmw-pub-cta mt-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <h2 class="h4 mb-1">{{ __('Vous avez déjà un compte ?') }}</h2>
                        <p class="mb-0">{{ __('Retrouvez vos collectes, vos affectations et vos inscriptions aux services dans votre espace.') }}</p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('login') }}" class="btn btn-success">{{ __('Se connecter') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
