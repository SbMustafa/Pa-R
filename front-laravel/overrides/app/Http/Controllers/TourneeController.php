<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TourneeController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function index(Request $request): View
    {
        $statut = $request->query('statut', '');
        $path = '/tournees' . ($statut !== '' ? '?statut=' . urlencode($statut) : '');
        $tournees = $this->api->get($path) ?? [];

        return view('tournees.index', compact('tournees', 'statut'));
    }

    public function create(): View
    {
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('tournees.create', compact('benevoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $tournee = $this->api->post('/tournees', $data);

        return redirect()->route('tournees.show', $tournee['id'])
            ->with('success', 'Tournée planifiée.');
    }

    public function show(string $id): View
    {
        $tournee = $this->api->get("/tournees/{$id}");

        if (! isset($tournee['id'])) {
            abort(404, 'Tournée introuvable.');
        }

        $lignes = $this->api->get("/tournees/{$id}/lignes") ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];
        $produits = $this->api->get('/produits') ?? [];

        return view('tournees.show', compact('tournee', 'lignes', 'benevoles', 'produits'));
    }

    public function edit(string $id): View
    {
        $tournee = $this->api->get("/tournees/{$id}");
        $benevoles = $this->api->get('/benevoles') ?? [];

        return view('tournees.edit', compact('tournee', 'benevoles'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $this->validated($request);

        $this->api->put("/tournees/{$id}", $data);

        return redirect()->route('tournees.show', $id)->with('success', 'Tournée mise à jour.');
    }

    public function updateStatut(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'statut' => 'required|in:planifiee,en_cours,livree',
        ]);

        $this->api->put("/tournees/{$id}", $data);

        return redirect()->route('tournees.show', $id)->with('success', 'Statut de la tournée mis à jour.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->api->delete("/tournees/{$id}");

        return redirect()->route('tournees.index')
            ->with('success', 'Tournée supprimée, les produits ont été remis en stock.');
    }

    /** Charge un produit du stock dans la tournée (sortie de stock). */
    public function storeLigne(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'produit_id' => 'required|integer',
            'quantite' => 'required|integer|min:1',
        ]);

        $reponse = $this->api->post("/tournees/{$id}/lignes", [
            'produit_id' => (int) $data['produit_id'],
            'quantite' => (int) $data['quantite'],
        ]);

        if (isset($reponse['error'])) {
            return redirect()->route('tournees.show', $id)->withErrors(['quantite' => $reponse['error']]);
        }

        return redirect()->route('tournees.show', $id)->with('success', 'Produit chargé, stock mis à jour.');
    }

    /** Retire un produit de la tournée (remise en stock). */
    public function destroyLigne(string $id, string $ligneId): RedirectResponse
    {
        $this->api->delete("/tournees/{$id}/lignes/{$ligneId}");

        return redirect()->route('tournees.show', $id)->with('success', 'Produit retiré, remis en stock.');
    }

    /** Récapitulatif PDF de la livraison (exigé par le cahier des charges). */
    public function recapitulatif(string $id): Response
    {
        $tournee = $this->api->get("/tournees/{$id}");

        if (! isset($tournee['id'])) {
            abort(404, 'Tournée introuvable.');
        }

        $lignes = $this->api->get("/tournees/{$id}/lignes") ?? [];
        $benevoles = $this->api->get('/benevoles') ?? [];
        $benevole = $tournee['benevole_id']
            ? collect($benevoles)->firstWhere('id', $tournee['benevole_id'])
            : null;

        $pdf = Pdf::loadView('tournees.recapitulatif', compact('tournee', 'lignes', 'benevole'));

        $nom = 'recapitulatif-livraison-' . $tournee['id'] . '-'
            . Carbon::parse($tournee['date_tournee'])->format('Y-m-d') . '.pdf';

        return $pdf->download($nom);
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'destinataire' => 'required|string|max:255',
            'type_destinataire' => 'required|in:association,particulier',
            'adresse' => 'nullable|string|max:255',
            'benevole_id' => 'nullable|integer',
            'date_tournee' => 'required|date',
            'statut' => 'required|in:planifiee,en_cours,livree',
            'notes' => 'nullable|string|max:500',
        ]);

        $data['benevole_id'] = ! empty($data['benevole_id']) ? (int) $data['benevole_id'] : null;
        $data['adresse'] = $data['adresse'] ?? '';
        $data['notes'] = $data['notes'] ?? '';
        $data['date_tournee'] = Carbon::parse($data['date_tournee'])->toISOString();

        return $data;
    }
}
