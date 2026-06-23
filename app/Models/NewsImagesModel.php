<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsImagesModel extends Model
{
    protected $table         = 'news_images';
    protected $returnType    = 'object';
    protected $allowedFields = ['news_id', 'filename', 'sort_order'];
    protected $useTimestamps = false;

    public function getByNewsId(int $newsId): array
    {
        return $this->where('news_id', $newsId)->orderBy('sort_order', 'ASC')->findAll();
    }

    public function getNextSortOrder(int $newsId): int
    {
        $row = $this->db->table($this->table)
                        ->selectMax('sort_order')
                        ->where('news_id', $newsId)
                        ->get()->getRowArray();

        return isset($row['sort_order']) && $row['sort_order'] !== null
            ? (int) $row['sort_order'] + 1
            : 0;
    }
}
