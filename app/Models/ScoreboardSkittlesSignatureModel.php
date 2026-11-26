<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Signatures des joueurs A, B et C sur la feuille récapitulative d'une Soirée Poule 5 Quilles.
 * Une poule signée est verrouillée : plus de correction de ses matchs ni du commentaire au marquoir.
 */
class ScoreboardSkittlesSignatureModel extends Model
{
    protected $table      = 'scoreboard_skittles_signatures';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['pool_id', 'a_signature', 'b_signature', 'c_signature', 'signed_at'];

    public function forPool(int $poolId): ?object
    {
        return $this->where('pool_id', $poolId)->first();
    }

    public function isSigned(?int $poolId): bool
    {
        return $poolId && $this->where('pool_id', $poolId)->countAllResults() > 0;
    }

    public function signedAt(int $poolId): ?string
    {
        $row = $this->select('signed_at')->where('pool_id', $poolId)->first();
        return $row->signed_at ?? null;
    }
}
