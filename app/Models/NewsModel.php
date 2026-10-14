<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table      = 'news';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'title', 'slug', 'excerpt', 'content', 'image', 'gallery_id', 'published_at', 'is_published',
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

    /** Premier jour de la saison en cours (la saison sportive commence en août, cf. Constants.php). */
    public static function seasonStart(): string
    {
        return ANNEE_1 . '-08-01';
    }

    /** Saison « 2025-2026 » d'une date de publication. */
    public static function seasonOf(string $date): string
    {
        $y = (int) substr($date, 0, 4);
        return (int) substr($date, 5, 2) < 8 ? ($y - 1) . '-' . $y : $y . '-' . ($y + 1);
    }
}
