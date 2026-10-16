<?php

namespace App\Models;

use CodeIgniter\Model;

class ScoreboardFinalPlayerModel extends Model
{
    protected $table      = 'scoreboard_final_players';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'final_id', 'position', 'member_id', 'last_name', 'first_name', 'club', 'club_code', 'license',
        'category_id', 'category', 'average_min', 'average_max', 'distance',
    ];

    /**
     * Codes FRBB déjà saisis, par nom de club normalisé (« R.B.C. Disonais » = « RBC Disonais ») ;
     * le code du RBC Disonais vient des paramètres du site.
     */
    public function clubCodes(): array
    {
        $codes = [self::clubKey(ScoreboardPlayerModel::HOME_CLUB) => (new SiteSettingModel())->getSetting('frbb_club_code', '')];
        foreach ((new ScoreboardPlayerModel())->select('club, club_code')->where('club_code IS NOT NULL')->where('club IS NOT NULL')->findAll() as $r) {
            $codes[self::clubKey($r->club)] = $r->club_code;
        }
        foreach ($this->select('club, club_code')->where('club_code IS NOT NULL')->where('club IS NOT NULL')->orderBy('id', 'ASC')->findAll() as $r) {
            $codes[self::clubKey($r->club)] = $r->club_code;
        }
        return array_filter($codes);
    }

    public static function clubKey(string $club): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower($club));
    }

    public function getForFinal(int $finalId): array
    {
        return $this->where('final_id', $finalId)->orderBy('position', 'ASC')->findAll();
    }
}
