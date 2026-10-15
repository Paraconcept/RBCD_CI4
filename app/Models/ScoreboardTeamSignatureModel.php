<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Signatures des deux capitaines sur la feuille récapitulative d'une rencontre d'équipe.
 * Une rencontre signée est verrouillée : plus de correction de ses matchs ni du commentaire au marquoir.
 */
class ScoreboardTeamSignatureModel extends Model
{
    protected $table      = 'scoreboard_team_signatures';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['encounter_id', 'home_signature', 'away_signature', 'signed_at'];

    public function forEncounter(int $encounterId): ?object
    {
        return $this->where('encounter_id', $encounterId)->first();
    }

    public function isSigned(?int $encounterId): bool
    {
        return $encounterId && $this->where('encounter_id', $encounterId)->countAllResults() > 0;
    }

    /** Date de signature sans charger les images (fiche admin, page du billard). */
    public function signedAt(int $encounterId): ?string
    {
        $row = $this->select('signed_at')->where('encounter_id', $encounterId)->first();
        return $row->signed_at ?? null;
    }
}
