<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Front office : la page de présentation du site, seule page accessible sans
 * compte. Elle présente l'association, son fonctionnement et ses services,
 * puis renvoie vers l'inscription (bénévole / commerçant) et la connexion.
 */
class AccueilController extends Controller
{
    /**
     * Services affichés quand le catalogue de l'API est vide (installation
     * neuve) ou que l'API ne répond pas : la vitrine doit rester présentable.
     */
    protected const SERVICES_PAR_DEFAUT = [
        ['nom' => 'Conseils anti-gaspi', 'categorie' => 'Prévention', 'description' => "Des conseils pratiques pour conserver, cuisiner et ne plus jeter."],
        ['nom' => 'Cours de cuisine', 'categorie' => 'Ateliers', 'description' => "Apprendre à cuisiner les restes et les produits proches de leur date limite."],
        ['nom' => 'Partage de véhicules', 'categorie' => 'Entraide', 'description' => "Mutualiser les trajets et les véhicules entre adhérents."],
        ['nom' => 'Échange de services', 'categorie' => 'Entraide', 'description' => "Bricolage, électricité, plomberie : les adhérents s'entraident entre particuliers."],
        ['nom' => 'Services de réparation', 'categorie' => 'Réemploi', 'description' => "Faire réparer plutôt que jeter, avec l'aide de bénévoles qualifiés."],
        ['nom' => 'Gardiennage', 'categorie' => 'Entraide', 'description' => "Un coup de main entre adhérents pour garder un logement, un animal ou du matériel."],
    ];

    public function __construct(protected ApiClient $api)
    {
    }

    public function index(DashboardController $tableauDeBord): View|RedirectResponse
    {
        // Un utilisateur connecté qui revient sur « / » attend son tableau de
        // bord, et la sidebar pointe sur url('/') : on garde ce comportement.
        if (auth()->check()) {
            return $tableauDeBord->index();
        }

        return view('accueil', ['services' => $this->services()]);
    }

    /**
     * Catalogue des services lu via l'API Go, comme le reste du site (le front
     * ne touche jamais la base). En lecture seule et dans un try/catch : si
     * l'API est indisponible, la page d'accueil doit rester affichable.
     */
    protected function services(): array
    {
        try {
            $services = $this->api->get('/services');
        } catch (\Throwable) {
            $services = null;
        }

        $actifs = array_values(array_filter(
            is_array($services) ? $services : [],
            fn ($service) => is_array($service) && ! empty($service['actif']) && ! empty($service['nom']),
        ));

        if ($actifs) {
            return $actifs;
        }

        // Contenu de la vitrine et non données saisies : il suit la langue du site.
        return array_map(fn (array $service) => array_map(fn (string $valeur) => __($valeur), $service),
            self::SERVICES_PAR_DEFAUT);
    }
}
