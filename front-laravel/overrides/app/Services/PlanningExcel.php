<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Génère le planning d'un bénévole au format Excel (.xlsx), tel que demandé par
 * le cahier des charges : « des plannings sont créés, édités et envoyés aux
 * différents bénévoles sous la forme de fichiers Excel ».
 */
class PlanningExcel
{
    public function __construct(protected AffectationsBenevole $affectations)
    {
    }

    /** Affectations d'un bénévole comprises dans les $jours prochains jours. */
    public function affectationsPeriode(int $benevoleId, int $jours): array
    {
        $debut = Carbon::now()->startOfDay();
        $fin = Carbon::now()->addDays($jours)->endOfDay();

        return array_values(array_filter(
            $this->affectations->pour($benevoleId, aVenir: true),
            function (array $a) use ($debut, $fin) {
                $date = Carbon::parse($a['date']);

                return $date->betweenIncluded($debut, $fin);
            }
        ));
    }

    /** Construit le fichier .xlsx et retourne son contenu binaire. */
    public function construire(array $benevole, array $affectations, int $jours): string
    {
        $classeur = new Spreadsheet();
        $feuille = $classeur->getActiveSheet();
        $feuille->setTitle('Planning');

        $feuille->setCellValue('A1', 'NO MORE WASTE — Planning bénévole');
        $feuille->mergeCells('A1:E1');
        $feuille->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $periode = Carbon::now()->format('d/m/Y') . ' au ' . Carbon::now()->addDays($jours)->format('d/m/Y');
        $feuille->setCellValue('A2', $benevole['nom'] . '  —  du ' . $periode);
        $feuille->mergeCells('A2:E2');
        $feuille->getStyle('A2')->getFont()->setItalic(true);

        if (! empty($benevole['capacites'])) {
            $feuille->setCellValue('A3', 'Capacités : ' . $benevole['capacites']);
            $feuille->mergeCells('A3:E3');
        }

        $entetes = ['Date', 'Heure', 'Type', 'Mission', 'Lieu'];
        $feuille->fromArray($entetes, null, 'A5');
        $feuille->getStyle('A5:E5')->getFont()->setBold(true);
        $feuille->getStyle('A5:E5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('198754');
        $feuille->getStyle('A5:E5')->getFont()->getColor()->setRGB('FFFFFF');

        $ligne = 6;
        foreach ($affectations as $a) {
            $date = Carbon::parse($a['date']);
            $feuille->setCellValue("A{$ligne}", $date->format('d/m/Y'));
            $feuille->setCellValue("B{$ligne}", $date->format('H:i'));
            $feuille->setCellValue("C{$ligne}", $a['type']);
            $feuille->setCellValue("D{$ligne}", $a['libelle']);
            $feuille->setCellValue("E{$ligne}", $a['lieu']);
            $ligne++;
        }

        if ($affectations === []) {
            $feuille->setCellValue('A6', 'Aucune mission sur la période.');
            $feuille->mergeCells('A6:E6');
        } else {
            $feuille->getStyle("A5:E" . ($ligne - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'E') as $colonne) {
            $feuille->getColumnDimension($colonne)->setAutoSize(true);
        }
        $feuille->getStyle('A5:C' . max(6, $ligne - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $fichier = tempnam(sys_get_temp_dir(), 'planning') . '.xlsx';
        (new Xlsx($classeur))->save($fichier);
        $contenu = file_get_contents($fichier);
        @unlink($fichier);

        return $contenu;
    }

    public function nomFichier(array $benevole): string
    {
        $nom = preg_replace('/[^A-Za-z0-9]+/', '-', $benevole['nom']);

        return 'planning-' . strtolower(trim($nom, '-')) . '-' . Carbon::now()->format('Y-m-d') . '.xlsx';
    }
}
