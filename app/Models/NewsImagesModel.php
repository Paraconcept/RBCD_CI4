<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsImagesModel extends Model
{
    protected $table         = 'news_images';
    protected $allowedFields = ['news_id', 'filename', 'sort_order'];
    protected $useTimestamps = false;

    public function getByNewsId(int $newsId): array
    {
        return $this->where('news_id', $newsId)->orderBy('sort_order', 'ASC')->findAll();
    }

    public function getNextSortOrder(int $newsId): int
    {
        $max = $this->selectMax('sort_order')->where('news_id', $newsId)->first();
        return $max ? (int) $max->sort_order + 1 : 0;
    }
}
