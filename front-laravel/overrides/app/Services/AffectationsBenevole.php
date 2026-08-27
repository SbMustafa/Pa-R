<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Rassemble les affectations d'un bénévole (séances de service qu'il anime,
 * collectes et tournées dont il est le chauffeur) en une seule liste triée
 * par date, utilisée par l'espace bénévole et par le back-office.
 */
class AffectationsBenevole
{
    public function __construct(protected ApiClient $api)
    {
    }

    /**
     * @param  bool  $aVenir  limiter aux affectations à venir
     * @return array<int, array{type: string, libelle: string, date: string, lieu: string, statut: string, url: ?string}>
     */
    public function pour(int $benevoleId, bool $aVenir = false): array
    {
        $filtre = $aVenir ? '&a_venir=1' : '';
        $affectations = [];

        $services = collect($this->api->get('/services') ?? [])->keyBy('id');
        foreach ($this->api->get("/seances?benevole_id={$benevoleId}{$filtre}") ?? [] as $seance) {
            $affectations[] = [
                'type' => 'Service',
                'libelle' => $services[$seance['service_id']]['nom'] ?? 'Séance',
                'date' => $seance['date_debut'],
                'lieu' => $seance['lieu'],
                'statut' => $seance['statut'],
                'url' => route('services.seances.edit', $seance['id']),
            ];
        }

        $commercants = collect($this->api->get('/commercants') ?? [])->keyBy('id');
        foreach ($this->api->get("/collectes?benevole_id={$benevoleId}{$filtre}") ?? [] as $collecte) {
            $provenance = $collecte['commercant_id']
                ? ($commercants[$collecte['commercant_id']]['nom'] ?? 'Commerçant')
                : ($collecte['source_libre'] ?: 'Provenance non précisée');

            $affectations[] = [
                'type' => 'Collecte',
                'libelle' => $provenance,
                'date' => $collecte['date_collecte'],
                'lieu' => $collecte['commercant_id'] ? ($commercants[$collecte['commercant_id']]['ville'] ?? '') : '',
                'statut' => $collecte['statut'],
                'url' => route('collectes.show', $collecte['id']),
            ];
        }

        foreach ($this->api->get("/tournees?benevole_id={$benevoleId}{$filtre}") ?? [] as $tournee) {
            $affectations[] = [
                'type' => 'Tournée',
                'libelle' => $tournee['destinataire'],
                'date' => $tournee['date_tournee'],
                'lieu' => $tournee['adresse'],
                'statut' => $tournee['statut'],
                'url' => route('tournees.show', $tournee['id']),
            ];
        }

        usort($affectations, fn ($a, $b) => Carbon::parse($a['date'])<=> Carbon::parse($b['date']));

        return $affectations;
    }
}
