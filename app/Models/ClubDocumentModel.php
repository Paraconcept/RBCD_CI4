<?php

namespace App\Models;

use CodeIgniter\Model;

class ClubDocumentModel extends Model
{
    protected $table         = 'club_documents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'slug', 'title', 'filename', 'uploaded_at',
    ];

    public function findBySlug(string $slug): ?object
    {
        return $this->where('slug', $slug)->first();
    }
}
