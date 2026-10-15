<?php

namespace App\Libraries;

use App\Models\ScoreboardTeamEncounterModel;
use App\Models\ScoreboardTeamSignatureModel;
use App\Models\SiteSettingModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Feuille récapitulative (PDF) d'une rencontre d'équipe : admin (affichage, e-mail) et Chromebooks du marquoir.
 */
class TeamRecapPdf
{
    /** Responsable CDR, destinataire de la feuille. */
    public static function cdr(): object
    {
        $s = new SiteSettingModel();

        return (object) [
            'name'  => (string) $s->getSetting('scoreboard_cdr_name', 'Christian JEUNEAU'),
            'email' => (string) $s->getSetting('scoreboard_cdr_email', 'cdregions@gmail.com'),
        ];
    }

    /** @return array{0: string, 1: string} PDF binaire et nom de fichier */
    public static function render(object $encounter): array
    {
        $model  = new ScoreboardTeamEncounterModel();
        $result = ScoreboardTeamEncounterModel::computeResult($encounter, $model->getMatches((int) $encounter->id));

        $logo = static function (string $file): ?string {
            $path = FCPATH . 'assets/images/' . $file;
            return is_file($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : null;
        };

        $html = view('admin/scoreboard/teams/pdf', [
            'encounter' => $encounter,
            'result'    => $result,
            'logoFrbb'  => $logo('frbb_kbbb_logo_100.png'),
            'logoLL'    => $logo('Ecusson_FRBB-LL.png'),
            'cdr'       => self::cdr(),
            'signature' => (new ScoreboardTeamSignatureModel())->forEncounter((int) $encounter->id),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Times');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return [
            $dompdf->output(),
            'recapitulatif_' . url_title($encounter->competition . '-' . $encounter->code, '-', true) . '.pdf',
        ];
    }
}
