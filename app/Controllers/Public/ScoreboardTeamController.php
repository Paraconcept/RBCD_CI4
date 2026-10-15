<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Libraries\TeamRecapPdf;
use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\ScoreboardTableModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\ScoreboardTeamSignatureModel;

/**
 * Clôture d'une rencontre d'équipe depuis les Chromebooks : signatures facultatives des capitaines et
 * feuille récapitulative PDF (à imprimer). L'envoi par e-mail reste réservé au DS, depuis l'admin.
 * Uniquement le jour de la rencontre : ensuite, tout se fait depuis l'admin.
 */
class ScoreboardTeamController extends BaseController
{
    private ScoreboardTeamEncounterModel $encounters;
    private ScoreboardTeamSignatureModel $signatures;

    public function __construct()
    {
        $this->encounters = new ScoreboardTeamEncounterModel();
        $this->signatures = new ScoreboardTeamSignatureModel();
    }

    public function show(int $id)
    {
        $encounter = $this->findToday($id);
        if (!$encounter) {
            return redirect()->to($this->back())->with('error', "Cette rencontre n'est plus clôturable au marquoir : voyez avec le directeur sportif.");
        }

        $state    = $this->encounters->closingState($encounter);
        $signedAt = $this->signatures->signedAt($id);
        $tables   = [];
        foreach ((new ScoreboardTableModel())->findAll() as $t) {
            $tables[(int) $t->id] = $t->name;
        }

        return view('public/scoreboard/team', [
            'title'     => 'Rencontre ' . $encounter->code,
            'encounter' => $encounter,
            'state'     => $state,
            'tables'    => $tables,
            'signedAt'  => $signedAt,
            'pdfReady'  => $this->pdfReady($encounter, $state['ready'], $signedAt !== null),
            'back'      => $this->back(),
            'billard'   => $this->billardParam(),
        ]);
    }

    public function comment(int $id)
    {
        $encounter = $this->findToday($id);
        if ($encounter && !$this->signatures->isSigned($id)) {
            $comment = mb_substr(trim((string) $this->request->getPost('recap_comment')), 0, 1000);
            $this->encounters->update($id, ['recap_comment' => $comment !== '' ? $comment : null]);
        }

        return redirect()->to($this->showUrl($id))->with('success', 'Commentaire enregistré.');
    }

    public function sign(int $id)
    {
        $encounter = $this->findToday($id);
        if (!$encounter || !$encounter->recap_signature_required || $this->signatures->isSigned($id)
            || !$this->encounters->closingState($encounter)['ready']) {
            return redirect()->to($this->showUrl($id));
        }

        $data = [
            'encounter_id'   => $id,
            'home_signature' => (string) $this->request->getPost('home_signature'),
            'away_signature' => (string) $this->request->getPost('away_signature'),
            'signed_at'      => ScoreboardMatchModel::now(),
        ];
        // Pas de withInput() : les images ne doivent pas transiter par la session (le formulaire les vérifie déjà)
        if (!ScoreboardSignatureModel::isValidImage($data['home_signature']) || !ScoreboardSignatureModel::isValidImage($data['away_signature'])) {
            return redirect()->to($this->showUrl($id))->with('error', 'Les deux signatures sont nécessaires.');
        }

        $this->signatures->insert($data);

        return redirect()->to($this->showUrl($id))->with('success', 'Feuille récapitulative signée.');
    }

    public function pdf(int $id)
    {
        $encounter = $this->findToday($id);
        if (!$encounter) {
            return redirect()->to($this->back());
        }
        $ready = $this->encounters->closingState($encounter)['ready'];
        if (!$this->pdfReady($encounter, $ready, $this->signatures->isSigned($id))) {
            return redirect()->to($this->showUrl($id));
        }
        [$pdf, $filename] = TeamRecapPdf::render($encounter);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($pdf);
    }

    private function pdfReady(object $encounter, bool $ready, bool $signed): bool
    {
        return $ready && (!$encounter->recap_signature_required || $signed);
    }

    private function findToday(int $id): ?object
    {
        $encounter = $this->encounters->find($id);
        if (!$encounter || !$encounter->scheduled_at
            || date('Y-m-d', strtotime($encounter->scheduled_at)) !== ScoreboardMatchModel::now('Y-m-d')) {
            return null;
        }
        return $encounter;
    }

    /** Billard d'où l'on vient (?billard=n), pour y revenir. */
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
        return base_url('marquoir/rencontre/' . $id) . ($n ? '?billard=' . $n : '');
    }
}
