<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SportResultModel;
use App\Models\MemberModel;

class SportResultsController extends BaseController
{
    private SportResultModel $model;

    public function __construct()
    {
        $this->model = new SportResultModel();
    }

    public function index(): string
    {
        $grouped = $this->model->getGroupedBySeasonWithWinner();

        return view('admin/sport_results/index', [
            'title'   => 'Résultats sportifs',
            'grouped' => $grouped,
        ]);
    }

    public function create(): string
    {
        return view('admin/sport_results/form', [
            'title'   => 'Nouveau résultat',
            'result'  => null,
            'members' => $this->getActiveMembers(),
        ]);
    }

    public function store()
    {
        $data = $this->buildData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pdfFile = $this->handlePdf();
        if ($pdfFile !== false) {
            $data['pdf_file'] = $pdfFile;
        }

        // Photo : upload manuel prioritaire, sinon snapshot du membre
        $photo = $this->handleWinnerPhoto();
        if ($photo !== false) {
            $data['winner_photo'] = $photo;
        } elseif ($data['winner_member_id']) {
            $snap = $this->snapshotMemberPhoto($data['winner_member_id']);
            if ($snap !== null) {
                $data['winner_photo'] = $snap;
            }
        }

        $this->model->insert($data);

        return redirect()->to(base_url('admin/sport-results'))
                         ->with('success', 'Résultat ajouté.');
    }

    public function edit(int $id): string
    {
        $result = $this->model->find($id);
        if (!$result) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/sport_results/form', [
            'title'   => 'Modifier le résultat',
            'result'  => $result,
            'members' => $this->getActiveMembers(),
        ]);
    }

    public function update(int $id)
    {
        $result = $this->model->find($id);
        if (!$result) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = $this->buildData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // PDF
        $pdfFile = $this->handlePdf();
        if ($pdfFile !== false) {
            if ($result->pdf_file && $pdfFile !== null) {
                $old = FCPATH . 'uploads/PDF/SportResults/' . $result->pdf_file;
                if (file_exists($old)) unlink($old);
            }
            $data['pdf_file'] = $pdfFile;
        }
        if ($this->request->getPost('remove_pdf') && $result->pdf_file) {
            $old = FCPATH . 'uploads/PDF/SportResults/' . $result->pdf_file;
            if (file_exists($old)) unlink($old);
            $data['pdf_file'] = null;
        }

        // Photo vainqueur
        $photo = $this->handleWinnerPhoto();
        if ($photo !== false) {
            // Upload manuel : remplace l'existant
            $this->deleteWinnerPhoto($result->winner_photo);
            $data['winner_photo'] = $photo;
        } elseif ($this->request->getPost('remove_winner_photo') && $result->winner_photo) {
            // Suppression explicite
            $this->deleteWinnerPhoto($result->winner_photo);
            $data['winner_photo'] = null;
        } elseif ($data['winner_member_id'] && $data['winner_member_id'] !== $result->winner_member_id) {
            // Membre changé → nouveau snapshot (supprime l'ancienne photo)
            $this->deleteWinnerPhoto($result->winner_photo);
            $snap = $this->snapshotMemberPhoto($data['winner_member_id']);
            $data['winner_photo'] = $snap;
        } elseif ($data['winner_member_id'] && !$result->winner_photo) {
            // Même membre mais pas encore de photo → snapshot
            $snap = $this->snapshotMemberPhoto($data['winner_member_id']);
            $data['winner_photo'] = $snap;
        }

        $this->model->update($id, $data);

        return redirect()->to(base_url('admin/sport-results'))
                         ->with('success', 'Résultat mis à jour.');
    }

    public function delete(int $id)
    {
        $result = $this->model->find($id);
        if ($result) {
            foreach ([
                FCPATH . 'uploads/PDF/SportResults/' . $result->pdf_file,
                FCPATH . 'uploads/sport_results/'    . $result->winner_photo,
            ] as $path) {
                if ($path && file_exists($path)) unlink($path);
            }
        }
        $this->model->delete($id);

        return redirect()->to(base_url('admin/sport-results'))
                         ->with('success', 'Résultat supprimé.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function buildData(): ?array
    {
        $rules = [
            'season' => 'required|min_length[9]|max_length[9]',
            'type'   => 'required|in_list[coupe,championnat,autre]',
            'title'  => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return null;
        }

        $memberId   = (int) $this->request->getPost('winner_member_id') ?: null;
        $winnerName = null;

        if ($memberId) {
            // Snapshot du nom au moment de l'encodage
            $member = (new MemberModel())->find($memberId);
            if ($member) {
                $winnerName = mb_strtoupper($member->last_name) . ' ' . $member->first_name;
            }
        } else {
            $winnerName = $this->request->getPost('winner_name') ?: null;
        }

        return [
            'season'           => $this->request->getPost('season'),
            'type'             => $this->request->getPost('type'),
            'title'            => $this->request->getPost('title'),
            'winner_member_id' => $memberId,
            'winner_name'      => $winnerName,
            'final_date'       => $this->request->getPost('final_date') ?: null,
        ];
    }

    private function handlePdf(): string|null|false
    {
        $file = $this->request->getFile('pdf_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return false;
        }
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/PDF/SportResults/', $newName);
        return $newName;
    }

    private function handleWinnerPhoto(): string|null|false
    {
        $file = $this->request->getFile('winner_photo');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return false;
        }
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/sport_results/', $newName);
        return $newName;
    }

    private function snapshotMemberPhoto(int $memberId): ?string
    {
        $member = (new MemberModel())->find($memberId);
        if (!$member || !$member->photo) {
            return null;
        }
        $src = FCPATH . 'uploads/members/' . $member->photo;
        if (!file_exists($src)) {
            return null;
        }
        $ext     = pathinfo($member->photo, PATHINFO_EXTENSION);
        $newName = 'snap_' . $memberId . '_' . time() . '.' . $ext;
        copy($src, FCPATH . 'uploads/sport_results/' . $newName);
        return $newName;
    }

    private function deleteWinnerPhoto(?string $filename): void
    {
        if (!$filename) return;
        $path = FCPATH . 'uploads/sport_results/' . $filename;
        if (file_exists($path)) unlink($path);
    }

    private function getActiveMembers(): array
    {
        return (new MemberModel())
            ->where('is_active', 1)
            ->orderBy('last_name', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->findAll();
    }
}
