<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\SkittlesPoolMailer;
use App\Libraries\SkittlesPoolPdf;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\ScoreboardSkittlesPoolModel;
use App\Models\ScoreboardSkittlesSignatureModel;

/**
 * Clôture d'une Soirée Poule 5 Quilles sur le Chromebook : récapitulatif, commentaire, signatures des joueurs A, B, C
 * (sur la feuille récapitulative uniquement) et PDF à imprimer. Uniquement le jour de la poule.
 */
class ScoreboardSkittlesController extends BaseController
{
    private ScoreboardSkittlesPoolModel $pools;
    private ScoreboardSkittlesSignatureModel $signatures;

    public function __construct()
    {
        $this->pools      = new ScoreboardSkittlesPoolModel();
        $this->signatures = new ScoreboardSkittlesSignatureModel();
    }

    public function show(int $id)
    {
        $pool = $this->findToday($id);
        if (!$pool) {
            return redirect()->to($this->back())->with('error', "Cette poule n'est plus clôturable au marquoir : voyez avec le directeur sportif.");
        }
        $sheet    = $this->pools->sheet($pool);
        $signedAt = $this->signatures->signedAt($id);

        return view('public/scoreboard/skittles_pool', [
            'title'    => ScoreboardSkittlesPoolModel::NAME,
            'pool'     => $pool,
            'sheet'    => $sheet,
            'signedAt' => $signedAt,
            'pdfReady' => $this->pdfReady($pool, $sheet['complete'], $signedAt !== null),
            'mail'     => SkittlesPoolMailer::progress($pool),
            'back'     => $this->back(),
            'billard'  => $this->billardParam(),
        ]);
    }

    public function comment(int $id)
    {
        $pool = $this->findToday($id);
        if ($pool && !$this->signatures->isSigned($id)) {
            $comment = mb_substr(trim((string) $this->request->getPost('comment')), 0, 1000);
            $this->pools->update($id, ['comment' => $comment !== '' ? $comment : null]);
        }

        return redirect()->to($this->showUrl($id))->with('success', 'Commentaire enregistré.');
    }

    public function sign(int $id)
    {
        $pool = $this->findToday($id);
        if (!$pool || !$pool->signature_required || $this->signatures->isSigned($id) || !$this->pools->sheet($pool)['complete']) {
            return redirect()->to($this->showUrl($id));
        }

        $data = ['pool_id' => $id, 'signed_at' => ScoreboardMatchModel::now()];
        foreach (['a', 'b', 'c'] as $k) {
            $data[$k . '_signature'] = (string) $this->request->getPost($k . '_signature');
            // Pas de withInput() : les images ne doivent pas transiter par la session (le formulaire les vérifie déjà)
            if (!ScoreboardSignatureModel::isValidImage($data[$k . '_signature'])) {
                return redirect()->to($this->showUrl($id))->with('error', 'Les signatures des trois joueurs sont nécessaires.');
            }
        }

        $this->signatures->insert($data);
        SkittlesPoolMailer::deliver($this->pools->find($id));
        $sent = $this->pools->find($id);

        return redirect()->to($this->showUrl($id))->with('success', 'Feuille récapitulative signée'
            . ($sent->sent_at ? ' et envoyée par e-mail.' : ($sent->send_error ? ', mais son envoi par e-mail a échoué : prévenez le directeur sportif.' : '.')));
    }

    public function pdf(int $id)
    {
        $pool = $this->findToday($id);
        if (!$pool) {
            return redirect()->to($this->back());
        }
        if (!$this->pdfReady($pool, $this->pools->sheet($pool)['complete'], $this->signatures->isSigned($id))) {
            return redirect()->to($this->showUrl($id));
        }
        [$pdf, $filename] = SkittlesPoolPdf::render($pool);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    private function pdfReady(object $pool, bool $complete, bool $signed): bool
    {
        return $complete && (!$pool->signature_required || $signed);
    }

    private function findToday(int $id): ?object
    {
        $pool = $this->pools->find($id);
        if (!$pool || !$pool->scheduled_at
            || date('Y-m-d', strtotime($pool->scheduled_at)) !== ScoreboardMatchModel::now('Y-m-d')) {
            return null;
        }
        return $pool;
    }

    private function billardParam(): ?int
    {
        $n = (int) $this->request->getGet('billard');
        return $n > 0 ? $n : null;
    }

    private function back(): string
    {
        $n = $this->billardParam();
        return base_url($n ? 'marquoir/billard/' . $n : 'marquoir');
    }

    private function showUrl(int $id): string
    {
        $n = $this->billardParam();
        return base_url('marquoir/poule-5-quilles/' . $id) . ($n ? '?billard=' . $n : '');
    }
}
