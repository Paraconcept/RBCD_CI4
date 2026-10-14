<?php

namespace App\Libraries;

use App\Models\ScoreboardInningModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Feuille de match individuelle (PDF) : affichée depuis la fiche du match et jointe à l'e-mail
 * de la feuille récapitulative d'une rencontre d'équipe.
 */
class MatchSheetPdf
{
    /**
     * Reprises par set puis par numéro : [set => [n° => [joueur => ['points' => série, 'total' => cumul du set]]]].
     * Une partie classique n'a qu'un set (1).
     */
    public static function sheets(int $matchId): array
    {
        $sheets = [];
        $cumul  = [];
        foreach ((new ScoreboardInningModel())->getForMatch($matchId) as $row) {
            $set = (int) $row->set_no;
            $p   = (int) $row->player;
            $cumul[$set][$p] = ($cumul[$set][$p] ?? 0) + (int) $row->points;
            $sheets[$set][(int) $row->inning_no][$p] = ['points' => (int) $row->points, 'total' => $cumul[$set][$p]];
        }
        return $sheets;
    }

    /** @return array{0: string, 1: string} PDF binaire et nom de fichier ($match chargé avec withTable()) */
    public static function render(object $match): array
    {
        $logo = FCPATH . 'assets/images/Ecusson_RBCD.png';
        $html = view('admin/scoreboard/pdf', [
            'match'  => $match,
            'sheets' => self::sheets((int) $match->id),
            'logo'   => is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $date = date('Y-m-d', strtotime($match->started_at ?? $match->scheduled_at ?? 'now'));

        return [
            $dompdf->output(),
            'feuille_match_' . $date . '_' . url_title($match->p1_last_name . '-' . $match->p2_last_name, '-', true) . '.pdf',
        ];
    }
}
