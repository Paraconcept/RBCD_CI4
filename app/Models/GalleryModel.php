<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table      = 'galleries';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'title', 'slug', 'description',
        'cover_photo_id', 'event_date', 'season', 'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllWithCover(bool $publishedOnly = true): array
    {
        $q = $this->db->table('galleries g')
            ->select([
                'g.id', 'g.title', 'g.slug', 'g.description',
                'g.event_date', 'g.season', 'g.is_published',
                'p.filename AS cover_filename',
            ])
            ->join('gallery_photos p', 'p.id = g.cover_photo_id', 'left')
            ->orderBy('g.event_date', 'DESC');

        if ($publishedOnly) {
            $q->where('g.is_published', 1);
        }

        return $q->get()->getResultObject();
    }

    public function getBySlug(string $slug): ?object
    {
        return $this->db->table('galleries g')
            ->select([
                'g.id', 'g.title', 'g.slug', 'g.description',
                'g.event_date', 'g.season', 'g.is_published', 'g.cover_photo_id',
                'p.filename AS cover_filename',
            ])
            ->join('gallery_photos p', 'p.id = g.cover_photo_id', 'left')
            ->where('g.slug', $slug)
            ->where('g.is_published', 1)
            ->get()->getRowObject() ?: null;
    }

    public function getWithCover(int $id): ?object
    {
        return $this->db->table('galleries g')
            ->select([
                'g.id', 'g.title', 'g.slug', 'g.description',
                'g.event_date', 'g.season', 'g.is_published', 'g.cover_photo_id',
                'p.filename AS cover_filename',
            ])
            ->join('gallery_photos p', 'p.id = g.cover_photo_id', 'left')
            ->where('g.id', $id)
            ->get()->getRowObject() ?: null;
    }

    public function getPhotoCount(int $id): int
    {
        return $this->db->table('gallery_photos')
            ->where('gallery_id', $id)
            ->countAllResults();
    }
}
