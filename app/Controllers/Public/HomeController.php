<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\NewsModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $news = (new NewsModel())->getPublished();
        $news = array_slice($news, 0, 3);

        return view('public/home/index', [
            'title'            => 'RBC Disonais — Club de Billard Carambole à Dison',
            'news'             => $news,
            'upcoming_matches' => [],
        ]);
    }
}
