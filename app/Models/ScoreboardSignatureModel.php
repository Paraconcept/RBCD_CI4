<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Signatures de la feuille de match d'une rencontre officielle (joueurs 1 et 2, arbitre).
 * Une feuille signée est verrouillée : plus de correction ni de nouvelle signature au marquoir.
 */
class ScoreboardSignatureModel extends Model
{
    protected $table      = 'scoreboard_signatures';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['match_id', 'referee_name', 'p1_signature', 'p2_signature', 'referee_signature', 'signed_at'];

    public const MAX_BYTES = 1500000;

    public function forMatch(int $matchId): ?object
    {
        return $this->where('match_id', $matchId)->first();
    }

    public function isSigned(int $matchId): bool
    {
        return $this->where('match_id', $matchId)->countAllResults() > 0;
    }

    public static function isValidImage(string $data): bool
    {
        return strlen($data) <= self::MAX_BYTES
            && preg_match('#^data:image/png;base64,[A-Za-z0-9+/]+=*$#', $data) === 1;
    }
}
