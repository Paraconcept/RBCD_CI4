<?php

namespace App\Models;

use CodeIgniter\Model;

class BarCallModel extends Model
{
    protected $table      = 'bar_calls';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['table_id', 'called_at', 'served_at'];

    // Un appel oublié (barman qui n'a pas cliqué) ne doit pas rester allumé jusqu'au lendemain
    public const EXPIRE_HOURS = 3;

    private function since(): string
    {
        return date('Y-m-d H:i:s', ScoreboardMatchModel::nowTs() - self::EXPIRE_HOURS * 3600);
    }

    /** Appels en attente, indexés par billard. */
    public function pendingByTable(): array
    {
        $rows = $this->where('served_at', null)->where('called_at >=', $this->since())
            ->orderBy('called_at', 'ASC')->findAll();

        $byTable = [];
        foreach ($rows as $r) {
            $byTable[(int) $r->table_id] ??= $r;
        }
        return $byTable;
    }

    /** Appelle le bar ; un deuxième appui pendant un appel en attente ne crée pas de doublon. Retourne [appel, nouveau ?]. */
    public function call(int $tableId): array
    {
        $existing = $this->where('table_id', $tableId)->where('served_at', null)
            ->where('called_at >=', $this->since())->first();
        if ($existing) {
            return [$existing, false];
        }

        $id = $this->insert(['table_id' => $tableId, 'called_at' => ScoreboardMatchModel::now()]);
        return [$this->find($id), true];
    }

    public function serve(int $id): void
    {
        $this->where('id', $id)->where('served_at', null)->set('served_at', ScoreboardMatchModel::now())->update();
    }
}
