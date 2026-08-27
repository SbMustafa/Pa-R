<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ApiClient $api)
    {
    }

    public function index(): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return view('dashboard', [
            'chiffres' => auth()->user()->isAdmin() ? $this->chiffresAdmin() : [],
        ]);
    }

    /**
     * Compteurs affichés sur le tableau de bord du back-office.
     * L'API est interrogée en lecture seule ; si elle ne répond pas, la page
     * doit rester affichable, donc on retombe sur des listes vides.
     */
    protected function chiffresAdmin(): array
    {
        try {
            $commercants = $this->api->get('/commercants') ?: [];
            $benevoles = $this->api->get('/benevoles') ?: [];
            $produits = $this->api->get('/produits') ?: [];
            $collectes = $this->api->get('/collectes') ?: [];
            $tournees = $this->api->get('/tournees') ?: [];
            $aRelancer = $this->api->get('/commercants/a-relancer?jours=30') ?: [];
        } catch (\Throwable) {
            return [];
        }

        $compter = fn (array $liste, string $champ, string $valeur) => count(
            array_filter($liste, fn ($ligne) => ($ligne[$champ] ?? null) === $valeur)
        );

        return [
            'commercants' => count(array_filter($commercants, fn ($c) => ! empty($c['actif']))),
            'a_relancer' => count($aRelancer),
            'benevoles_valides' => $compter($benevoles, 'statut', 'valide'),
            'benevoles_en_attente' => $compter($benevoles, 'statut', 'en_attente'),
            'produits' => count($produits),
            'unites' => array_sum(array_column($produits, 'quantite')),
            'collectes' => $compter($collectes, 'statut', 'planifiee') + $compter($collectes, 'statut', 'en_cours'),
            'tournees' => $compter($tournees, 'statut', 'planifiee') + $compter($tournees, 'statut', 'en_cours'),
        ];
    }
}
