<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\IntmTeamModel;
use App\Models\MemberModel;

class IntmController extends BaseController
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
        return view('admin/intm/index', [
            'teams' => (new IntmTeamModel())->getAllWithPlayers(),
        ]);
    }

    public function create(): string
    {
        return view('admin/intm/form', [
            'team'      => null,
            'members'   => $this->federatedMembers(),
            'seasons'   => $this->seasons(),
            'divisions' => IntmTeamModel::DIVISIONS,
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new IntmTeamModel())->insert([
            'name'       => $this->request->getPost('name'),
            'season'     => $this->request->getPost('season'),
            'division'   => $this->request->getPost('division') ?: null,
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id'),
            'player4_id' => $this->request->getPost('player4_id'),
        ]);

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
            'team'      => $team,
            'members'   => $this->federatedMembers(),
            'seasons'   => $this->seasons(),
            'divisions' => IntmTeamModel::DIVISIONS,
        ]);
    }

    public function update(int $id)
    {
        $model = new IntmTeamModel();
        if (!$model->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, [
            'name'       => $this->request->getPost('name'),
            'season'     => $this->request->getPost('season'),
            'division'   => $this->request->getPost('division') ?: null,
            'player1_id' => $this->request->getPost('player1_id'),
            'player2_id' => $this->request->getPost('player2_id'),
            'player3_id' => $this->request->getPost('player3_id'),
            'player4_id' => $this->request->getPost('player4_id'),
        ]);

        return redirect()->to(base_url('admin/intm'))
                         ->with('success', 'Équipe mise à jour.');
    }

    public function delete(int $id)
    {
        (new IntmTeamModel())->delete($id);

        return redirect()->to(base_url('admin/intm'))
                         ->with('success', 'Équipe supprimée.');
    }

    private function rules(): array
    {
        return [
            'name'       => 'required|max_length[100]',
            'season'     => 'required|max_length[9]',
            'division'   => 'permit_empty|in_list[1,2A,2B,3A,3B,3C,4A,4B,4C]',
            'player1_id' => 'required|is_natural_no_zero',
            'player2_id' => 'required|is_natural_no_zero',
            'player3_id' => 'required|is_natural_no_zero',
            'player4_id' => 'required|is_natural_no_zero',
        ];
    }

    private function seasons(): array
    {
        return [SAISON_PASSEE, SAISON_EN_COURS, SAISON_PROCHAINE];
    }
}
