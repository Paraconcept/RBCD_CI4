<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MemberKeyModel;
use App\Models\MemberModel;

class ClubKeysController extends BaseController
{
    private MemberKeyModel $model;

    public function __construct()
    {
        $this->model = new MemberKeyModel();
    }

    public function index(): string
    {
        $activeMembers = (new MemberModel())
            ->where('is_active', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->findAll();

        return view('admin/club_keys/index', [
            'title'       => 'Clés du club',
            'breadcrumbs' => [
                ['title' => 'Gestion'],
                ['title' => 'Clés du club'],
            ],
            'keys'          => $this->model->getAllWithHolder(),
            'activeMembers' => $activeMembers,
        ]);
    }

    public function store()
    {
        $badge = trim($this->request->getPost('badge_number') ?? '');
        $notes = trim($this->request->getPost('notes') ?? '');

        $this->model->insert([
            'member_id'    => null,
            'badge_number' => $badge ?: null,
            'notes'        => $notes ?: null,
        ]);

        return redirect()->to(base_url('admin/club-keys'))->with('success', 'Clé ajoutée au stock.');
    }

    public function assign(int $id)
    {
        $key = $this->model->find($id);
        if (!$key) {
            return redirect()->to(base_url('admin/club-keys'))->with('error', 'Clé introuvable.');
        }

        $memberId  = (int) $this->request->getPost('member_id');
        $givenDate = $this->request->getPost('given_date') ?: date('Y-m-d');

        $this->model->update($id, [
            'member_id'     => $memberId,
            'given_date'    => $givenDate,
            'returned_date' => null,
        ]);

        return redirect()->to(base_url('admin/club-keys'))->with('success', 'Clé attribuée.');
    }

    public function returnKey(int $id)
    {
        $key = $this->model->find($id);
        if (!$key) {
            return redirect()->to(base_url('admin/club-keys'))->with('error', 'Clé introuvable.');
        }

        $this->model->update($id, [
            'member_id'     => null,
            'returned_date' => date('Y-m-d'),
        ]);

        return redirect()->to(base_url('admin/club-keys'))->with('success', 'Clé marquée comme retournée.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id);
        return redirect()->to(base_url('admin/club-keys'))->with('success', 'Clé supprimée.');
    }
}
