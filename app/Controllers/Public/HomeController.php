<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\NewsModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $model = new NewsModel();
        $news  = $model->where('is_published', 1)
                       ->where('published_at <=', date('Y-m-d'))
                       ->orderBy('published_at', 'DESC')
                       ->orderBy('id', 'DESC')
                       ->paginate(5);

        $db        = \Config\Database::connect();
        $birthdays = $db->table('members')
                        ->select("first_name, last_name, DATE_FORMAT(birth_date, '%d/%m') AS birthday_day_month")
                        ->where('is_active', 1)
                        ->where('birth_date IS NOT NULL', null, false)
                        ->where('MONTH(birth_date)', (int) date('n'))
                        ->orderBy('DAY(birth_date)', 'ASC')
                        ->get()->getResultArray();

        return view('public/home/index', [
            'title'            => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'             => $news,
            'pager'            => $model->pager,
            'upcoming_matches' => [],
            'birthdays'        => $birthdays,
        ]);
    }
}
