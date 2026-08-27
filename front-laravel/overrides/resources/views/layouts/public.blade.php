<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NO MORE WASTE - @yield('title', __('Association de lutte contre le gaspillage'))</title>
    <meta name="description" content="{{ __("NO MORE WASTE récupère chaque jour les invendus des commerçants et les redistribue par des tournées de distribution. Adhérez pour accéder à nos services.") }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts._charte')
    <style>
        /* ------------------------------------------------------------------
           Front office : le site vitrine, seule partie visible sans compte.
           Tout est préfixé .nmw-pub- pour ne jamais retomber sur le back-office.
           ------------------------------------------------------------------ */
        .nmw-pub-entete {
            position: sticky; top: 0; z-index: 10;
            background: rgba(255, 255, 255, .92);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--nmw-bordure);
        }
        .nmw-pub-entete .container { display: flex; align-items: center; gap: 1rem; padding-top: .7rem; padding-bottom: .7rem; }
        .nmw-pub-marque { display: flex; align-items: center; gap: .7rem; text-decoration: none; }
        .nmw-pub-marque span:last-child { font-weight: 600; color: var(--nmw-encre); letter-spacing: -.01em; }
        .nmw-pub-nav { display: flex; gap: 1.4rem; margin-left: 1.5rem; }
        .nmw-pub-nav a { color: #4b5b55; text-decoration: none; font-size: .9rem; font-weight: 500; }
        .nmw-pub-nav a:hover { color: var(--nmw-vert); }

        .nmw-pub-hero { background: linear-gradient(180deg, var(--nmw-vert-clair) 0%, var(--nmw-fond) 100%); padding: 4rem 0 3.5rem; }
        .nmw-pub-hero h1 { font-size: clamp(2rem, 4.5vw, 3rem); line-height: 1.12; margin-bottom: 1rem; }
        .nmw-pub-hero p.lead { color: #3f5049; font-size: 1.05rem; max-width: 34rem; }
        .nmw-pub-pastille {
            display: inline-flex; align-items: center; gap: .45rem;
            background: #fff; border: 1px solid #c9e2d5; color: var(--nmw-vert);
            border-radius: 999px; padding: .3rem .8rem;
            font-size: .78rem; font-weight: 600; margin-bottom: 1.1rem;
        }
        .nmw-pub-section { padding: 3.5rem 0; }
        .nmw-pub-section-claire { background: #fff; border-block: 1px solid var(--nmw-bordure); }
        .nmw-pub-titre-section { font-size: 1.65rem; margin-bottom: .5rem; }
        .nmw-pub-chapo { color: var(--nmw-gris); max-width: 42rem; margin-bottom: 2rem; }

        .nmw-pub-chiffre { font-size: 2rem; font-weight: 700; color: var(--nmw-vert); line-height: 1; }
        .nmw-pub-chiffre-libelle { color: var(--nmw-gris); font-size: .85rem; }

        .nmw-pub-etape-num {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--nmw-vert); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .9rem; margin-bottom: .9rem;
        }
        .nmw-pub-service { height: 100%; }
        .nmw-pub-service .card-body { padding: 1.25rem; }
        .nmw-pub-service-cat {
            color: var(--nmw-vert); font-size: .72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .nmw-pub-cta { background: var(--nmw-encre); color: #d7e4de; border-radius: var(--nmw-rayon); padding: 2.25rem; }
        .nmw-pub-cta h2 { color: #fff; }

        .nmw-pub-pied { background: var(--nmw-encre); color: #93a89f; padding: 2.5rem 0 2rem; font-size: .875rem; }
        .nmw-pub-pied a { color: #cddbd4; text-decoration: none; }
        .nmw-pub-pied a:hover { color: #fff; }
        .nmw-pub-pied h3 { color: #fff; font-size: .8rem; text-transform: uppercase; letter-spacing: .07em; margin-bottom: .7rem; }
    </style>
</head>
<body>
<header class="nmw-pub-entete">
    <div class="container">
        <a href="{{ url('/') }}" class="nmw-pub-marque">
            <span class="nmw-logo">NW</span>
            <span>NO MORE WASTE</span>
        </a>

        <nav class="nmw-pub-nav d-none d-lg-flex">
            <a href="#association">{{ __("L'association") }}</a>
            <a href="#fonctionnement">{{ __('Notre fonctionnement') }}</a>
            <a href="#services">{{ __('Nos services') }}</a>
            <a href="#adherer">{{ __('Nous rejoindre') }}</a>
        </nav>

        <div class="ms-auto d-flex align-items-center gap-2">
            {{-- Sélecteur de langue : l'association est implantée à l'étranger,
                 le visiteur doit pouvoir changer de langue dès la page d'accueil. --}}
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">{{ strtoupper(app()->getLocale()) }}</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach (\App\Http\Middleware\SetLocale::LANGUES as $code => $libelle)
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                                href="{{ route('langue.changer', $code) }}">{{ $libelle }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm">{{ __('Se connecter') }}</a>
            <a href="{{ route('register') }}" class="btn btn-success btn-sm d-none d-sm-inline-block">{{ __('Devenir adhérent') }}</a>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

<footer class="nmw-pub-pied">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="nmw-logo">NW</span>
                    <span class="text-white fw-semibold">NO MORE WASTE</span>
                </div>
                <p class="mb-0">{{ __("Association humanitaire de lutte contre le gaspillage, créée en 2013 à Paris.") }}</p>
            </div>
            <div class="col-6 col-md-3">
                <h3>{{ __('Nos implantations') }}</h3>
                <p class="mb-1">{{ __('Paris (siège), Nantes, Marseille, Limoges') }}</p>
                <p class="mb-0">{{ __('Naples, Porto, Dublin') }}</p>
            </div>
            <div class="col-6 col-md-4">
                <h3>{{ __('Accès au site') }}</h3>
                <p class="mb-1"><a href="{{ route('login') }}">{{ __('Se connecter') }}</a></p>
                <p class="mb-1"><a href="{{ route('register') }}">{{ __('Créer un compte') }}</a></p>
                <p class="mb-0"><a href="{{ route('password.request') }}">{{ __('Mot de passe oublié') }}</a></p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
