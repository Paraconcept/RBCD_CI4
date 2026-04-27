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
        'duty_date', 'period', 'admin_user_id', 'member_id',
    ];

    public function getForDates(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        $rows = $this->db->table('schedule_bar_duties bd')
            ->select('bd.*, COALESCE(m.last_name, au.last_name) AS last_name, COALESCE(m.first_name, au.first_name) AS first_name')
            ->join('members m',     'm.id  = bd.member_id',    'left')
            ->join('admin_users au','au.id = bd.admin_user_id', 'left')
            ->whereIn('bd.duty_date', $dates)
            ->get()->getResultObject();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->duty_date][$row->period] = $row;
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
