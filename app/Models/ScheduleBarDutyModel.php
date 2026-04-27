<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleBarDutyModel extends Model
{
    protected $table         = 'schedule_bar_duties';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $allowedFields = [
        'duty_date', 'period', 'admin_user_id',
    ];

    public function getForDates(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        $rows = $this->db->table('schedule_bar_duties bd')
            ->select('bd.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = bd.admin_user_id')
            ->whereIn('bd.duty_date', $dates)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->duty_date][$row->period] = $row;
        }
        return $indexed;
    }

    public function getForDate(string $date): array
    {
        $rows = $this->db->table('schedule_bar_duties bd')
            ->select('bd.*, au.last_name, au.first_name')
            ->join('admin_users au', 'au.id = bd.admin_user_id')
            ->where('bd.duty_date', $date)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->period] = $row;
        }
        return $indexed;
    }

    public function isSlotTaken(string $date, string $period): bool
    {
        return $this->where('duty_date', $date)->where('period', $period)->countAllResults() > 0;
    }

    public function findSlot(string $date, string $period): ?object
    {
        return $this->where('duty_date', $date)->where('period', $period)->first();
    }
}
