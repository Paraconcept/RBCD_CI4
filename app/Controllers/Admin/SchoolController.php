<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SchoolSettingModel;
use App\Models\MemberModel;

class SchoolController extends BaseController
{
    public function index(): string
    {
        $model    = new SchoolSettingModel();
        $settings = $model->first();

        $members = (new MemberModel())
            ->where('is_active', 1)
            ->orderBy('last_name', 'ASC')
            ->orderBy('first_name', 'ASC')
            ->findAll();

        return view('admin/school/index', [
            'title'       => 'École de Billard',
            'breadcrumbs' => [['title' => 'École de Billard']],
            'settings'    => $settings,
            'members'     => $members,
        ]);
    }

    public function save()
    {
        $model    = new SchoolSettingModel();
        $settings = $model->first();

        $data = [
            'teacher_member_id'   => $this->request->getPost('teacher_member_id') ?: null,
            'contact_member_id'   => $this->request->getPost('contact_member_id') ?: null,
            'schedule'            => $this->request->getPost('schedule'),
            'frequency_per_month' => (int) $this->request->getPost('frequency_per_month'),
        ];

        if ($settings) {
            $model->update($settings->id, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to(base_url('admin/school'))
                         ->with('success', 'Paramètres de l\'école mis à jour.');
    }
}
