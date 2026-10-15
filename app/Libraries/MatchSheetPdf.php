<?php

namespace App\Libraries;

use App\Models\ScoreboardInningModel;
use App\Models\ScoreboardSignatureModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Feuille de match individuelle (PDF) : affichée depuis la fiche du match et jointe à l'e-mail
 * de la feuille récapitulative d'une rencontre d'équipe.
 */
class MatchSheetPdf
{
    public const INNINGS_PER_PAGE = 75;
    private const MAX_COMPACT = 3;

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
        $logo   = FCPATH . 'assets/images/Ecusson_RBCD.png';
        $sheets = self::sheets((int) $match->id);
        $data   = [
            'match'  => $match,
            'sheets' => $sheets,
            'logo'   => is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null,
            'signature' => (new ScoreboardSignatureModel())->forMatch((int) $match->id),
        ];

        // Une page par set et par tranche de 75 reprises : au-delà, une feuille déborde (long commentaire,
        // signatures) et on la recompose de plus en plus serrée
        $expected = 0;
        foreach ($sheets ?: [1 => []] as $sheet) {
            $expected += max(1, (int) ceil(($sheet ? max(array_keys($sheet)) : 0) / self::INNINGS_PER_PAGE));
        }
        for ($compact = 0; $compact <= self::MAX_COMPACT; $compact++) {
            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('admin/scoreboard/pdf', $data + ['compact' => $compact]), 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            if ($dompdf->getCanvas()->get_page_count() <= $expected) {
                break;
            }
        }

        $date = date('Y-m-d', strtotime($match->started_at ?? $match->scheduled_at ?? 'now'));

        return [
            $dompdf->output(),
            'feuille_match_' . $date . '_' . url_title($match->p1_last_name . '-' . $match->p2_last_name, '-', true) . '.pdf',
        ];
    }
}
