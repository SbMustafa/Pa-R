<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ApiClient;
use App\Services\CreationCompte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercantController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected CreationCompte $comptes,
    ) {
    }

    public function index(): View
    {
        $commercants = $this->api->get('/commercants') ?? [];

        return view('commercants.index', compact('commercants'));
    }

    public function create(): View
    {
        return view('commercants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
            'creer_compte' => 'nullable|boolean',
            'email_connexion' => 'nullable|required_if:creer_compte,1|email|max:255|unique:users,email',
        ]);

        $payload = collect($data)->except(['creer_compte', 'email_connexion'])->toArray();
        $compteCree = false;
        $lienEnvoye = true;

        if ($request->boolean('creer_compte')) {
            $compte = $this->comptes->creerAvecLienActivation(
                $data['nom'], $data['email_connexion'], 'commercant'
            );
            $payload['user_id'] = $compte['user']->id;
            $compteCree = true;
            $lienEnvoye = $compte['lien_envoye'];
        }

        $this->api->post('/commercants', $payload);

        $message = match (true) {
            $compteCree && $lienEnvoye => "Commerçant créé. Un lien d'activation a été envoyé à {$data['email_connexion']}.",
            // Le compte existe bel et bien : seul le mail a échoué, on le dit clairement
            // plutôt que d'annoncer un envoi qui n'a pas eu lieu.
            $compteCree => "Commerçant créé, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP). La personne peut demander un nouveau lien via « Mot de passe oublié ».",
            default => 'Commerçant créé avec succès.',
        };

        return redirect()->route('commercants.index')->with('success', $message);
    }

    public function edit(string $id): View
    {
        $commercant = $this->api->get("/commercants/{$id}");

        return view('commercants.edit', compact('commercant'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $existing = $this->api->get("/commercants/{$id}");

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
            'creer_compte' => 'nullable|boolean',
            'email_connexion' => 'nullable|required_if:creer_compte,1|email|max:255|unique:users,email',
        ]);

        $payload = collect($data)->except(['creer_compte', 'email_connexion'])->toArray();
        $compteCree = false;
        $lienEnvoye = true;

        if (empty($existing['user_id']) && $request->boolean('creer_compte')) {
            $compte = $this->comptes->creerAvecLienActivation(
                $data['nom'], $data['email_connexion'], 'commercant'
            );
            $payload['user_id'] = $compte['user']->id;
            $compteCree = true;
            $lienEnvoye = $compte['lien_envoye'];
        }

        $this->api->put("/commercants/{$id}", $payload);

        $message = match (true) {
            $compteCree && $lienEnvoye => "Commerçant mis à jour. Un lien d'activation a été envoyé à {$data['email_connexion']}.",
            // Le compte existe bel et bien : seul le mail a échoué, on le dit clairement
            // plutôt que d'annoncer un envoi qui n'a pas eu lieu.
            $compteCree => "Commerçant mis à jour, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP). La personne peut demander un nouveau lien via « Mot de passe oublié ».",
            default => 'Commerçant mis à jour.',
        };

        return redirect()->route('commercants.index')->with('success', $message);
    }

    public function destroy(string $id): RedirectResponse
    {
        // Le compte de connexion n'existe que pour accéder à cette fiche : on le
        // supprime avec elle, sinon il reste orphelin et bloque la réutilisation
        // de son adresse email.
        $commercant = $this->api->get("/commercants/{$id}");

        $this->api->delete("/commercants/{$id}");

        if (! empty($commercant['user_id'])) {
            User::where('id', $commercant['user_id'])->delete();
        }

        return redirect()->route('commercants.index')->with('success', 'Commerçant supprimé.');
    }

    /**
     * Espace commerçant. Si aucune fiche n'est liée au compte, on propose de la
     * créer plutôt que de laisser l'utilisateur devant une erreur sans issue.
     */
    public function mine(Request $request): View
    {
        $commercant = $this->api->get('/commercants/by-user/' . $request->user()->id);

        if (! isset($commercant['id'])) {
            return view('commercants.creer-ma-fiche');
        }

        return view('commercants.mine', compact('commercant'));
    }

    /** Création de sa fiche par un commerçant qui n'en a pas encore. */
    public function storeMine(Request $request): RedirectResponse
    {
        if (isset($this->api->get('/commercants/by-user/' . $request->user()->id)['id'])) {
            return redirect()->route('commercants.mine');
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['email'] = $request->user()->email;

        $this->api->post('/commercants', $data);

        return redirect()->route('commercants.mine')->with('success', 'Votre fiche a été créée.');
    }

    public function updateMine(Request $request): RedirectResponse
    {
        $commercant = $this->api->get('/commercants/by-user/' . $request->user()->id);

        if (! isset($commercant['id'])) {
            return redirect()->route('commercants.mine');
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'siret' => 'nullable|string|max:20',
        ]);

        $this->api->put("/commercants/{$commercant['id']}", $data);

        return redirect()->route('commercants.mine')->with('success', 'Votre fiche a été mise à jour.');
    }
}
