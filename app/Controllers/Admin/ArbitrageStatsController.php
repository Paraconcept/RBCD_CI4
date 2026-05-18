<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ArbitrageStatsController extends BaseController
{
    public function index(?string $seasonYear = null): string
    {
        $now = new \DateTime();
        if (!$seasonYear) {
            $y = (int) $now->format('Y');
            $seasonYear = ($now >= new \DateTime("{$y}-08-15")) ? $y : $y - 1;
        }
        $seasonYear  = (int) $seasonYear;
        $seasonStart = "{$seasonYear}-08-15";
        $seasonEnd   = ($seasonYear + 1) . "-06-30";

        $db = \Config\Database::connect();

        $members = $db->table('members')
            ->select('id, last_name, first_name')
            ->where('is_active', 1)
            ->where('is_federated', 1)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()->getResultObject();

        // Home match days per member (distinct dates)
        $homeRows = $db->table('schedule_encounter_players sep')
            ->select('sep.member_id, se.match_date')
            ->join('schedule_encounters se', 'se.id = sep.encounter_id')
            ->where('se.is_home', 1)
            ->where('sep.member_id IS NOT NULL', null, false)
            ->where('se.match_date >=', $seasonStart)
            ->where('se.match_date <=', $seasonEnd)
            ->get()->getResultObject();

        $arbRows = $db->query("
            SELECT sa.member_id AS resolved_member_id, se.match_date
            FROM schedule_arbitrage sa
            JOIN schedule_encounters se ON se.id = sa.encounter_id
            JOIN members m ON m.id = sa.member_id
            WHERE se.match_date >= ? AND se.match_date <= ?
              AND sa.member_id IS NOT NULL
              AND m.is_federated = 1
        ", [$seasonStart, $seasonEnd])->getResultObject();

        $barRows = $db->query("
            SELECT bd.member_id AS resolved_member_id, bd.duty_date
            FROM schedule_bar_duties bd
            JOIN members m ON m.id = bd.member_id
            WHERE bd.duty_date >= ? AND bd.duty_date <= ?
              AND bd.member_id IS NOT NULL
              AND m.is_federated = 1
        ", [$seasonStart, $seasonEnd])->getResultObject();

        $mrqRows = $db->query("
            SELECT sm.member_id AS resolved_member_id, se.match_date
            FROM schedule_marqueurs sm
            JOIN schedule_encounters se ON se.id = sm.encounter_id
            JOIN members m ON m.id = sm.member_id
            WHERE se.match_date >= ? AND se.match_date <= ?
              AND sm.member_id IS NOT NULL
              AND m.is_federated = 1
        ", [$seasonStart, $seasonEnd])->getResultObject();

        // Index home dates per member (unique dates)
        $homeByMember = [];
        $allDates     = [];
        foreach ($homeRows as $r) {
            $homeByMember[$r->member_id][$r->match_date] = true;
            $allDates[$r->match_date] = true;
        }

        // Index arb dates per member (all entries — one per service rendered)
        $arbByMember = [];
        foreach ($arbRows as $r) {
            $arbByMember[$r->resolved_member_id][] = $r->match_date;
            $allDates[$r->match_date] = true;
        }

        // Index bar dates per member (all entries — one per period)
        $barByMember = [];
        foreach ($barRows as $r) {
            $barByMember[$r->resolved_member_id][] = $r->duty_date;
            $allDates[$r->duty_date] = true;
        }

        // Index marqueur dates per member
        $mrqByMember = [];
        foreach ($mrqRows as $r) {
            $mrqByMember[$r->resolved_member_id][] = $r->match_date;
            $allDates[$r->match_date] = true;
        }

        ksort($allDates);
        $dates = array_keys($allDates);

        $stats = [];
        foreach ($members as $m) {
            $homeDates = $homeByMember[$m->id] ?? [];
            $arbDates  = $arbByMember[$m->id]  ?? [];
            $barDates  = $barByMember[$m->id]  ?? [];
            $mrqDates  = $mrqByMember[$m->id]  ?? [];

            $homeCount = count($homeDates);
            $arbCount  = count($arbDates);
            $barCount  = count($barDates);
            $mrqCount  = count($mrqDates);
            $required  = $homeCount * 3 / 2;
            $done      = $arbCount + $barCount + $mrqCount;

            if ($homeCount === 0 && $done === 0) {
                $status = 'none';
            } elseif ($done < $required) {
                $status = 'deficit';
            } else {
                $status = 'ok';
            }

            $stats[$m->id] = [
                'home_dates' => $homeDates,
                'arb_dates'  => array_count_values($arbDates),
                'bar_dates'  => array_count_values($barDates),
                'mrq_dates'  => array_count_values($mrqDates),
                'home_count' => $homeCount,
                'arb_count'  => $arbCount,
                'bar_count'  => $barCount,
                'mrq_count'  => $mrqCount,
                'required'   => $required,
                'done'       => $done,
                'status'     => $status,
            ];
        }

        // Build list of available season years (current season year - 3 → current)
        $currentSeasonYear = ($now >= new \DateTime($now->format('Y') . '-08-15'))
            ? (int) $now->format('Y')
            : (int) $now->format('Y') - 1;
        $availableSeasons  = range($currentSeasonYear, max($currentSeasonYear - 3, 2023));

        return view('admin/arbitrage_stats/index', [
            'title'            => 'Statistiques d\'arbitrage',
            'breadcrumbs'      => [['title' => 'Statistiques d\'arbitrage']],
            'seasonYear'       => $seasonYear,
            'seasonStart'      => $seasonStart,
            'seasonEnd'        => $seasonEnd,
            'dates'            => $dates,
            'members'          => $members,
            'stats'            => $stats,
            'availableSeasons' => $availableSeasons,
        ]);
    }
}
