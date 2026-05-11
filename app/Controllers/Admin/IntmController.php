<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\IntmTeamModel;
use App\Models\MemberModel;

class IntmController extends BaseController
{
    private const UPLOAD_PATH = 'uploads/intm_teams/';

    private function federatedMembers(): array
    {
        return (new MemberModel())
            ->where('is_active', 1)
            ->where('is_federated', 1)
            ->orderBy('last_name', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->findAll();
    }

    public function index(): string
    {
        return view('admin/intm/index', [
            'teams' => (new IntmTeamModel())->getAllWithPlayers(),
        ]);
    }

    public function create(): string
    {
        return view('admin/intm/form', [
            'team'    => null,
            'members' => $this->federatedMembers(),
            'modes'   => IntmTeamModel::GAME_MODES,
            'seasons' => $this->seasons(),
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'season'     => $this->request->getPost('season'),
            'game_mode'  => $this->request->getPost('game_mode'),
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $data['photo'] = $this->uploadPhoto($photo);
        }

        (new IntmTeamModel())->insert($data);

        return redirect()->to(base_url('admin/intm'))
                         ->with('success', 'Équipe créée avec succès.');
    }

    public function edit(int $id): string
    {
        $team = (new IntmTeamModel())->find($id);
        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/intm/form', [
            'team'    => $team,
            'members' => $this->federatedMembers(),
            'modes'   => IntmTeamModel::GAME_MODES,
            'seasons' => $this->seasons(),
        ]);
    }

    public function update(int $id)
    {
        $model = new IntmTeamModel();
        $team  = $model->find($id);
        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'       => $this->request->getPost('name'),
            'season'     => $this->request->getPost('season'),
            'game_mode'  => $this->request->getPost('game_mode'),
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id'),
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $this->deletePhotoFile($team->photo);
            $data['photo'] = $this->uploadPhoto($photo);
        }

        if ($this->request->getPost('remove_photo') && $team->photo) {
            $this->deletePhotoFile($team->photo);
            $data['photo'] = null;
        }

        $model->update($id, $data);

        return redirect()->to(base_url('admin/intm'))
                         ->with('success', 'Équipe mise à jour.');
    }

    public function delete(int $id)
    {
        $model = new IntmTeamModel();
        $team  = $model->find($id);
        if ($team) {
            $this->deletePhotoFile($team->photo);
            $model->delete($id);
        }

        return redirect()->to(base_url('admin/intm'))
                         ->with('success', 'Équipe supprimée.');
    }

    private function uploadPhoto($file): string
    {
        $name = $file->getRandomName();
        $file->move(FCPATH . self::UPLOAD_PATH, $name);
        return $name;
    }

    private function deletePhotoFile(?string $filename): void
    {
        if ($filename) {
            $path = FCPATH . self::UPLOAD_PATH . $filename;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function rules(): array
    {
        return [
            'name'       => 'required|max_length[100]',
            'season'     => 'required|max_length[9]',
            'game_mode'  => 'required|in_list[Libre PF,Libre GF,3 Bandes PF,3 Bandes GF]',
            'player1_id' => 'required|is_natural_no_zero',
            'player2_id' => 'required|is_natural_no_zero',
            'player3_id' => 'required|is_natural_no_zero',
            'photo'      => 'permit_empty|is_image[photo]|max_size[photo,3072]|mime_in[photo,image/jpeg,image/png,image/webp]',
        ];
    }

    private function seasons(): array
    {
        return [SAISON_PASSEE, SAISON_EN_COURS, SAISON_PROCHAINE];
    }
}
