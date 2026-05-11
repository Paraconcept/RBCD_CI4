<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JournalIssuesSeeder extends Seeder
{
    private array $monthsFr = [
        1  => 'Janvier',  2  => 'Février',   3  => 'Mars',
        4  => 'Avril',    5  => 'Mai',        6  => 'Juin',
        7  => 'Juillet',  8  => 'Août',       9  => 'Septembre',
        10 => 'Octobre',  11 => 'Novembre',   12 => 'Décembre',
    ];

    public function run(): void
    {
        $uploadPath = FCPATH . 'uploads/PDF/PartieLibre/';
        $files      = glob($uploadPath . '*.pdf') ?: [];
        sort($files);

        $rows = [];
        foreach ($files as $path) {
            $name = basename($path);
            // Pattern: YYYY_Partie_Libre_MM.pdf
            if (!preg_match('/^(\d{4})_Partie_Libre_(\d{2})\.pdf$/i', $name, $m)) {
                continue;
            }

            $year  = (int) $m[1];
            $month = (int) $m[2];
            $title = ($this->monthsFr[$month] ?? 'Mois ' . $month) . ' ' . $year;
            $date  = sprintf('%04d-%02d-01', $year, $month);

            $rows[] = [
                'title'          => $title,
                'published_date' => $date,
                'description'    => null,
                'file_path'      => $name,
                'is_published'   => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];
        }

        if (!empty($rows)) {
            $this->db->table('journal_issues')->insertBatch($rows);
        }
    }
}
