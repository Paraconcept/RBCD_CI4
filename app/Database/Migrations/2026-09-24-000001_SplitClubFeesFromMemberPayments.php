<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * La cotisation RBCD et les forfaits suivent l'année civile (jan–déc),
 * la FRBB suit la saison (sep–juin) : on sort le volet club de member_payments
 * vers member_club_fees (une ligne par membre et par année civile).
 */
class SplitClubFeesFromMemberPayments extends Migration
{
    public function up(): void
    {
        $bool = ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0];
        $date = ['type' => 'DATE', 'null' => true];
        $amount = ['type' => 'DECIMAL', 'constraint' => '8,2', 'null' => true];

        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'member_id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => false],
            'year'                 => ['type' => 'SMALLINT', 'constraint' => 4, 'unsigned' => true, 'null' => false],
            'rbcd_h1_paid'         => $bool,
            'rbcd_h1_paid_date'    => $date,
            'rbcd_h1_amount'       => $amount,
            'rbcd_h2_paid'         => $bool,
            'rbcd_h2_paid_date'    => $date,
            'rbcd_h2_amount'       => $amount,
            'forfait_h1_choice'    => $bool,
            'forfait_h1_paid'      => $bool,
            'forfait_h1_paid_date' => $date,
            'forfait_h2_choice'    => $bool,
            'forfait_h2_paid'      => $bool,
            'forfait_h2_paid_date' => $date,
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['member_id', 'year']);
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('member_club_fees');

        $this->forge->addColumn('treasury_settings', [
            'semester_cotisation' => [
                'type' => 'DECIMAL', 'constraint' => '8,2', 'null' => false, 'default' => 30,
                'after' => 'annual_cotisation',
            ],
        ]);

        $this->copyToClubFees();

        $this->forge->dropColumn('member_payments', [
            'rbcd_paid', 'rbcd_paid_date',
            'forfait_f1_choice', 'forfait_f1_paid', 'forfait_f1_paid_date',
            'forfait_f2_choice', 'forfait_f2_paid', 'forfait_f2_paid_date',
        ]);
    }

    public function down(): void
    {
        $bool = ['type' => 'TINYINT', 'constraint' => 1, 'null' => false, 'default' => 0];
        $date = ['type' => 'DATE', 'null' => true];

        $this->forge->addColumn('member_payments', [
            'rbcd_paid'            => $bool + ['after' => 'year'],
            'rbcd_paid_date'       => $date + ['after' => 'rbcd_paid'],
            'forfait_f1_choice'    => $bool + ['after' => 'frbb_paid_date'],
            'forfait_f1_paid'      => $bool + ['after' => 'forfait_f1_choice'],
            'forfait_f1_paid_date' => $date + ['after' => 'forfait_f1_paid'],
            'forfait_f2_choice'    => $bool + ['after' => 'forfait_f1_paid_date'],
            'forfait_f2_paid'      => $bool + ['after' => 'forfait_f2_choice'],
            'forfait_f2_paid_date' => $date + ['after' => 'forfait_f2_paid'],
        ]);

        // Année civile Y → ligne de saison Y-1 (ancienne convention « janvier–décembre ANNEE_2 »)
        $now = date('Y-m-d H:i:s');
        foreach ($this->db->table('member_club_fees')->get()->getResultObject() as $f) {
            $data = [
                'rbcd_paid'            => ($f->rbcd_h1_paid || $f->rbcd_h2_paid) ? 1 : 0,
                'rbcd_paid_date'       => $f->rbcd_h1_paid_date ?? $f->rbcd_h2_paid_date,
                'forfait_f1_choice'    => $f->forfait_h1_choice,
                'forfait_f1_paid'      => $f->forfait_h1_paid,
                'forfait_f1_paid_date' => $f->forfait_h1_paid_date,
                'forfait_f2_choice'    => $f->forfait_h2_choice,
                'forfait_f2_paid'      => $f->forfait_h2_paid,
                'forfait_f2_paid_date' => $f->forfait_h2_paid_date,
            ];
            $where = ['member_id' => $f->member_id, 'year' => $f->year - 1];
            if ($this->db->table('member_payments')->where($where)->countAllResults() > 0) {
                $this->db->table('member_payments')->where($where)->update($data);
            } else {
                $this->db->table('member_payments')->insert($where + $data + ['created_at' => $now, 'updated_at' => $now]);
            }
        }

        $this->forge->dropColumn('treasury_settings', 'semester_cotisation');
        $this->forge->dropTable('member_club_fees');
    }

    private function copyToClubFees(): void
    {
        $settings     = $this->db->table('treasury_settings')->get()->getRowObject();
        $annualAmount = (float) ($settings->annual_cotisation ?? 50);

        $fees = [];
        $slot = function (object $p, ?string $paidDate) use (&$fees): array {
            $year = $this->calendarYear((int) $p->year, $paidDate, $p->created_at);
            $key  = $p->member_id . '-' . $year;
            $fees[$key] ??= [
                'member_id' => (int) $p->member_id, 'year' => $year,
                'rbcd_h1_paid' => 0, 'rbcd_h1_paid_date' => null, 'rbcd_h1_amount' => null,
                'rbcd_h2_paid' => 0, 'rbcd_h2_paid_date' => null, 'rbcd_h2_amount' => null,
                'forfait_h1_choice' => 0, 'forfait_h1_paid' => 0, 'forfait_h1_paid_date' => null,
                'forfait_h2_choice' => 0, 'forfait_h2_paid' => 0, 'forfait_h2_paid_date' => null,
            ];
            return [$key, $year];
        };
        $minDate = fn(?string $a, ?string $b) => $a === null ? $b : ($b === null ? $a : min($a, $b));

        foreach ($this->db->table('member_payments')->get()->getResultObject() as $p) {
            // Ancienne cotisation annuelle : les deux semestres payés, montant porté sur le 1er
            // pour que le bilan ne compte pas deux fois la même cotisation.
            if ($p->rbcd_paid) {
                [$k] = $slot($p, $p->rbcd_paid_date);
                $fees[$k]['rbcd_h1_paid']      = 1;
                $fees[$k]['rbcd_h2_paid']      = 1;
                $fees[$k]['rbcd_h1_paid_date'] = $minDate($fees[$k]['rbcd_h1_paid_date'], $p->rbcd_paid_date);
                $fees[$k]['rbcd_h2_paid_date'] = $fees[$k]['rbcd_h1_paid_date'];
                $fees[$k]['rbcd_h1_amount']    = $annualAmount;
                $fees[$k]['rbcd_h2_amount']    = 0;
            }
            foreach (['f1' => 'h1', 'f2' => 'h2'] as $old => $new) {
                if (!$p->{"forfait_{$old}_choice"} && !$p->{"forfait_{$old}_paid"}) {
                    continue;
                }
                $paidDate = $p->{"forfait_{$old}_paid_date"};
                [$k] = $slot($p, $paidDate);
                $fees[$k]["forfait_{$new}_choice"] = 1;
                if ($p->{"forfait_{$old}_paid"}) {
                    $fees[$k]["forfait_{$new}_paid"]      = 1;
                    $fees[$k]["forfait_{$new}_paid_date"] = $minDate($fees[$k]["forfait_{$new}_paid_date"], $paidDate);
                }
            }
        }

        if ($fees) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('member_club_fees')->insertBatch(array_map(
                fn($f) => $f + ['created_at' => $now, 'updated_at' => $now],
                array_values($fees)
            ));
        }
    }

    /**
     * Ligne de saison S : le volet club visait l'année civile S+1. Sans date de paiement,
     * une ligne créée pendant l'année S (ex. saison 2026 encodée en juillet 2026 pour
     * le forfait juil–déc) vise l'année de création.
     */
    private function calendarYear(int $seasonYear, ?string $paidDate, ?string $createdAt): int
    {
        if ($paidDate) {
            return (int) substr($paidDate, 0, 4);
        }
        $fallback = $seasonYear + 1;
        return $createdAt ? min($fallback, (int) substr($createdAt, 0, 4)) : $fallback;
    }
}
