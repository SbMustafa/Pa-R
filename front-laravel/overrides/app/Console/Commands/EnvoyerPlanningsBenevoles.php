<?php

namespace App\Console\Commands;

use App\Mail\PlanningBenevole;
use App\Services\ApiClient;
use App\Services\PlanningExcel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerPlanningsBenevoles extends Command
{
    protected $signature = 'plannings:envoyer
        {--jours=7 : Nombre de jours couverts par le planning}
        {--tous : Envoyer aussi aux bénévoles sans mission sur la période}';

    protected $description = 'Envoie à chaque bénévole validé son planning au format Excel';

    public function handle(ApiClient $api, PlanningExcel $planning): int
    {
        $jours = (int) $this->option('jours');
        $benevoles = $api->get('/benevoles') ?? [];
        $envoyes = 0;
        $echecs = 0;

        foreach ($benevoles as $benevole) {
            if ($benevole['statut'] !== 'valide') {
                continue;
            }
            if (empty($benevole['email'])) {
                $this->warn("· {$benevole['nom']} : pas d'adresse email, ignoré.");
                continue;
            }

            $affectations = $planning->affectationsPeriode($benevole['id'], $jours);

            if ($affectations === [] && ! $this->option('tous')) {
                $this->line("· {$benevole['nom']} : aucune mission sur la période, ignoré.");
                continue;
            }

            $contenu = $planning->construire($benevole, $affectations, $jours);

            // Un envoi qui échoue ne doit pas priver les autres bénévoles de leur planning.
            try {
                Mail::to($benevole['email'])->send(new PlanningBenevole(
                    $benevole,
                    $affectations,
                    $jours,
                    $contenu,
                    $planning->nomFichier($benevole),
                ));
            } catch (\Throwable $e) {
                $this->error("· {$benevole['nom']} <{$benevole['email']}> : envoi impossible ({$e->getMessage()})");
                $echecs++;
                continue;
            }

            $this->line("· Planning envoyé à {$benevole['nom']} <{$benevole['email']}> ("
                . count($affectations) . ' mission(s))');
            $envoyes++;
        }

        $this->info("{$envoyes} planning(s) envoyé(s)."
            . ($echecs > 0 ? " {$echecs} échec(s) d'envoi." : ''));

        return $echecs > 0 ? self::FAILURE : self::SUCCESS;
    }
}
