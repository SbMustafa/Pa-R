<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rappel automatique de renouvellement des adhésions commerçants (exigé par le sujet).
// Exécuté chaque jour à 8h par le planificateur lancé dans docker-entrypoint.sh.
Schedule::command('adhesions:rappels')->dailyAt('08:00');

// Planning Excel envoyé chaque jour aux bénévoles ayant des missions à venir
// (« tous les jours, des plannings sont créés, édités et envoyés aux différents
// bénévoles sous la forme de fichiers Excel »).
Schedule::command('plannings:envoyer')->dailyAt('07:00');
