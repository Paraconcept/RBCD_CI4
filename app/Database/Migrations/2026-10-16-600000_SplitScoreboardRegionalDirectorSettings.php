<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SplitScoreboardRegionalDirectorSettings extends Migration
{
    public function up(): void
    {
        // « Destinataire des feuilles de match par sets » (texte libre) devient nom, e-mail et GSM du DSR,
        // repris aussi sur la feuille du championnat régional
        $old = $this->db->table('site_settings')->where('key', 'scoreboard_sets_sheet_recipient')->get()->getRow();
        $values = [];
        if ($old && $old->value !== '') {
            if (preg_match('/^\s*(.+?)\s+-\s+D\.S\.R\./u', $old->value, $m)) {
                $values['scoreboard_dsr_name'] = $m[1];
            }
            if (preg_match('/[\w.+-]+@[\w-]+\.[\w.-]+/u', $old->value, $m)) {
                $values['scoreboard_dsr_email'] = $m[0];
            }
            if (preg_match('/Gsm\s*:\s*([\d \/.]+)/iu', $old->value, $m)) {
                $values['scoreboard_dsr_mobile'] = trim($m[1]);
            }
        }
        $now = date('Y-m-d H:i:s');
        foreach ($values as $key => $value) {
            $this->db->table('site_settings')->insert(['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now]);
        }
        $this->db->table('site_settings')->where('key', 'scoreboard_sets_sheet_recipient')->delete();

        $this->forge->dropColumn('scoreboard_finals', 'regional_contact');
    }

    public function down(): void
    {
        $this->forge->addColumn('scoreboard_finals', [
            'regional_contact' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'kind'],
        ]);
        $this->db->table('site_settings')->whereIn('key', ['scoreboard_dsr_name', 'scoreboard_dsr_email', 'scoreboard_dsr_mobile'])->delete();
    }
}
