<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleEventModel extends Model
{
    protected $table      = 'schedule_events';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'event_date', 'start_time', 'title', 'description', 'color',
    ];

    public static array $colors = [
        'blue'   => ['label' => 'Info (bleu)',      'bg' => '#e8f4fd', 'border' => '#2980b9', 'text' => '#1a5276'],
        'green'  => ['label' => 'Positif (vert)',   'bg' => '#e8f5e9', 'border' => '#2e7d32', 'text' => '#1b5e20'],
        'orange' => ['label' => 'Attention (orange)','bg' => '#fff3cd', 'border' => '#e67e22', 'text' => '#7d4e00'],
        'red'    => ['label' => 'Urgent (rouge)',   'bg' => '#fdecea', 'border' => '#c0392b', 'text' => '#7b241c'],
    ];

    public function getForDates(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }
        $rows = $this->whereIn('event_date', $dates)
                     ->orderBy('event_date')->orderBy('start_time')
                     ->findAll();
        $byDate = [];
        foreach ($rows as $row) {
            $byDate[$row->event_date][] = $row;
        }
        return $byDate;
    }
}
