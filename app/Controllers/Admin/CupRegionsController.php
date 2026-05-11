<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CupTeamModel;
use App\Models\MemberModel;

class CupRegionsController extends BaseController
{
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
        return view('admin/cup_regions/index', [
            'teams' => (new CupTeamModel())->getAllWithPlayers(),
        ]);
    }

    public function create(): string
    {
        return view('admin/cup_regions/form', [
            'team'    => null,
            'members' => $this->federatedMembers(),
            'modes'   => CupTeamModel::GAME_MODES,
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new CupTeamModel())->insert([
            'name'       => $this->request->getPost('name'),
            'game_mode'  => $this->request->getPost('game_mode'),
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id') ?: null,
        ]);

        return redirect()->to(base_url('admin/cup-regions'))
                         ->with('success', 'Équipe créée avec succès.');
    }

    public function edit(int $id): string
    {
        $team = (new CupTeamModel())->find($id);
        if (!$team) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/cup_regions/form', [
            'team'    => $team,
            'members' => $this->federatedMembers(),
            'modes'   => CupTeamModel::GAME_MODES,
        ]);
    }

    public function update(int $id)
    {
        $model = new CupTeamModel();
        if (!$model->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, [
            'name'       => $this->request->getPost('name'),
            'game_mode'  => $this->request->getPost('game_mode'),
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id') ?: null,
        ]);

        return redirect()->to(base_url('admin/cup-regions'))
                         ->with('success', 'Équipe mise à jour.');
    }

    public function delete(int $id)
    {
        (new CupTeamModel())->delete($id);

        return redirect()->to(base_url('admin/cup-regions'))
                         ->with('success', 'Équipe supprimée.');
    }

    private function rules(): array
    {
        return [
            'name'       => 'required|max_length[100]',
            'game_mode'  => 'required|in_list[Libre,3 Bandes PF,3 Bandes GF]',
            'player1_id' => 'required|is_natural_no_zero',
            'player2_id' => 'required|is_natural_no_zero',
        ];
    }
}
