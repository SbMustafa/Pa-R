<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Back-office des services proposés aux adhérents : le catalogue (propositions)
 * et le planning des séances, avec la liste des inscrits.
 */
class ServiceController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function index(): View
    {
        $services = $this->api->get('/services') ?? [];

        return view('services.index', compact('services'));
    }

    public function create(): View
    {
        return view('services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->api->post('/services', $this->validated($request));

        return redirect()->route('services.index')->with('success', 'Service créé.');
    }

    public function edit(string $id): View
    {
        $service = $this->api->get("/services/{$id}");

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->api->put("/services/{$id}", $this->validated($request));

        return redirect()->route('services.index')->with('success', 'Service mis à jour.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->api->delete("/services/{$id}");

        return redirect()->route('services.index')
            ->with('success', 'Service supprimé, ainsi que ses séances et inscriptions.');
    }

    /** Planning : toutes les séances, avec le nombre d'inscrits. */
    public function planning(Request $request): View
    {
        $serviceId = $request->query('service_id', '');
        $path = '/seances' . ($serviceId !== '' ? '?service_id=' . urlencode($serviceId) : '');

        $seances = $this->api->get($path) ?? [];
        $services = collect($this->api->get('/services') ?? [])->keyBy('id');
        $benevoles = collect($this->api->get('/benevoles') ?? [])->keyBy('id');

        foreach ($seances as &$seance) {
            $seance['nb_inscrits'] = count($this->api->get("/seances/{$seance['id']}/inscriptions") ?? []);
        }
        unset($seance);

        return view('services.planning', compact('seances', 'services', 'benevoles', 'serviceId'));
    }

    public function createSeance(): View
    {
        $services = $this->api->get('/services?actif=1') ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('services.seance-create', compact('services', 'benevoles'));
    }

    public function storeSeance(Request $request): RedirectResponse
    {
        $this->api->post('/seances', $this->validatedSeance($request));

        return redirect()->route('services.planning')->with('success', 'Séance ajoutée au planning.');
    }

    public function editSeance(string $id): View
    {
        $seance = $this->api->get("/seances/{$id}");
        $services = $this->api->get('/services') ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];
        $inscriptions = $this->api->get("/seances/{$id}/inscriptions") ?? [];

        return view('services.seance-edit', compact('seance', 'services', 'benevoles', 'inscriptions'));
    }

    public function updateSeance(Request $request, string $id): RedirectResponse
    {
        $this->api->put("/seances/{$id}", $this->validatedSeance($request));

        return redirect()->route('services.planning')->with('success', 'Séance mise à jour.');
    }

    public function destroySeance(string $id): RedirectResponse
    {
        $this->api->delete("/seances/{$id}");

        return redirect()->route('services.planning')
            ->with('success', 'Séance supprimée, ainsi que ses inscriptions.');
    }

    /** Désinscription d'un participant par l'administration. */
    public function destroyInscription(string $id, string $inscriptionId): RedirectResponse
    {
        $this->api->delete("/seances/{$id}/inscriptions/{$inscriptionId}");

        return redirect()->route('services.seances.edit', $id)->with('success', 'Participant désinscrit.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'categorie' => 'nullable|string|max:100',
            'actif' => 'nullable|boolean',
        ]);

        $data['description'] = $data['description'] ?? '';
        $data['categorie'] = $data['categorie'] ?? '';
        $data['actif'] = $request->boolean('actif');

        return $data;
    }

    protected function validatedSeance(Request $request): array
    {
        $data = $request->validate([
            'service_id' => 'required|integer',
            'date_debut' => 'required|date',
            'lieu' => 'nullable|string|max:255',
            'places_max' => 'required|integer|min:1',
            'benevole_id' => 'nullable|integer',
            'statut' => 'required|in:ouverte,fermee,annulee',
        ]);

        $data['service_id'] = (int) $data['service_id'];
        $data['places_max'] = (int) $data['places_max'];
        $data['benevole_id'] = ! empty($data['benevole_id']) ? (int) $data['benevole_id'] : null;
        $data['lieu'] = $data['lieu'] ?? '';
        $data['date_debut'] = Carbon::parse($data['date_debut'])->toISOString();

        return $data;
    }
}
