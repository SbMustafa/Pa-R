<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function index(Request $request): View
    {
        $recherche = $request->query('q', '');
        $path = '/produits' . ($recherche !== '' ? '?q=' . urlencode($recherche) : '');
        $produits = $this->api->get($path) ?? [];

        return view('stocks.index', compact('produits', 'recherche'));
    }

    public function create(): View
    {
        return view('stocks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code_barre' => 'nullable|string|max:64',
            'nom' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'emplacement' => 'nullable|string|max:100',
            'date_limite' => 'nullable|date',
        ]);
        $data['quantite'] = (int) $data['quantite'];
        $data['emplacement'] = $data['emplacement'] ?? '';
        // Laissé vide, l'API Go génère une référence interne (produits sans code-barre d'origine).
        $data['code_barre'] = $data['code_barre'] ?? '';
        if (! empty($data['date_limite'])) {
            $data['date_limite'] = Carbon::parse($data['date_limite'])->toISOString();
        }

        $this->api->post('/produits', $data);

        return redirect()->route('stocks.index')->with('success', 'Produit ajouté au stock.');
    }

    public function edit(string $id): View
    {
        $produit = $this->api->get("/produits/{$id}");

        return view('stocks.edit', compact('produit'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'code_barre' => 'required|string|max:64',
            'nom' => 'required|string|max:255',
            'quantite' => 'required|integer|min:0',
            'emplacement' => 'nullable|string|max:100',
            'date_limite' => 'nullable|date',
        ]);
        $data['quantite'] = (int) $data['quantite'];
        $data['emplacement'] = $data['emplacement'] ?? '';
        if (! empty($data['date_limite'])) {
            $data['date_limite'] = Carbon::parse($data['date_limite'])->toISOString();
        }

        $this->api->put("/produits/{$id}", $data);

        return redirect()->route('stocks.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->api->delete("/produits/{$id}");

        return redirect()->route('stocks.index')->with('success', 'Produit retiré du stock.');
    }
}
