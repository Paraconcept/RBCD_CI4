<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OpeningHourModel;

class OpeningHoursController extends BaseController
{
    private OpeningHourModel $model;

    public function __construct()
    {
        $this->model = new OpeningHourModel();
    }

    public function index(): string
    {
        return view('admin/opening_hours/index', [
            'title'       => 'Heures d\'ouverture',
            'breadcrumbs' => [
                ['title' => 'Gestion'],
                ['title' => 'Heures d\'ouverture'],
            ],
            'hours' => $this->model->getAllOrdered(),
        ]);
    }

    public function save()
    {
        $posted = $this->request->getPost('hours') ?? [];

        foreach ($posted as $dayOrder => $data) {
            $isClosed = !empty($data['is_closed']) ? 1 : 0;

            $row = [
                'is_closed'       => $isClosed,
                'morning_open'    => $isClosed ? null : ($data['morning_open']    ?: null),
                'morning_close'   => $isClosed ? null : ($data['morning_close']   ?: null),
                'afternoon_open'  => $isClosed ? null : ($data['afternoon_open']  ?: null),
                'afternoon_close' => $isClosed ? null : ($data['afternoon_close'] ?: null),
                'evening_open'    => $isClosed ? null : ($data['evening_open']    ?: null),
                'evening_close'   => $isClosed ? null : ($data['evening_close']   ?: null),
            ];

            $this->model->where('day_order', (int) $dayOrder)->set($row)->update();
        }

        return redirect()->to(base_url('admin/opening-hours'))->with('success', 'Heures d\'ouverture enregistrées.');
    }
}
