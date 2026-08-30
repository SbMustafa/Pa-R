<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NO MORE WASTE - @yield('title', __('Administration'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts._charte')
</head>
<body>
@auth
    @php
        $utilisateur = auth()->user();

        // Icônes en SVG inline : pas de police d'icônes à charger.
        $icones = [
            'accueil'   => '<path d="M3 10.2 12 3l9 7.2V21H3z"/><path d="M9 21v-6h6v6"/>',
            'commerce'  => '<path d="M4 9h16v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><path d="M3.5 9 5 3.8h14L20.5 9"/><path d="M9 21v-6h6v6"/>',
            'benevoles' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><path d="M16 5.5a3 3 0 0 1 0 5.4"/><path d="M18 20a5.5 5.5 0 0 0-2.2-4.4"/>',
            'collecte'  => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="7" cy="18.5" r="1.8"/><circle cx="17" cy="18.5" r="1.8"/>',
            'stock'     => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5"/><path d="M12 12v9"/>',
            'tournee'   => '<circle cx="6.5" cy="6" r="2.5"/><circle cx="17.5" cy="18" r="2.5"/><path d="M6.5 8.5V13a4 4 0 0 0 4 4h4.5"/>',
            'services'  => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 3v4M16 3v4"/>',
            'fiche'     => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
            'compte'    => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
            'comptes'   => '<path d="M12 3 5 6v5.5c0 4 2.9 7.6 7 8.5 4.1-.9 7-4.5 7-8.5V6z"/><circle cx="12" cy="10.5" r="2"/><path d="M8.7 16.2a3.7 3.7 0 0 1 6.6 0"/>',
        ];

        // Menu construit selon le rôle : back-office pour l'admin,
        // espace personnel pour les commerçants et les bénévoles.
        $menu = [];

        if ($utilisateur->isAdmin()) {
            $menu[] = [__('Pilotage'), [
                [__('Tableau de bord'), url('/'), '/', 'accueil'],
                [__('Utilisateurs'), route('utilisateurs.index'), 'utilisateurs*', 'comptes'],
            ]];
            $menu[] = [__('Activité'), [
                [__('Collectes'), route('collectes.index'), 'collectes*', 'collecte'],
                [__('Stock'), route('stocks.index'), 'stocks*', 'stock'],
                [__('Tournées'), route('tournees.index'), 'tournees*', 'tournee'],
            ]];
            $menu[] = [__('Réseau'), [
                [__('Commerçants'), route('commercants.index'), 'commercants*', 'commerce'],
                [__('Bénévoles'), route('benevoles.index'), 'benevoles*', 'benevoles'],
                [__('Services'), route('services.index'), 'services*', 'services'],
            ]];
        } else {
            $espace = [
                [__('Tableau de bord'), url('/'), '/', 'accueil'],
            ];

            if ($utilisateur->isCommercant()) {
                $espace[] = [__('Ma fiche'), route('commercants.mine'), 'ma-fiche', 'fiche'];
            }

            if ($utilisateur->isBenevole()) {
                $espace[] = [__('Ma candidature'), route('benevoles.mine'), 'ma-candidature', 'fiche'];
                $espace[] = [__('Mes affectations'), route('affectations.index'), 'mes-affectations*', 'tournee'];
            }

            $espace[] = [__('Services'), route('mes-services.index'), 'mes-services*', 'services'];

            $menu[] = [__('Mon espace'), $espace];
        }
    @endphp

    <div class="nmw-shell">
        <aside class="nmw-sidebar offcanvas-lg offcanvas-start" id="nmwSidebar" tabindex="-1">
            <div class="nmw-brand">
                <span class="nmw-logo"><img src="{{ asset('images/logo.svg') }}" alt="NO MORE WASTE"></span>
                <span>
                    <span class="nmw-brand-nom">NO MORE WASTE</span>
                    <span class="nmw-brand-sous">{{ $utilisateur->isAdmin() ? __('Back-office') : __('Espace adhérent') }}</span>
                </span>
                <button type="button" class="btn-close btn-close-white ms-auto d-lg-none"
                    data-bs-dismiss="offcanvas" data-bs-target="#nmwSidebar" aria-label="{{ __('Fermer') }}"></button>
            </div>

            <nav class="nmw-nav">
                @foreach ($menu as [$titre, $liens])
                    <p class="nmw-nav-titre">{{ $titre }}</p>
                    @foreach ($liens as [$libelle, $url, $motif, $icone])
                        <a href="{{ $url }}" class="nmw-lien {{ request()->is($motif) ? 'is-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                                stroke-linecap="round" stroke-linejoin="round">{!! $icones[$icone] !!}</svg>
                            {{ $libelle }}
                        </a>
                    @endforeach
                @endforeach
            </nav>

            <div class="nmw-sidebar-pied">
                <a href="{{ route('account.edit') }}" class="nmw-lien {{ request()->is('mon-compte*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round">{!! $icones['compte'] !!}</svg>
                    {{ __('Mon compte') }}
                </a>
            </div>
        </aside>

        <div class="nmw-main">
            <header class="nmw-topbar">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#nmwSidebar" aria-label="{{ __('Menu') }}">☰</button>

                <div class="d-none d-lg-block"></div>

                <div class="d-flex align-items-center gap-3">
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

                    <div class="nmw-user">
                        <span class="nmw-avatar">{{ strtoupper(mb_substr($utilisateur->name, 0, 1)) }}</span>
                        <span class="d-none d-sm-inline">{{ $utilisateur->name }}</span>
                        <span class="nmw-role d-none d-md-inline">{{ __($utilisateur->role) }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('Déconnexion') }}</button>
                    </form>
                </div>
            </header>

            <main class="nmw-contenu">
                @include('layouts._messages')
                @yield('content')
            </main>
        </div>
    </div>
@else
    <div class="nmw-auth">
        <div class="nmw-auth-entete">
            <span class="nmw-logo"><img src="{{ asset('images/logo.svg') }}" alt="NO MORE WASTE"></span> NO MORE WASTE
        </div>

        <div class="nmw-auth-corps">
            @include('layouts._messages')
            @yield('content')
        </div>

        <div class="dropdown mt-4">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">{{ strtoupper(app()->getLocale()) }}</button>
            <ul class="dropdown-menu">
                @foreach (\App\Http\Middleware\SetLocale::LANGUES as $code => $libelle)
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                            href="{{ route('langue.changer', $code) }}">{{ $libelle }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
