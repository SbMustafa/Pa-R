<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccueilController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\BenevoleController;
use App\Http\Controllers\CollecteController;
use App\Http\Controllers\CommercantController;
use App\Http\Controllers\InscriptionServiceController;
use App\Http\Controllers\LangueController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TourneeController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

// Racine du site : page de présentation publique pour les visiteurs,
// tableau de bord pour les utilisateurs connectés (voir AccueilController).
Route::get('/', [AccueilController::class, 'index']);

// Changement de langue (site multilingue : implantations à l'étranger).
Route::get('/langue/{langue}', [LangueController::class, 'changer'])->name('langue.changer');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/mot-de-passe-oublie', [PasswordController::class, 'showDemande'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordController::class, 'envoyerLien'])->name('password.email');
    Route::get('/definir-mot-de-passe/{token}', [PasswordController::class, 'showFormulaire'])->name('password.reset');
    Route::post('/definir-mot-de-passe', [PasswordController::class, 'enregistrer'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/mon-compte', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/mon-compte/mot-de-passe', [AccountController::class, 'updatePassword'])->name('account.password');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Comptes de connexion : création d'autres administrateurs, changement de rôle,
    // suppression. Les garde-fous anti-verrouillage sont dans le contrôleur.
    Route::get('utilisateurs', [UtilisateurController::class, 'index'])->name('utilisateurs.index');
    Route::get('utilisateurs/nouveau', [UtilisateurController::class, 'create'])->name('utilisateurs.create');
    Route::post('utilisateurs', [UtilisateurController::class, 'store'])->name('utilisateurs.store');
    Route::patch('utilisateurs/{utilisateur}/role', [UtilisateurController::class, 'updateRole'])->name('utilisateurs.role');
    Route::post('utilisateurs/{utilisateur}/lien', [UtilisateurController::class, 'renvoyerLien'])->name('utilisateurs.lien');
    Route::delete('utilisateurs/{utilisateur}', [UtilisateurController::class, 'destroy'])->name('utilisateurs.destroy');

    Route::resource('commercants', CommercantController::class)->except(['show']);

    Route::resource('benevoles', BenevoleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('benevoles/{id}/statut', [BenevoleController::class, 'updateStatut'])->name('benevoles.statut');
    Route::get('benevoles/{id}/planning', [BenevoleController::class, 'planningExcel'])->name('benevoles.planning');

    Route::resource('stocks', StockController::class)->except(['show']);

    Route::resource('collectes', CollecteController::class);
    Route::patch('collectes/{id}/statut', [CollecteController::class, 'updateStatut'])->name('collectes.statut');
    Route::post('collectes/{id}/produits', [CollecteController::class, 'storeProduit'])->name('collectes.produits.store');

    Route::resource('tournees', TourneeController::class);
    Route::patch('tournees/{id}/statut', [TourneeController::class, 'updateStatut'])->name('tournees.statut');
    Route::get('tournees/{id}/recapitulatif', [TourneeController::class, 'recapitulatif'])->name('tournees.recapitulatif');
    Route::post('tournees/{id}/lignes', [TourneeController::class, 'storeLigne'])->name('tournees.lignes.store');
    Route::delete('tournees/{id}/lignes/{ligne}', [TourneeController::class, 'destroyLigne'])->name('tournees.lignes.destroy');

    // Catalogue des services et planning des séances (les routes « seances »
    // sont déclarées avant la resource pour ne pas être capturées par /services/{id}).
    Route::get('services/planning', [ServiceController::class, 'planning'])->name('services.planning');
    Route::get('services/seances/nouvelle', [ServiceController::class, 'createSeance'])->name('services.seances.create');
    Route::post('services/seances', [ServiceController::class, 'storeSeance'])->name('services.seances.store');
    Route::get('services/seances/{id}', [ServiceController::class, 'editSeance'])->name('services.seances.edit');
    Route::put('services/seances/{id}', [ServiceController::class, 'updateSeance'])->name('services.seances.update');
    Route::delete('services/seances/{id}', [ServiceController::class, 'destroySeance'])->name('services.seances.destroy');
    Route::delete('services/seances/{id}/inscriptions/{inscription}', [ServiceController::class, 'destroyInscription'])
        ->name('services.inscriptions.destroy');

    Route::resource('services', ServiceController::class)->except(['show']);
});

// Services ouverts à tous les adhérents connectés (commerçants et bénévoles).
Route::middleware(['auth', 'role:commercant,benevole'])->group(function () {
    Route::get('/mes-services', [InscriptionServiceController::class, 'index'])->name('mes-services.index');
    Route::post('/mes-services/{seance}', [InscriptionServiceController::class, 'store'])->name('mes-services.store');
    Route::delete('/mes-services/{seance}', [InscriptionServiceController::class, 'destroy'])->name('mes-services.destroy');
});

Route::middleware(['auth', 'role:commercant'])->group(function () {
    Route::get('/ma-fiche', [CommercantController::class, 'mine'])->name('commercants.mine');
    Route::post('/ma-fiche', [CommercantController::class, 'storeMine'])->name('commercants.mine.store');
    Route::put('/ma-fiche', [CommercantController::class, 'updateMine'])->name('commercants.mine.update');
});

Route::middleware(['auth', 'role:benevole'])->group(function () {
    Route::get('/ma-candidature', [BenevoleController::class, 'mine'])->name('benevoles.mine');
    Route::post('/ma-candidature', [BenevoleController::class, 'storeMine'])->name('benevoles.mine.store');
    Route::put('/ma-candidature', [BenevoleController::class, 'updateMine'])->name('benevoles.mine.update');

    Route::get('/mes-affectations', [AffectationController::class, 'index'])->name('affectations.index');
    Route::get('/mes-affectations/planning', [AffectationController::class, 'planningExcel'])->name('affectations.planning');
});
