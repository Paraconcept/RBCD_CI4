<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\NewsModel;
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
                      ->select('first_name, last_name, birth_date, photo, gender')
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

        return view('public/home/index', [
            'title'            => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'             => $news,
            'pager'            => $model->pager,
            'upcoming_matches' => [],
            'birthdays'        => $birthdays,
        ]);
    }
}
