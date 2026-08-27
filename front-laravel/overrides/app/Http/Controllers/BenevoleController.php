<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AffectationsBenevole;
use App\Services\ApiClient;
use App\Services\CreationCompte;
use App\Services\PlanningExcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BenevoleController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected CreationCompte $comptes,
        protected AffectationsBenevole $affectations,
        protected PlanningExcel $planning,
    ) {
    }

    /** Télécharge le planning Excel d'un bénévole (même fichier que celui envoyé par mail). */
    public function planningExcel(Request $request, string $id): Response
    {
        $benevole = $this->api->get("/benevoles/{$id}");

        if (! isset($benevole['id'])) {
            abort(404, 'Bénévole introuvable.');
        }

        $jours = (int) $request->query('jours', 7);
        $affectations = $this->planning->affectationsPeriode((int) $id, $jours);
        $contenu = $this->planning->construire($benevole, $affectations, $jours);

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $this->planning->nomFichier($benevole) . '"',
        ]);
    }

    public function index(): View
    {
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('benevoles.index', compact('benevoles'));
    }

    public function create(): View
    {
        return view('benevoles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'capacites' => 'nullable|array',
            'capacites.*' => 'string|max:50',
            'disponibilites' => 'nullable|string|max:255',
            'creer_compte' => 'nullable|boolean',
            'email_connexion' => 'nullable|required_if:creer_compte,1|email|max:255|unique:users,email',
        ]);
        $data['capacites'] = implode(', ', $data['capacites'] ?? []);

        $payload = collect($data)->except(['creer_compte', 'email_connexion'])->toArray();
        $compteCree = false;
        $lienEnvoye = true;

        if ($request->boolean('creer_compte')) {
            $compte = $this->comptes->creerAvecLienActivation(
                $data['nom'], $data['email_connexion'], 'benevole'
            );
            $payload['user_id'] = $compte['user']->id;
            $compteCree = true;
            $lienEnvoye = $compte['lien_envoye'];
        }

        $this->api->post('/benevoles', $payload);

        $message = match (true) {
            $compteCree && $lienEnvoye => "Bénévole créé. Un lien d'activation a été envoyé à {$data['email_connexion']}.",
            // Le compte existe bel et bien : seul le mail a échoué, on le dit clairement
            // plutôt que d'annoncer un envoi qui n'a pas eu lieu.
            $compteCree => "Bénévole créé, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP). La personne peut demander un nouveau lien via « Mot de passe oublié ».",
            default => 'Bénévole créé avec succès.',
        };

        return redirect()->route('benevoles.index')->with('success', $message);
    }

    public function edit(string $id): View
    {
        $benevole = $this->api->get("/benevoles/{$id}");
        $affectations = $this->affectations->pour((int) $id);

        return view('benevoles.edit', compact('benevole', 'affectations'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $existing = $this->api->get("/benevoles/{$id}");

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'capacites' => 'nullable|array',
            'capacites.*' => 'string|max:50',
            'disponibilites' => 'nullable|string|max:255',
            'creer_compte' => 'nullable|boolean',
            'email_connexion' => 'nullable|required_if:creer_compte,1|email|max:255|unique:users,email',
        ]);
        $data['capacites'] = implode(', ', $data['capacites'] ?? []);

        $payload = collect($data)->except(['creer_compte', 'email_connexion'])->toArray();
        $compteCree = false;
        $lienEnvoye = true;

        if (empty($existing['user_id']) && $request->boolean('creer_compte')) {
            $compte = $this->comptes->creerAvecLienActivation(
                $data['nom'], $data['email_connexion'], 'benevole'
            );
            $payload['user_id'] = $compte['user']->id;
            $compteCree = true;
            $lienEnvoye = $compte['lien_envoye'];
        }

        $this->api->put("/benevoles/{$id}", $payload);

        $message = match (true) {
            $compteCree && $lienEnvoye => "Bénévole mis à jour. Un lien d'activation a été envoyé à {$data['email_connexion']}.",
            // Le compte existe bel et bien : seul le mail a échoué, on le dit clairement
            // plutôt que d'annoncer un envoi qui n'a pas eu lieu.
            $compteCree => "Bénévole mis à jour, mais l'email d'activation n'a pas pu être envoyé (vérifier la configuration SMTP). La personne peut demander un nouveau lien via « Mot de passe oublié ».",
            default => 'Bénévole mis à jour.',
        };

        return redirect()->route('benevoles.index')->with('success', $message);
    }

    public function updateStatut(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'statut' => 'required|in:en_attente,valide,refuse',
        ]);

        $this->api->put("/benevoles/{$id}", $data);

        return redirect()->route('benevoles.index')->with('success', 'Statut mis à jour.');
    }

    public function destroy(string $id): RedirectResponse
    {
        // Le compte de connexion n'existe que pour accéder à cette candidature :
        // on le supprime avec elle, sinon il reste orphelin et bloque la
        // réutilisation de son adresse email.
        $benevole = $this->api->get("/benevoles/{$id}");

        $this->api->delete("/benevoles/{$id}");

        if (! empty($benevole['user_id'])) {
            User::where('id', $benevole['user_id'])->delete();
        }

        return redirect()->route('benevoles.index')->with('success', 'Bénévole supprimé.');
    }

    /**
     * Espace bénévole. Si aucune candidature n'est liée au compte (compte créé
     * avant le module, ou fiche supprimée), on propose de la déposer plutôt que
     * de laisser l'utilisateur devant une erreur sans issue.
     */
    public function mine(Request $request): View
    {
        $benevole = $this->api->get('/benevoles/by-user/' . $request->user()->id);

        if (! isset($benevole['id'])) {
            return view('benevoles.creer-ma-candidature');
        }

        return view('benevoles.mine', compact('benevole'));
    }

    /** Dépôt de candidature par un utilisateur qui n'en a pas encore. */
    public function storeMine(Request $request): RedirectResponse
    {
        if (isset($this->api->get('/benevoles/by-user/' . $request->user()->id)['id'])) {
            return redirect()->route('benevoles.mine');
        }

        $data = $request->validate([
            'telephone' => 'nullable|string|max:20',
            'capacites' => 'nullable|array',
            'capacites.*' => 'string|max:50',
            'disponibilites' => 'nullable|string|max:255',
        ]);

        $this->api->post('/benevoles', [
            'user_id' => $request->user()->id,
            'nom' => $request->user()->name,
            'email' => $request->user()->email,
            'telephone' => $data['telephone'] ?? '',
            'capacites' => implode(', ', $data['capacites'] ?? []),
            'disponibilites' => $data['disponibilites'] ?? '',
        ]);

        return redirect()->route('benevoles.mine')
            ->with('success', 'Votre candidature a été déposée, elle est en attente de validation.');
    }

    public function updateMine(Request $request): RedirectResponse
    {
        $benevole = $this->api->get('/benevoles/by-user/' . $request->user()->id);

        if (! isset($benevole['id'])) {
            return redirect()->route('benevoles.mine');
        }

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'capacites' => 'nullable|array',
            'capacites.*' => 'string|max:50',
            'disponibilites' => 'nullable|string|max:255',
        ]);
        $data['capacites'] = implode(', ', $data['capacites'] ?? []);

        $this->api->put("/benevoles/{$benevole['id']}", $data);

        return redirect()->route('benevoles.mine')->with('success', 'Votre candidature a été mise à jour.');
    }
}
