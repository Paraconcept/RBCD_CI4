<?php

namespace App\Models;

use CodeIgniter\Model;

class JournalIssueModel extends Model
{
    protected $table      = 'journal_issues';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'title', 'published_date',
        'description', 'file_path', 'is_published',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPublishedGroupedByYear(): array
    {
        $rows = $this->where('is_published', 1)
                     ->orderBy('published_date', 'ASC')
                     ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $year = $row->published_date ? date('Y', strtotime($row->published_date)) : '—';
            $grouped[$year][] = $row;
        }

        krsort($grouped); // années les plus récentes en premier

        return $grouped;
    }
}
