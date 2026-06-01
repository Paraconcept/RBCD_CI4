<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ScheduleEventModel;

class ScheduleEventsController extends BaseController
{
    private ScheduleEventModel $model;

    public function __construct()
    {
        $this->model = new ScheduleEventModel();
    }

    public function index(): string
    {
        $events = $this->model->orderBy('event_date', 'DESC')->orderBy('start_time')->findAll();

        return view('admin/schedule_events/index', [
            'title'   => 'Événements du tableau',
            'events'  => $events,
            'colors'  => ScheduleEventModel::$colors,
        ]);
    }

    public function create(): string
    {
        return view('admin/schedule_events/form', [
            'title'  => 'Nouvel événement',
            'event'  => null,
            'colors' => ScheduleEventModel::$colors,
        ]);
    }

    public function store()
    {
        $data = $this->collectPost();
        $this->model->insert($data);

        $week = (int) (new \DateTime($data['event_date']))->format('W');
        $year = (int) (new \DateTime($data['event_date']))->format('o');

        return redirect()->to(base_url("admin/schedule/{$week}/{$year}"))
                         ->with('success', 'Événement créé.');
    }

    public function duplicate(int $id): string
    {
        $source = $this->model->find($id);
        if (!$source) {
            return redirect()->to(base_url('admin/schedule-events'));
        }

        return view('admin/schedule_events/form', [
            'title'   => 'Dupliquer — ' . $source->title,
            'event'   => null,
            'prefill' => $source,
            'colors'  => ScheduleEventModel::$colors,
        ]);
    }

    public function edit(int $id): string
    {
        $event = $this->model->find($id);

        return view('admin/schedule_events/form', [
            'title'  => 'Modifier l\'événement',
            'event'  => $event,
            'colors' => ScheduleEventModel::$colors,
        ]);
    }

    public function update(int $id)
    {
        $this->model->find($id);
        $this->model->update($id, $this->collectPost());

        return redirect()->to(base_url('admin/schedule-events'))
                         ->with('success', 'Événement mis à jour.');
    }

    public function delete(int $id)
    {
        $this->model->find($id);
        $this->model->delete($id);

        return redirect()->to(base_url('admin/schedule-events'))
                         ->with('success', 'Événement supprimé.');
    }

    private function collectPost(): array
    {
        return [
            'event_date'  => $this->request->getPost('event_date'),
            'start_time'  => $this->request->getPost('start_time') ?: null,
            'title'       => trim($this->request->getPost('title')),
            'description' => trim($this->request->getPost('description')) ?: null,
            'color'       => $this->request->getPost('color') ?: 'blue',
        ];
    }
}
