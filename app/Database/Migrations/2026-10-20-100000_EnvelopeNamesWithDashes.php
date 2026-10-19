<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Noms d'enveloppe avec des tirets (E20-10-01) au lieu de points (E20.10.01), en trésorerie comme à la caisse.
 */
class EnvelopeNamesWithDashes extends Migration
{
    public function up(): void
    {
        $this->db->query("UPDATE treasury_envelopes SET name = REPLACE(name, '.', '-') WHERE name REGEXP '^E[0-9]{2}[.][0-9]{2}[.][0-9]{2}$'");
        $this->db->query("UPDATE bar_services SET envelope_code = REPLACE(envelope_code, '.', '-') WHERE envelope_code REGEXP '^E[0-9]{2}[.][0-9]{2}[.][0-9]{2}$'");
    }

    public function down(): void
    {
        $this->db->query("UPDATE treasury_envelopes SET name = REPLACE(name, '-', '.') WHERE name REGEXP '^E[0-9]{2}-[0-9]{2}-[0-9]{2}$'");
        $this->db->query("UPDATE bar_services SET envelope_code = REPLACE(envelope_code, '-', '.') WHERE envelope_code REGEXP '^E[0-9]{2}-[0-9]{2}-[0-9]{2}$'");
    }
}
