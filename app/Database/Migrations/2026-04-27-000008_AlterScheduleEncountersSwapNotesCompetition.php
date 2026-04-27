<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncountersSwapNotesCompetition extends Migration
{
    public function up()
    {
        // Swap simultané : notes → competition (TEXT), competition → notes (TEXT)
        $this->db->query("
            ALTER TABLE schedule_encounters
                CHANGE `notes`       `competition` TEXT          NULL,
                CHANGE `competition` `notes`       TEXT          NULL
        ");
    }

    public function down()
    {
        $this->db->query("
            ALTER TABLE schedule_encounters
                CHANGE `competition` `notes`       TEXT          NULL,
                CHANGE `notes`       `competition` VARCHAR(100)  NOT NULL DEFAULT ''
        ");
    }
}
