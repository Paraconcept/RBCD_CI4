<?php

namespace App\Libraries;

use App\Models\ScoreboardInningModel;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\SiteSettingModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Feuille de match individuelle (PDF) : affichée depuis la fiche du match et jointe à l'e-mail
 * de la feuille récapitulative d'une rencontre d'équipe.
 */
class MatchSheetPdf
{
    public const INNINGS_PER_PAGE = 75;
    // Pied de la feuille par sets (modèle FRBB Coupe de Belgique), modifiable dans Marquoir › Paramètres
    public const SETS_RECIPIENT = 'Willemsen Stéphane - D.S.R. Liège - Luxembourg · stephane.willemsen@gmail.com · Gsm : 0499 23 22 26';
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

    /** Titres bilingues de la feuille par sets : « Coupe de Belgique / Beker van België » ou la compétition encodée. */
    private static function setsTitles(object $match): array
    {
        $competition = trim((string) ($match->competition ?? ''));
        if ($competition === '' || stripos($competition, 'Coupe de Belgique') !== false) {
            return ['titleFr' => 'Coupe de Belgique', 'titleNl' => 'Beker van België'];
        }
        return ['titleFr' => $competition, 'titleNl' => ''];
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

        // Par sets : modèle FRBB « Coupe de Belgique », une seule page A4 portrait pour tous les sets.
        // Sinon une page par tranche de 75 reprises. Une feuille qui déborde (long commentaire, signatures)
        // est recomposée de plus en plus serrée.
        $sets = ScoreboardMatchModel::isSets($match);
        if ($sets) {
            $expected = 1;
            $data += self::setsTitles($match) + [
                'matchNumber' => $match->te_code ?? '',
                'recipient'   => (string) (new SiteSettingModel())->getSetting('scoreboard_sets_sheet_recipient', self::SETS_RECIPIENT),
            ];
        } else {
            $expected = 0;
            foreach ($sheets ?: [1 => []] as $sheet) {
                $expected += max(1, (int) ceil(($sheet ? max(array_keys($sheet)) : 0) / self::INNINGS_PER_PAGE));
            }
        }
        for ($compact = 0; $compact <= self::MAX_COMPACT; $compact++) {
            $options = new Options();
            $options->set('defaultFont', $sets ? 'Times' : 'Helvetica');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view($sets ? 'admin/scoreboard/pdf_sets' : 'admin/scoreboard/pdf', $data + ['compact' => $compact]), 'UTF-8');
            $dompdf->setPaper('A4', $sets ? 'portrait' : 'landscape');
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
