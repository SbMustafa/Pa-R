# NO MORE WASTE - Mission 1 (développement d'applications)

Scaffold du projet : API en Go (Gin + GORM + MySQL) et front en Laravel (PHP), avec
une première fonctionnalité complète de bout en bout : **gestion des commerçants adhérents**.

## Architecture

```
PA/
├── docker-compose.yml       # orchestre mysql + api-go + front-laravel
├── scripts/install.sh       # script d'installation/démarrage en une commande
├── api-go/                  # API REST en Go (Gin + GORM)
│   ├── main.go
│   └── internal/
│       ├── config/          # connexion + migration DB
│       ├── models/          # modèles GORM (Commercant, ...)
│       └── handlers/        # handlers HTTP (CRUD)
└── front-laravel/           # front Laravel (back-office + auth)
    ├── Dockerfile           # génère un vrai projet Laravel au build + ajoute nos fichiers
    ├── docker-entrypoint.sh # migrate + seed au démarrage du conteneur
    └── overrides/           # nos fichiers applicatifs, copiés dans le squelette Laravel
        ├── app/Http/Controllers/{CommercantController,BenevoleController,DashboardController}.php
        ├── app/Http/Controllers/AccueilController.php    # front office : page d'accueil publique
        ├── app/Http/Controllers/UtilisateurController.php  # comptes de connexion (admin)
        ├── app/Http/Controllers/Auth/AuthController.php
        ├── app/Http/Middleware/EnsureRole.php   # middleware role:admin|commercant|benevole
        ├── app/Models/User.php                  # + colonne role
        ├── app/Services/ApiClient.php   # client HTTP vers l'API Go
        ├── database/migrations/, database/seeders/DatabaseSeeder.php
        ├── bootstrap/app.php            # enregistre l'alias de middleware "role"
        ├── routes/web.php
        └── resources/views/
            ├── accueil.blade.php           # page de présentation publique (front office)
            ├── layouts/_charte.blade.php   # charte graphique, partagée par les deux coques
            ├── layouts/public.blade.php    # coque vitrine : en-tête public + pied de page
            ├── layouts/app.blade.php       # coque : sidebar + barre du haut
            ├── layouts/_messages.blade.php # messages flash + erreurs de validation
            └── {auth,commercants,benevoles,...}/
```

Le front Laravel ne touche jamais la base de données directement : il appelle l'API Go
en HTTP/JSON via `App\Services\ApiClient` (qui utilise `Http::` de Laravel). C'est ce
pattern qu'il faut reproduire pour chaque nouvelle fonctionnalité (adhésions, stocks,
tournées, bénévoles, services...).

## Démarrer le projet

Prérequis : Docker Desktop démarré.

```bash
./scripts/install.sh
```

ou directement :

```bash
docker compose up --build
```

- Site : http://localhost:8000/ (page de présentation publique ; tableau de bord une fois connecté)
- Compte admin de démo (créé par le seeder) : `admin@nomorewaste.fr` / `password`
- Inscription libre (rôle bénévole ou commerçant) : http://localhost:8000/register
- Back-office commerçants (réservé au rôle `admin`) : http://localhost:8000/commercants
- API brute : http://localhost:8081/api/commercants
- Boîte mail de développement (mailpit) : http://localhost:8025
- MySQL exposé sur le port hôte 3307 (le 3306 par défaut était déjà pris par un autre projet Docker sur cette machine)

Ports choisis pour éviter les conflits avec d'autres projets Docker déjà lancés sur la
machine (un autre projet "UpcycleConnect" tourne en parallèle sur 80/8080/3306). Si tu
es sur une autre machine, tu peux remettre les ports standards dans `docker-compose.yml`.

## Interface

