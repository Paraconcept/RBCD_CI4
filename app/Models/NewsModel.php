<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table      = 'news';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'title', 'slug', 'excerpt', 'content', 'image', 'published_at', 'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPublished(): array
    {
        return $this->where('is_published', 1)
                    ->where('published_at <=', date('Y-m-d'))
                    ->orderBy('published_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    public function getBySlug(string $slug): ?object
    {
        return $this->where('slug', $slug)
                    ->where('is_published', 1)
                    ->where('published_at <=', date('Y-m-d'))
                    ->first();
    }
}
