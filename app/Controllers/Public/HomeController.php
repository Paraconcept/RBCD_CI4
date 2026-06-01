<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\ScheduleEncounterModel;
use App\Models\ScheduleEventModel;
use App\Models\SiteSettingModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $perPage = (int) (new SiteSettingModel())->getSetting('news_per_page', 5);
        $model   = new NewsModel();
        $news    = $model->where('is_published', 1)
                         ->where('published_at <=', date('Y-m-d'))
                         ->orderBy('published_at', 'DESC')
                         ->orderBy('id', 'DESC')
                         ->paginate($perPage);

        $db      = \Config\Database::connect();
        $members = $db->table('members')
                      ->select('id, first_name, last_name, birth_date, photo, gender')
                      ->where('is_active', 1)
                      ->where('birth_date IS NOT NULL', null, false)
                      ->where('show_birth_date', 1)
                      ->get()->getResultArray();

        $today  = new \DateTime();
        $dow    = (int) $today->format('N');
        $monday = (clone $today)->modify('-' . ($dow - 1) . ' days');
        $sunday = (clone $monday)->modify('+6 days');
        $year   = (int) $today->format('Y');

        $birthdays = [];
        foreach ($members as $m) {
            [$y, $mo, $d] = explode('-', $m['birth_date']);
            try { $bday = new \DateTime("$year-$mo-$d"); } catch (\Exception $e) { continue; }
            if ($bday >= $monday && $bday <= $sunday) {
                $m['birthday_day_month'] = $bday->format('d/m');
                $m['age']                = $year - (int) $y;
                $birthdays[] = $m;
            }
        }
        usort($birthdays, fn($a, $b) => $a['birthday_day_month'] <=> $b['birthday_day_month']);

        $today   = date('Y-m-d');
        $maxDate = date('Y-m-d', strtotime('+60 days'));

        $encRows = (new ScheduleEncounterModel())
            ->where('match_date >=', $today)
            ->where('match_date <=', $maxDate)
            ->orderBy('match_date')->orderBy('match_time')
            ->findAll();

        $encByDate = [];
        if (!empty($encRows)) {
            $ids     = array_map(fn($e) => $e->id, $encRows);
            $players = $db->table('schedule_encounter_players sep')
                ->select('sep.encounter_id, sep.member_id, sep.player_home_name, sep.opponent_name, m.last_name, m.first_name')
                ->join('members m', 'm.id = sep.member_id', 'left')
                ->whereIn('sep.encounter_id', $ids)
                ->get()->getResultObject();
            $byEnc = [];
            foreach ($players as $p) { $byEnc[$p->encounter_id][] = $p; }
            foreach ($encRows as $enc) {
                $enc->players = $byEnc[$enc->id] ?? [];
                $encByDate[$enc->match_date][] = $enc;
            }
        }

        $evRows = (new ScheduleEventModel())
            ->where('event_date >=', $today)
            ->where('event_date <=', $maxDate)
            ->orderBy('event_date')->orderBy('start_time')
            ->findAll();

        $evByDate = [];
        foreach ($evRows as $ev) { $evByDate[$ev->event_date][] = $ev; }

        $allDates = array_unique(array_merge(array_keys($encByDate), array_keys($evByDate)));
        sort($allDates);
        $allDates = array_slice($allDates, 0, 2);

        $upcomingDays = [];
        foreach ($allDates as $date) {
            $upcomingDays[] = [
                'date'       => $date,
                'isToday'    => $date === $today,
                'encounters' => $encByDate[$date] ?? [],
                'events'     => $evByDate[$date] ?? [],
            ];
        }

        return view('public/home/index', [
            'title'        => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'         => $news,
            'pager'        => $model->pager,
            'birthdays'    => $birthdays,
            'upcomingDays' => $upcomingDays,
            'eventColors'  => ScheduleEventModel::$colors,
        ]);
    }
}
