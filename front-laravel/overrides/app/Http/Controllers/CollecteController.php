<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollecteController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function index(Request $request): View
    {
        $statut = $request->query('statut', '');
        $path = '/collectes' . ($statut !== '' ? '?statut=' . urlencode($statut) : '');
        $collectes = $this->api->get($path) ?? [];
        $commercants = $this->api->get('/commercants') ?? [];

        return view('collectes.index', compact('collectes', 'commercants', 'statut'));
    }

    public function create(): View
    {
        $commercants = $this->api->get('/commercants') ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('collectes.create', compact('commercants', 'benevoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $collecte = $this->api->post('/collectes', $data);

        return redirect()->route('collectes.show', $collecte['id'])
            ->with('success', 'Collecte planifiée.');
    }

    public function show(string $id): View
    {
        $collecte = $this->api->get("/collectes/{$id}");

        if (! isset($collecte['id'])) {
            abort(404, 'Collecte introuvable.');
        }

        $produits = $this->api->get("/produits?collecte_id={$id}") ?? [];
        $commercants = $this->api->get('/commercants') ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('collectes.show', compact('collecte', 'produits', 'commercants', 'benevoles'));
    }

    public function edit(string $id): View
    {
        $collecte = $this->api->get("/collectes/{$id}");
        $commercants = $this->api->get('/commercants') ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('collectes.edit', compact('collecte', 'commercants', 'benevoles'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $this->validated($request);

        $this->api->put("/collectes/{$id}", $data);

        return redirect()->route('collectes.show', $id)->with('success', 'Collecte mise à jour.');
    }

    public function updateStatut(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'statut' => 'required|in:planifiee,en_cours,terminee',
        ]);

        $this->api->put("/collectes/{$id}", $data);

        return redirect()->route('collectes.show', $id)->with('success', 'Statut de la collecte mis à jour.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->api->delete("/collectes/{$id}");

        return redirect()->route('collectes.index')->with('success', 'Collecte supprimée.');
    }

    /**
     * Réception d'un produit rapporté par cette collecte : il est référencé
     * (code-barre saisi ou scanné, sinon généré par l'API) et entre en stock.
     */
    public function storeProduit(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'code_barre' => 'nullable|string|max:64',
            'nom' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'emplacement' => 'nullable|string|max:100',
            'date_limite' => 'nullable|date',
        ]);
        $data['quantite'] = (int) $data['quantite'];
        $data['code_barre'] = $data['code_barre'] ?? '';
        $data['emplacement'] = $data['emplacement'] ?? '';
        $data['collecte_id'] = (int) $id;
        if (! empty($data['date_limite'])) {
            $data['date_limite'] = Carbon::parse($data['date_limite'])->toISOString();
        }

        $this->api->post('/produits', $data);

        return redirect()->route('collectes.show', $id)->with('success', 'Produit réceptionné et entré en stock.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'commercant_id' => 'nullable|integer',
            // Provenance exclusive : soit un commerçant adhérent, soit une source libre.
            'source_libre' => 'nullable|string|max:255|required_without:commercant_id|prohibited_unless:commercant_id,null',
            'benevole_id' => 'nullable|integer',
            'date_collecte' => 'required|date',
            'statut' => 'required|in:planifiee,en_cours,terminee',
            'notes' => 'nullable|string|max:500',
        ], [
            'source_libre.required_without' => 'Indiquez un commerçant adhérent ou la provenance (particulier, ...).',
            'source_libre.prohibited_unless' => 'Choisissez soit un commerçant adhérent, soit une provenance libre, pas les deux.',
        ]);

        $data['commercant_id'] = ! empty($data['commercant_id']) ? (int) $data['commercant_id'] : null;
        $data['benevole_id'] = ! empty($data['benevole_id']) ? (int) $data['benevole_id'] : null;
        $data['source_libre'] = $data['source_libre'] ?? '';
        $data['notes'] = $data['notes'] ?? '';
        $data['date_collecte'] = Carbon::parse($data['date_collecte'])->toISOString();

        return $data;
    }
}
