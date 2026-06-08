<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteSettingModel;

class SettingsController extends BaseController
{
    public function index(): string
    {
        $model = new SiteSettingModel();

        $db             = \Config\Database::connect();
        $committeeMembers = $db->table('members m')
            ->select('m.id, m.first_name, m.last_name')
            ->join('admin_user_roles aur', 'aur.member_id = m.id')
            ->where('m.is_active', 1)
            ->groupBy('m.id')
            ->orderBy('m.last_name', 'ASC')
            ->get()->getResultObject();

        return view('admin/settings/index', [
            'news_per_page'           => (int) $model->getSetting('news_per_page', 5),
            'journal_editor_member_id' => (int) $model->getSetting('journal_editor_member_id', 0),
            'committeeMembers'         => $committeeMembers,
        ]);
    }

    public function save()
    {
        $model = new SiteSettingModel();

        $newsPerPage = (int) $this->request->getPost('news_per_page');
        if ($newsPerPage < 1)  $newsPerPage = 1;
        if ($newsPerPage > 50) $newsPerPage = 50;
        $model->setSetting('news_per_page', $newsPerPage);

        $editorId = (int) $this->request->getPost('journal_editor_member_id');
        $model->setSetting('journal_editor_member_id', $editorId > 0 ? $editorId : '');

        return redirect()->to(base_url('admin/settings'))
                         ->with('success', 'Paramètres enregistrés.');
    }
}
