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

        return view('public/home/index', [
            'title'            => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'             => $news,
            'pager'            => $model->pager,
            'upcoming_matches' => [],
        ]);
    }
}
