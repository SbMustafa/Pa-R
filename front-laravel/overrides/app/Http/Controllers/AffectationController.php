<?php

namespace App\Http\Controllers;

use App\Services\AffectationsBenevole;
use App\Services\ApiClient;
use App\Services\PlanningExcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Espace bénévole : les missions auxquelles il a été affecté
 * (séances de service qu'il anime, collectes et tournées à conduire).
 */
class AffectationController extends Controller
{
    public function __construct(
        protected ApiClient $api,
        protected AffectationsBenevole $affectations,
        protected PlanningExcel $planning,
    ) {
    }

    /** Le bénévole télécharge son propre planning au format Excel. */
    public function planningExcel(Request $request): Response|RedirectResponse
    {
        $benevole = $this->api->get('/benevoles/by-user/' . $request->user()->id);

        if (! isset($benevole['id']) || $benevole['statut'] !== 'valide') {
            return redirect()->route('affectations.index')
                ->withErrors(['planning' => "Aucun planning n'est disponible pour votre compte."]);
        }

        $jours = (int) $request->query('jours', 7);
        $affectations = $this->planning->affectationsPeriode($benevole['id'], $jours);
        $contenu = $this->planning->construire($benevole, $affectations, $jours);

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $this->planning->nomFichier($benevole) . '"',
        ]);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $benevole = $this->api->get('/benevoles/by-user/' . $request->user()->id);

        // Pas encore de candidature : on renvoie vers le dépôt de candidature
        // plutôt que d'afficher une erreur sans issue.
        if (! isset($benevole['id'])) {
            return redirect()->route('benevoles.mine');
        }

        $affectations = $benevole['statut'] === 'valide'
            ? $this->affectations->pour($benevole['id'])
            : [];

        return view('mes-affectations.index', compact('benevole', 'affectations'));
    }
}
