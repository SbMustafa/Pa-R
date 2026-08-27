{{-- Charte graphique NO MORE WASTE, partagée par les deux coques du site :
     layouts/app (back-office + espace adhérent) et layouts/public (site vitrine).
     Pour restyler le site, on ne touche qu'à ce fichier. --}}
    <style>
        /* ------------------------------------------------------------------
           Charte NO MORE WASTE. Tout est posé ici : les vues continuent
           d'utiliser les classes Bootstrap standard (btn-success, table,
           card, form-control...), on ne fait que les repeindre.
           ------------------------------------------------------------------ */
        :root {
            --nmw-vert: #1f6f4f;
            --nmw-vert-fonce: #1a5c42;
            --nmw-vert-clair: #e7f3ec;
            --nmw-encre: #12302a;
            --nmw-fond: #f4f6f5;
            --nmw-bordure: #e4e9e6;
            --nmw-texte: #1b2420;
            --nmw-gris: #6b7a74;
            --nmw-rayon: 14px;
            --nmw-ombre: 0 1px 2px rgba(16, 32, 26, .06), 0 8px 24px rgba(16, 32, 26, .04);
        }

        body {
            background: var(--nmw-fond);
            color: var(--nmw-texte);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4 { font-weight: 600; letter-spacing: -.01em; color: #16211c; }
        a { color: var(--nmw-vert); }
        a:hover { color: var(--nmw-vert-fonce); }

        /* --- Coque : sidebar fixe + zone de contenu --- */
        .nmw-shell { min-height: 100vh; }
        .nmw-sidebar {
            width: 268px;
            background: var(--nmw-encre);
            color: #cddbd4;
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 992px) {
            .nmw-shell { display: flex; }
            .nmw-sidebar { position: sticky; top: 0; height: 100vh; flex: 0 0 268px; }
            .nmw-main { flex: 1; min-width: 0; }
        }

        .nmw-brand {
            display: flex; align-items: center; gap: .7rem;
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }
        .nmw-brand-nom { color: #fff; font-weight: 600; line-height: 1.15; display: block; font-size: .95rem; }
        .nmw-brand-sous { color: #7f978d; font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
        .nmw-logo {
            width: 36px; height: 36px; flex: 0 0 36px;
            border-radius: 10px;
            background: var(--nmw-vert);
            color: #fff; font-weight: 700; font-size: .8rem;
            display: flex; align-items: center; justify-content: center;
        }

        .nmw-nav { padding: 1rem .75rem; flex: 1; overflow-y: auto; }
        .nmw-nav-titre {
            color: #6d857b; font-size: .68rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .09em;
            margin: 1.1rem .75rem .4rem;
        }
        .nmw-nav-titre:first-child { margin-top: 0; }
        .nmw-lien {
            display: flex; align-items: center; gap: .7rem;
            padding: .58rem .75rem;
            margin-bottom: 2px;
            border-radius: 9px;
            color: #cddbd4;
            text-decoration: none;
            font-size: .9rem;
            transition: background .12s ease, color .12s ease;
        }
        .nmw-lien:hover { background: rgba(255, 255, 255, .06); color: #fff; }
        .nmw-lien.is-active { background: var(--nmw-vert); color: #fff; font-weight: 500; }
        .nmw-lien svg { width: 18px; height: 18px; flex: 0 0 18px; opacity: .9; }

        .nmw-sidebar-pied { padding: .75rem; border-top: 1px solid rgba(255, 255, 255, .07); }

        /* --- Barre du haut --- */
        .nmw-topbar {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            background: #fff;
            border-bottom: 1px solid var(--nmw-bordure);
            padding: .7rem 1.25rem;
            position: sticky; top: 0; z-index: 5;
        }
        .nmw-user { display: flex; align-items: center; gap: .55rem; font-size: .875rem; }
        .nmw-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--nmw-vert-clair); color: var(--nmw-vert);
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: .8rem;
        }
        .nmw-role {
            background: var(--nmw-vert-clair); color: var(--nmw-vert);
            border-radius: 999px; padding: .15rem .55rem;
            font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em;
        }
        .nmw-contenu { padding: 1.75rem 1.5rem 3.5rem; max-width: 1320px; }
        @media (min-width: 992px) { .nmw-contenu { padding: 2rem 2.25rem 4rem; } }

        /* --- Repeinture des composants Bootstrap utilisés par les vues --- */
        .btn { border-radius: 9px; font-weight: 500; font-size: .875rem; }
        .btn-success {
            --bs-btn-bg: var(--nmw-vert); --bs-btn-border-color: var(--nmw-vert);
            --bs-btn-hover-bg: var(--nmw-vert-fonce); --bs-btn-hover-border-color: var(--nmw-vert-fonce);
            --bs-btn-active-bg: #164f39; --bs-btn-active-border-color: #164f39;
            --bs-btn-disabled-bg: var(--nmw-vert); --bs-btn-disabled-border-color: var(--nmw-vert);
        }
        .btn-outline-success {
            --bs-btn-color: var(--nmw-vert); --bs-btn-border-color: #c6dcd0;
            --bs-btn-hover-bg: var(--nmw-vert); --bs-btn-hover-border-color: var(--nmw-vert);
            --bs-btn-active-bg: var(--nmw-vert-fonce); --bs-btn-active-border-color: var(--nmw-vert-fonce);
        }
        .btn-outline-secondary {
            --bs-btn-color: #4b5b55; --bs-btn-border-color: var(--nmw-bordure);
            --bs-btn-hover-bg: #eef2f0; --bs-btn-hover-color: #16211c; --bs-btn-hover-border-color: #d5ddd9;
        }
        .bg-success { background-color: var(--nmw-vert) !important; }
        .text-success { color: var(--nmw-vert) !important; }
        .alert { border-radius: var(--nmw-rayon); border: 1px solid transparent; font-size: .9rem; }
        .alert-success { --bs-alert-bg: var(--nmw-vert-clair); --bs-alert-border-color: #c9e2d5; --bs-alert-color: #14523a; }
        .alert-danger { --bs-alert-bg: #fdecec; --bs-alert-border-color: #f6cfcf; --bs-alert-color: #8f2323; }

        .card { border: 1px solid var(--nmw-bordure); border-radius: var(--nmw-rayon); box-shadow: var(--nmw-ombre); }
        .card-header { background: #fafbfa; border-bottom: 1px solid var(--nmw-bordure); font-weight: 600; }
        .bg-white.rounded, form.bg-white { border: 1px solid var(--nmw-bordure); border-radius: var(--nmw-rayon) !important; }
        .shadow-sm { box-shadow: var(--nmw-ombre) !important; }

        .table {
            --bs-table-striped-bg: transparent;
            background: #fff;
            border-collapse: separate; border-spacing: 0;
            border-radius: var(--nmw-rayon); overflow: hidden;
            box-shadow: var(--nmw-ombre);
            margin-bottom: 1.5rem;
            font-size: .9rem;
        }
        .table > :not(caption) > * > * { padding: .8rem 1rem; border-bottom-color: #eef1ef; }
        .table thead th {
            background: #f7f9f8; color: #5d6d66;
            font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
            border-bottom: 1px solid var(--nmw-bordure); white-space: nowrap;
        }
        .table tbody tr:hover > * { background: #f8faf9; }
        .table tbody tr:last-child > * { border-bottom: 0; }

        .form-label { font-size: .82rem; font-weight: 600; color: #46554f; margin-bottom: .3rem; }
        .form-control, .form-select { border-radius: 9px; border-color: #dbe2de; font-size: .9rem; padding: .5rem .75rem; }
        .form-control:focus, .form-select:focus { border-color: #86c0a6; box-shadow: 0 0 0 .2rem rgba(31, 111, 79, .14); }
        .form-check-input:checked { background-color: var(--nmw-vert); border-color: var(--nmw-vert); }
        .badge { font-weight: 600; border-radius: 999px; padding: .35em .7em; font-size: .72rem; }
        .dropdown-menu { border-radius: 12px; border-color: var(--nmw-bordure); box-shadow: var(--nmw-ombre); font-size: .9rem; }
        .dropdown-item.active { background: var(--nmw-vert); }

        /* --- Pages publiques (connexion, inscription, mot de passe) --- */
        .nmw-auth { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .nmw-auth-entete { display: flex; align-items: center; gap: .7rem; margin-bottom: 1.5rem; font-weight: 600; color: var(--nmw-encre); }
        .nmw-auth-corps { width: 100%; max-width: 940px; }
        .nmw-auth .form-control { padding: .6rem .8rem; }
    </style>
