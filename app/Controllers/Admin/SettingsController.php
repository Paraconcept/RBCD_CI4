<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiteSettingModel;

class SettingsController extends BaseController
{
    public function index(): string
    {
        $model = new SiteSettingModel();
        return view('admin/settings/index', [
            'news_per_page' => (int) $model->get('news_per_page', 5),
        ]);
    }

    public function save()
    {
        $model = new SiteSettingModel();

        $newsPerPage = (int) $this->request->getPost('news_per_page');
        if ($newsPerPage < 1)  $newsPerPage = 1;
        if ($newsPerPage > 50) $newsPerPage = 50;

        $model->set('news_per_page', $newsPerPage);

        return redirect()->to(base_url('admin/settings'))
                         ->with('success', 'Paramètres enregistrés.');
    }
}
