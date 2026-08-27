<?php

namespace App\Console\Commands;

use App\Mail\RappelRenouvellement;
use App\Services\ApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnvoyerRappelsAdhesion extends Command
{
    protected $signature = 'adhesions:rappels {--jours=30 : Nombre de jours avant échéance déclenchant le rappel}';

    protected $description = "Envoie un rappel de renouvellement aux commerçants dont l'adhésion arrive à échéance";

    public function handle(ApiClient $api): int
    {
        $jours = (int) $this->option('jours');

        $commercants = $api->get("/commercants/a-relancer?jours={$jours}") ?? [];

        if ($commercants === []) {
            $this->info('Aucune adhésion à relancer.');

            return self::SUCCESS;
        }

        $envoyes = 0;
        $echecs = 0;

        foreach ($commercants as $commercant) {
            if (empty($commercant['email'])) {
                $this->warn("· {$commercant['nom']} : pas d'adresse email, ignoré.");
                continue;
            }

            $joursRestants = (int) Carbon::now()->startOfDay()
                ->diffInDays(Carbon::parse($commercant['date_renouvellement'])->startOfDay(), false);

            // Un envoi qui échoue (SMTP injoignable, adresse refusée) ne doit pas
            // interrompre la boucle : les autres commerçants doivent être relancés.
            try {
                Mail::to($commercant['email'])->send(new RappelRenouvellement($commercant, $joursRestants));
            } catch (\Throwable $e) {
                $this->error("· {$commercant['nom']} <{$commercant['email']}> : envoi impossible ({$e->getMessage()})");
                $echecs++;
                continue;
            }

            // Trace l'envoi pour ne pas relancer le même commerçant tous les jours.
            // Volontairement après l'envoi : un mail en échec sera retenté demain.
            $api->put("/commercants/{$commercant['id']}", [
                'date_dernier_rappel' => Carbon::now()->toISOString(),
            ]);

            $this->line("· Rappel envoyé à {$commercant['nom']} <{$commercant['email']}> ({$joursRestants} j)");
            $envoyes++;
        }

        $this->info("{$envoyes} rappel(s) envoyé(s)."
            . ($echecs > 0 ? " {$echecs} échec(s) d'envoi." : ''));

        return $echecs > 0 ? self::FAILURE : self::SUCCESS;
    }
}