Une seule coque pour tout le site : `resources/views/layouts/app.blade.php`.
- **Connecté** : sidebar sombre à gauche (menu construit selon le rôle — back-office complet pour
  `admin`, espace personnel pour `commercant`/`benevole`, lien actif détecté via `request()->is()`),
  barre du haut avec sélecteur de langue, identité + rôle et déconnexion. Sous 992 px la sidebar
  devient un tiroir (`offcanvas-lg` de Bootstrap) ouvert par le bouton ☰.
- **Non connecté** (login, inscription, mot de passe) : page centrée sans sidebar.
- **Visiteur sur `/`** : le front office (`layouts/app.blade.php` n'est pas utilisé ici, voir plus bas).

La charte (vert `#1f6f4f`, fond `#f4f6f5`, cartes et tableaux arrondis) est posée dans le `<style>`
du layout **en surchargeant les variables CSS de Bootstrap** (`--bs-btn-bg` sur `.btn-success`,
`--bs-table-striped-bg`, etc.). Les vues continuent donc d'utiliser les classes Bootstrap standard
(`table`, `card`, `btn btn-success`, `form-control`) : pour restyler le site, on ne touche qu'au
layout, jamais aux 40 vues. Les icônes du menu sont des SVG inline (aucune police d'icônes à charger).

Le tableau de bord (`DashboardController`) affiche pour l'admin quatre compteurs (commerçants actifs
et adhésions à relancer, bénévoles validés et candidatures en attente, produits en stock, collectes
et tournées en cours) lus via l'API Go, dans un `try/catch` pour que la page reste affichable si
l'API ne répond pas.

## Front office : la page d'accueil publique (`/`)

Le sujet demande « à la fois un back-office (utilisé par NO MORE WASTE) et un front office
(utilisé par les clients) ». Le front office est **une seule page de présentation**, la seule
partie du site visible sans compte : `AccueilController` + `resources/views/accueil.blade.php`.

Elle présente l'association et ses implantations, le fonctionnement en trois étapes
(collecte → entreposage référencé par code-barres → tournées de distribution), le catalogue
des services, et deux appels à l'action vers `/register` (bénévole / commerçant partenaire).

Points d'implémentation :

| Choix | Raison |
|---|---|
| `/` reste la même route pour tout le monde : vitrine pour le visiteur, tableau de bord pour le connecté (`AccueilController::index` délègue à `DashboardController`) | les liens `url('/')` de la sidebar et la détection `request()->is('/')` continuent de fonctionner, rien à changer dans les 40 vues |
| Les services sont lus via l'API Go, comme le reste du site | le front ne touche jamais la base ; la vitrine montre le vrai catalogue |
| `try/catch` + liste de repli (`SERVICES_PAR_DEFAUT`) | une installation neuve a un catalogue vide et l'API peut ne pas répondre : la page d'accueil doit rester présentable |
| Coque dédiée `layouts/public.blade.php` (en-tête, ancres, sélecteur de langue, pied de page) | le layout `app` est construit autour de la sidebar et d'un contenu centré à 940 px, inadapté à une vitrine pleine largeur |
| La charte a été sortie dans `layouts/_charte.blade.php`, incluse par les deux coques | une seule source de vérité pour les couleurs : restyler le site reste une modification d'un seul fichier |
| Toutes les chaînes passent par `__()`, y compris la liste de repli | le sélecteur de langue est disponible dès la page d'accueil (fr / en / it / pt) |

## Gestion des comptes (`/utilisateurs`)

Page réservée à `role:admin` (`UtilisateurController`) : liste des comptes, création d'autres
**administrateurs**, changement de rôle, suppression, et bouton « Renvoyer le lien » qui relance
le mail de définition de mot de passe (utile si l'envoi a échoué à la création).

Le compte admin unique du seeder est un point de blocage : mot de passe partagé, aucune
traçabilité, et plus personne n'entre s'il est perdu. La création réutilise `CreationCompte`,
donc **aucun mot de passe n'est saisi ni vu par l'administrateur** : la personne reçoit un lien
d'activation à usage unique et choisit le sien.

Garde-fous (dans `UtilisateurController`, testés en requêtes directes, pas seulement masqués dans la vue) :

| Règle | Raison |
|---|---|
| On ne change pas son propre rôle, on ne supprime pas son propre compte | empêche de se verrouiller dehors |
| On ne touche pas au dernier administrateur | invariant d'accès au back-office ; déjà couvert par la règle précédente, gardé comme filet |
| Un compte rattaché à une fiche commerçant/bénévole ne peut être ni supprimé ni changé de rôle | son cycle de vie appartient à sa fiche : `CommercantController::destroy` supprime déjà le compte avec elle |
| Seul le rôle `admin` se crée ici | un commerçant/bénévole créé ici n'aurait pas de fiche côté API Go |

`/register` reste volontairement fermé aux rôles `commercant` et `benevole`
(`'role' => 'required|in:commercant,benevole'`) : personne ne se déclare admin depuis l'inscription
publique. Les fiches liées sont résolues en deux appels API (`/commercants`, `/benevoles`) indexés
par `user_id`, pas un appel par utilisateur.

## Envoi des mails (SMTP)

L'application envoie des mails à plusieurs endroits (lien d'activation de compte, mot de passe
oublié, rappel de renouvellement d'adhésion, planning Excel des bénévoles). Le SMTP se règle
**sans rebuild** et **sans identifiant dans l'image Docker** :

- `docker-compose.yml` passe les variables `MAIL_*` au conteneur `front-laravel` sous la forme
  `${MAIL_HOST:-mailpit}` ; Docker Compose les résout depuis le fichier **`.env` à la racine du
  projet** (non versionné, modèle dans `.env.example`).
- Les variables d'environnement du conteneur sont **prioritaires** sur le `.env` interne de Laravel
  (Dotenv n'écrase jamais une variable déjà définie), donc `front-laravel/overrides/env.docker.append`
  ne sert plus que de repli.
- **Sans fichier `.env`** à la racine → repli automatique sur **Mailpit** (`mailpit:1025`),
  boîte de développement consultable sur http://localhost:8025.
- **Avec le fichier `.env`** → envoi réel via le compte Gmail de l'association
  (`marinablue2025@gmail.com`, `smtp.gmail.com:587`, STARTTLS).

Après modification du `.env` : `docker compose up -d front-laravel` (pas de `--build`).

> **Gmail exige un mot de passe d'application.** Le mot de passe du compte est refusé par le SMTP
> (`534 5.7.9 Application-specific password required`). Il faut activer la validation en 2 étapes
> puis générer un mot de passe d'application de 16 caractères sur
> https://myaccount.google.com/apppasswords et le coller dans `MAIL_PASSWORD` du `.env`.
>
> Penser aussi qu'en SMTP réel les commandes planifiées (`adhesions:rappels`, `plannings:envoyer`)
> écrivent à de **vraies** adresses : pour les tests, garder Mailpit en supprimant le `.env`.

### Résistance aux pannes d'envoi

L'envoi est **synchrone** (`QUEUE_CONNECTION=sync`) : sans précaution, un SMTP en échec fait
remonter une exception jusqu'à l'utilisateur. Les quatre points d'envoi la rattrapent donc :

- `PasswordController::envoyerLien` (mot de passe oublié) journalise l'erreur et renvoie
  **toujours** le même message générique. Sans ce `try/catch`, une adresse inconnue répondait 302
  et un compte existant 500 — ce qui révélait quels comptes sont enregistrés, exactement ce que le
  message générique cherche à éviter.
- `CreationCompte::creerAvecLienActivation` retourne `['user' => ..., 'lien_envoye' => bool]` : le
  compte et la fiche commerçant/bénévole sont créés même si le mail échoue (sinon on laissait un
  `User` orphelin sans fiche côté API), et le back-office affiche « compte créé, mais l'email
  d'activation n'a pas pu être envoyé » au lieu d'annoncer un envoi qui n'a pas eu lieu.
- `adhesions:rappels` et `plannings:envoyer` rattrapent l'échec **par destinataire** : un mail
  refusé n'interrompt plus la boucle, `date_dernier_rappel` n'est écrit qu'après un envoi réussi
  (le commerçant est donc relancé le lendemain), et la commande sort en code d'erreur avec le
  nombre d'échecs.

Le lien de réinitialisation reste valable 60 minutes, et Laravel refuse une seconde demande pour la
même adresse dans les 60 secondes (`config/auth.php`, `passwords.users.throttle`).

Vérifier la configuration vue par Laravel :

```bash
docker exec pa-front-laravel-1 php artisan tinker --execute='print_r(config("mail.mailers.smtp"));'
```

## Ajouter une fonctionnalité (le pattern à répéter)

Exemple donné en soutenance : "ajoute un champ `notes` sur le commerçant".

**Côté Go** (`api-go/`) :
1. Ajouter le champ dans `internal/models/commercant.go`
2. GORM migre automatiquement le schéma au redémarrage (`AutoMigrate`)
3. Rien d'autre à faire si le champ doit juste être exposé en JSON (déjà géré par `ShouldBindJSON`/`json:"..."`)

**Côté Laravel** (`front-laravel/overrides/`) :
1. Ajouter le champ dans la validation du `CommercantController` (`store`/`update`)
2. Ajouter le champ dans la vue `resources/views/commercants/_form.blade.php`
3. L'afficher dans `resources/views/commercants/index.blade.php` si besoin

Exemple de nouvelle ressource complète (ex: Adhésions) :
1. `api-go/internal/models/adhesion.go` (modèle GORM)
2. `api-go/internal/handlers/adhesion.go` (List/Get/Create/Update/Delete, copier `commercant.go`)
3. Enregistrer les routes dans `api-go/main.go`
4. `front-laravel/overrides/app/Http/Controllers/AdhesionController.php` (copier `CommercantController.php`)
5. Routes + vues Blade correspondantes

## État

Testé de bout en bout (create/read/update/delete d'un commerçant, formulaire Laravel →
API Go → MySQL → réaffichage). Points corrigés pendant la mise au point initiale, à
garder en tête si tu recrées un environnement similaire :
- `gin-contrib/cors` récent exige Go ≥ 1.25 → image de build `golang:1.25`
- Composer bloque désormais par défaut l'installation de paquets ayant des advisories
  de sécurité connues (dont `laravel/framework` 11.x actuellement) → il faut
  `composer config --global policy.advisories.block false` avant `create-project`
- Les dépendances Laravel récentes exigent PHP ≥ 8.4 → image d'exécution `php:8.4-apache`
- Les extensions PHP `pdo_mysql`, `mbstring`, `zip`, etc. ne sont pas incluses par
  défaut dans l'image officielle `php:8.4-apache` → installées via `docker-php-ext-install`
  (nécessite `libonig-dev` pour compiler `mbstring`)
- Le champ `date_adhesion` (Go `time.Time`) est maintenant défaulté à `time.Now()` côté
  handler `Create` s'il n'est pas fourni, pour éviter une erreur MySQL en mode strict
  sur `'0000-00-00'`

## Authentification et rôles

Auth "maison" (pas de package Breeze/Fortify, pour rester simple à modifier en live) :
- 3 rôles stockés dans `users.role` : `admin`, `commercant`, `benevole`
- `AuthController` gère login/register/logout avec `Auth::attempt()` / `Auth::login()` (natif Laravel)
- Middleware `EnsureRole` (alias `role:xxx`, enregistré dans `bootstrap/app.php`) protège les routes
- Les tables `users` etc. vivent dans la **même base MySQL** que les données métier Go
  (`no_more_waste`), pour rester sur une seule base à sauvegarder/installer
- `docker-entrypoint.sh` exécute `migrate --force` puis `db:seed --force` (idempotent, via
  `updateOrCreate`) à chaque démarrage du conteneur front-laravel

Pattern pour protéger une nouvelle page par rôle :
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('adhesions', AdhesionController::class);
});
```

**Lien `User` ↔ `Commercant`** : un `User` de rôle `commercant` est lié à sa fiche via la
colonne `commercants.user_id` (ajoutée côté Go, `gorm:"uniqueIndex"`). La fiche est créée
automatiquement lors de l'inscription (`AuthController::register`, appel à
`POST /api/commercants` avec `user_id`, `nom`, `email`). Le commerçant connecté consulte et
modifie sa propre fiche sur `/ma-fiche` (`CommercantController::mine`/`updateMine`, retrouvée
via `GET /api/commercants/by-user/{user_id}`) — il n'a pas accès au back-office `/commercants`
réservé à `admin`.

**Stocks et collectes** : le modèle Go `Produit` référence chaque produit par un code-barre
unique (`NMW-<horodatage>-<aléa>` généré par l'API si le champ est laissé vide, pour le vrac
et les dons de particuliers) et le back-office `/stocks` permet de le retrouver par code-barre.
Le modèle `Collecte` (commerçant adhérent *ou* provenance libre, bénévole affecté, statut
`planifiee`/`en_cours`/`terminee`) représente un ramassage : sur `/collectes/{id}`, chaque
produit rapporté est réceptionné (code-barre scanné/saisi/généré) et entre en stock avec un
`produits.collecte_id` qui trace sa provenance. Supprimer une collecte conserve les produits
déjà entrés en stock (le lien est simplement coupé).

**Activation de compte et mot de passe oublié** : quand l'admin coche « créer un compte de
connexion » sur une fiche commerçant ou bénévole, aucun mot de passe n'est affiché ni transmis
à la main. `App\Services\CreationCompte` crée le `User` avec un mot de passe aléatoire que
personne ne connaît, puis envoie un lien d'activation (`App\Notifications\DefinirMotDePasse`,
en français, jeton natif Laravel à usage unique) ; la personne choisit elle-même son mot de
passe sur `/definir-mot-de-passe/{token}`. Le même mécanisme sert au « mot de passe oublié »
(`/mot-de-passe-oublie`, lien depuis la page de connexion), qui répond toujours la même chose
que l'adresse existe ou non pour ne pas révéler les comptes enregistrés.

**Site multilingue** : l'association étant implantée à Naples, Porto et Dublin, l'interface
existe en **français** (défaut), **anglais**, **italien** et **portugais**. Les textes des vues
passent par `__()` et les traductions vivent dans `lang/{en,it,pt}.json` (le français est la clé
elle-même, donc sans fichier). Le middleware `SetLocale` applique la langue stockée en session,
que l'on change par le sélecteur de la barre de navigation (`/langue/{code}`). `lang/fr/validation.php`
traduit aussi les messages de validation, affichés en anglais par défaut par Laravel.

**Plannings Excel envoyés aux bénévoles** : `App\Services\PlanningExcel` (phpoffice/phpspreadsheet,
ajouté au `composer require` du Dockerfile — l'extension PHP `gd` qu'il exige est installée dans
l'image) génère un vrai `.xlsx` listant les missions d'un bénévole sur une période. La commande
`php artisan plannings:envoyer [--jours=7] [--tous]` l'envoie par mail en pièce jointe à chaque
bénévole **validé** ayant des missions (l'option `--tous` inclut ceux qui n'en ont pas), et elle
est planifiée quotidiennement à 7h. Le fichier est aussi téléchargeable à la demande :
`/benevoles/{id}/planning` côté admin, `/mes-affectations/planning` côté bénévole.

**Affectation des bénévoles** : un bénévole validé peut être affecté à une séance de service
(depuis le planning), à une collecte ou à une tournée — ses capacités déclarées (chauffeur,
cuisinier, ...) sont affichées dans les listes de choix. `App\Services\AffectationsBenevole`
regroupe ces trois sources en une liste triée par date, utilisée par `/mes-affectations`
(espace bénévole) et par la fiche bénévole du back-office. Un bénévole dont la candidature
n'est pas validée n'apparaît dans aucune liste d'affectation.

**Services aux adhérents (propositions, plannings, inscriptions)** : trois modèles Go —
`Service` (catalogue : conseils anti-gaspi, cours de cuisine, partage de véhicules, ...),
`Seance` (créneau daté d'un service = le planning, avec lieu, places et bénévole affecté) et
`Inscription` (index unique `seance_id`+`user_id`). Côté admin : `/services` pour le catalogue,
`/services/planning` pour les séances et la liste des participants. Côté adhérent
(commerçants **et** bénévoles) : `/mes-services` affiche les séances à venir et permet de
s'inscrire ou se désinscrire. L'API refuse les inscriptions en double, sur séance complète,
fermée ou déjà passée.

**Rappel automatique de renouvellement d'adhésion** : la cotisation étant annuelle,
`date_renouvellement` est calculée à la création (`date_adhesion` + 1 an). L'API expose
`GET /api/commercants/a-relancer?jours=30` (adhésions actives arrivant à échéance ou déjà
expirées, hors commerçants déjà relancés pour ce cycle). Côté Laravel, la commande
`php artisan adhesions:rappels` envoie le mail (`App\Mail\RappelRenouvellement`, sujet
différent selon que l'échéance est proche ou dépassée) puis note `date_dernier_rappel` pour
ne pas relancer deux fois. Elle est planifiée quotidiennement à 8h (`routes/console.php`) ;
`docker-entrypoint.sh` lance en tâche de fond la boucle `php artisan schedule:run` chaque
minute qui remplace le cron. Les mails partent vers le SMTP configuré (voir « Envoi des
mails » plus haut) : par défaut le conteneur **mailpit**, consultable sur http://localhost:8025. Le back-office `/commercants` affiche une colonne
« Adhésion » avec un badge orange (< 30 jours) ou rouge (expirée).

**Tournées de distribution et récapitulatif PDF** : le modèle `Tournee` (destinataire =
association caritative ou particulier en détresse, bénévole en charge, statut
`planifiee`/`en_cours`/`livree`) et ses `LigneTournee`. Sur `/tournees/{id}`, charger un
produit le **sort du stock** dans une transaction (refus si la quantité est insuffisante) ;
le retirer de la tournée — ou supprimer la tournée — le **remet en stock**. Chaque ligne
recopie le nom et le code-barre du produit au moment du chargement, pour qu'un récapitulatif
de livraison passée reste exact même si la fiche produit change ensuite. Le récapitulatif PDF
exigé par le sujet est généré via `barryvdh/laravel-dompdf` (ajouté au `composer require` du
Dockerfile) sur `/tournees/{id}/recapitulatif`.

**Suivi des bénévoles (candidature → validation)** : même pattern que les commerçants, avec un
statut en plus (`Benevole.Statut` : `en_attente` / `valide` / `refuse`, colonne `benevoles.user_id`
en lien avec le compte). Une candidature est créée automatiquement à l'inscription en rôle
`benevole` (`POST /api/benevoles`, statut par défaut `en_attente`). Le bénévole complète sa fiche
(capacités : chauffeur/cuisinier/plombier/…, disponibilités) sur `/ma-candidature`
(`BenevoleController::mine`/`updateMine`) mais ne peut pas changer son propre statut. L'admin
liste les candidatures sur `/benevoles` et valide/refuse via `PATCH /benevoles/{id}/statut`
(`BenevoleController::updateStatut`). L'affectation à un service précis viendra avec le module
Services (pas encore développé).
