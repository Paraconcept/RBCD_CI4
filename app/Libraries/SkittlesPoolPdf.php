<?php

namespace App\Libraries;

use App\Models\ScoreboardSkittlesPoolModel;
use App\Models\ScoreboardSkittlesSignatureModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Feuille récapitulative d'une Soirée Poule 5 Quilles, au modèle FRBB Liège-Luxembourg (A4 portrait, une page) :
 * admin, e-mail et Chromebooks du marquoir.
 */
class SkittlesPoolPdf
{
    private const MAX_COMPACT = 2;

    /** @return array{0: string, 1: string} PDF binaire et nom de fichier */
    public static function render(object $pool): array
    {
        $image = static function (string $file, string $mime = 'image/png'): ?string {
            $path = FCPATH . 'assets/images/' . $file;
            return is_file($path) ? 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path)) : null;
        };

        $data = [
            'pool'      => $pool,
            'sheet'     => (new ScoreboardSkittlesPoolModel())->sheet($pool),
            'logo'      => $image('Ecusson_FRBB-LL.png'),
            'photo'     => $image('5quilles.png'),
            'regional'  => ScoreboardSkittlesPoolModel::regional(),
            'signature' => (new ScoreboardSkittlesSignatureModel())->forPool((int) $pool->id),
        ];

        // Toujours une page : recomposée plus serrée si un long commentaire la fait déborder
        for ($compact = 0; $compact <= self::MAX_COMPACT; $compact++) {
            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('admin/scoreboard/skittles/pdf', $data + ['compact' => $compact]), 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            if ($dompdf->getCanvas()->get_page_count() <= 1) {
                break;
            }
        }

        $date = $pool->scheduled_at ? date('Y-m-d', strtotime($pool->scheduled_at)) . '_' : '';

        return [$dompdf->output(), 'poule-5-quilles_' . $date . url_title($pool->name, '-', true) . '.pdf'];
    }
}
