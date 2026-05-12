<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryPhotoModel extends Model
{
    protected $table      = 'gallery_photos';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['gallery_id', 'filename', 'caption', 'sort_order'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByGallery(int $galleryId): array
    {
        return $this->where('gallery_id', $galleryId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    public function getNextSortOrder(int $galleryId): int
    {
        $row = $this->selectMax('sort_order')->where('gallery_id', $galleryId)->first();
        return ($row && $row->sort_order !== null) ? (int)$row->sort_order + 1 : 1;
    }
}
