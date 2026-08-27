<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ApiClient;
use App\Services\CreationCompte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Gestion des comptes de connexion par l'administration.
 *
 * Le compte admin unique livré par le seeder est un point de blocage : mot de passe
 * partagé, aucune traçabilité, et plus personne ne rentre s'il est perdu. Cette page
 * permet donc à un administrateur d'en créer d'autres — sans jamais saisir ni voir de
 * mot de passe, la personne reçoit un lien d'activation et choisit le sien.
 */
class UtilisateurController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected CreationCompte $comptes,
    ) {
    }

    public function index(): View
    {
        return view('utilisateurs.index', [
            'utilisateurs' => User::orderBy('role')->orderBy('name')->get(),
            'fiches' => $this->fichesParUtilisateur(),
        ]);
    }

    public function create(): View
    {
        return view('utilisateurs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        // Seul le rôle admin se crée ici. Un commerçant ou un bénévole doit être créé
        // depuis sa propre page, qui crée aussi sa fiche côté API Go : sinon on
        // obtiendrait un compte sans fiche, invisible des listes métier.
        $compte = $this->comptes->creerAvecLienActivation($data['name'], $data['email'], 'admin');

        $message = $compte['lien_envoye']
            ? "Administrateur créé. Un lien d'activation a été envoyé à {$data['email']}."
            : "Administrateur créé, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP). Utilisez « Renvoyer le lien » depuis la liste.";

        return redirect()->route('utilisateurs.index')->with('success', $message);
    }

    public function updateRole(Request $request, User $utilisateur): RedirectResponse
    {
        $data = $request->validate(['role' => 'required|in:admin,commercant,benevole']);

        if ($erreur = $this->refusSiDernierAcces($utilisateur, 'changer le rôle de')) {
            return back()->withErrors(['role' => $erreur]);
        }

        if ($data['role'] === $utilisateur->role) {
            return back()->with('success', 'Aucun changement : le rôle est déjà celui-là.');
        }

        // Changer le rôle d'un compte rattaché à une fiche métier le couperait de son
        // espace (/ma-fiche, /ma-candidature) tout en laissant la fiche pointer sur lui.
        if ($fiche = $this->fichesParUtilisateur()[$utilisateur->id] ?? null) {
            return back()->withErrors(['role' => "Ce compte est rattaché à une fiche {$fiche['libelle']} : son rôle ne peut pas changer tant que la fiche existe."]);
        }

        $utilisateur->update(['role' => $data['role']]);

        return back()->with('success', "Rôle de {$utilisateur->name} changé en « {$data['role']} ».");
    }

    public function renvoyerLien(User $utilisateur): RedirectResponse
    {
        try {
            $statut = Password::sendResetLink(['email' => $utilisateur->email]);
        } catch (\Throwable $e) {
            Log::error("Renvoi du lien d'activation à {$utilisateur->email} impossible : {$e->getMessage()}");

            return back()->withErrors(['email' => "L'email n'a pas pu être envoyé (vérifier la configuration SMTP)."]);
        }

        if ($statut === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Un lien a déjà été envoyé à cette adresse il y a moins d\'une minute.']);
        }

        return back()->with('success', "Lien de définition du mot de passe envoyé à {$utilisateur->email}.");
    }

    public function destroy(User $utilisateur): RedirectResponse
    {
        if ($erreur = $this->refusSiDernierAcces($utilisateur, 'supprimer')) {
            return back()->withErrors(['role' => $erreur]);
        }

        // Le cycle de vie d'un compte commerçant/bénévole appartient à sa fiche :
        // supprimer la fiche supprime déjà le compte (CommercantController::destroy).
        if ($fiche = $this->fichesParUtilisateur()[$utilisateur->id] ?? null) {
            return back()->withErrors(['role' => "Ce compte est rattaché à une fiche {$fiche['libelle']} : supprimez la fiche, ce qui supprimera aussi le compte."]);
        }

        // Un jeton d'activation en attente n'aurait plus de destinataire.
        Password::broker()->deleteToken($utilisateur);
        $utilisateur->delete();

        return back()->with('success', 'Compte supprimé.');
    }

    /**
     * Garde-fous anti-verrouillage : on ne se retire pas soi-même l'accès, et on ne
     * touche pas au dernier administrateur. Le second cas est déjà couvert par le
     * premier (pour qu'un autre compte soit admin, il faut qu'ils soient au moins
     * deux), mais on garde le contrôle explicite : c'est l'invariant qui compte, et
     * il doit tenir même si une autre voie d'accès est ajoutée plus tard.
     */
    protected function refusSiDernierAcces(User $utilisateur, string $action): ?string
    {
        if ($utilisateur->id === auth()->id()) {
            return "Vous ne pouvez pas {$action} votre propre compte.";
        }

        if ($utilisateur->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return "Impossible de {$action} le dernier administrateur : plus personne n'aurait accès au back-office.";
        }

        return null;
    }

    /**
     * Fiches métier indexées par user_id, pour savoir si un compte est rattaché à un
     * commerçant ou à un bénévole (deux appels API, pas un par utilisateur).
     *
     * @return array<int, array{libelle: string, id: int}>
     */
    protected function fichesParUtilisateur(): array
    {
        $fiches = [];

        foreach (['commercants' => 'commerçant', 'benevoles' => 'bénévole'] as $ressource => $libelle) {
            foreach ($this->api->get("/{$ressource}") ?? [] as $ligne) {
                if (! empty($ligne['user_id'])) {
                    $fiches[$ligne['user_id']] = ['libelle' => $libelle, 'id' => $ligne['id']];
                }
            }
        }

        return $fiches;
    }
}
