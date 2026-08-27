<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace adhérent : consultation des séances à venir et inscription.
 * Les services sont ouverts à tous les adhérents de l'association
 * (commerçants comme bénévoles), conformément au cahier des charges.
 */
class InscriptionServiceController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    /** Catalogue des séances à venir, ouvertes à l'inscription. */
    public function index(Request $request): View
    {
        $seances = $this->api->get('/seances?a_venir=1') ?? [];
        $services = collect($this->api->get('/services') ?? [])->keyBy('id');

        $mesInscriptions = collect($this->api->get('/inscriptions/by-user/' . $request->user()->id) ?? [])
            ->keyBy('seance_id');

        foreach ($seances as &$seance) {
            $seance['nb_inscrits'] = count($this->api->get("/seances/{$seance['id']}/inscriptions") ?? []);
        }
        unset($seance);

        return view('mes-services.index', compact('seances', 'services', 'mesInscriptions'));
    }

    public function store(Request $request, string $seanceId): RedirectResponse
    {
        $user = $request->user();

        $reponse = $this->api->post("/seances/{$seanceId}/inscriptions", [
            'user_id' => $user->id,
            'nom' => $user->name,
            'email' => $user->email,
        ]);

        if (isset($reponse['error'])) {
            return redirect()->route('mes-services.index')->withErrors(['inscription' => $reponse['error']]);
        }

        return redirect()->route('mes-services.index')->with('success', 'Inscription enregistrée.');
    }

    public function destroy(Request $request, string $seanceId): RedirectResponse
    {
        $inscription = collect($this->api->get('/inscriptions/by-user/' . $request->user()->id) ?? [])
            ->firstWhere('seance_id', (int) $seanceId);

        if ($inscription) {
            $this->api->delete("/seances/{$seanceId}/inscriptions/{$inscription['id']}");
        }

        return redirect()->route('mes-services.index')->with('success', 'Désinscription enregistrée.');
    }
}
