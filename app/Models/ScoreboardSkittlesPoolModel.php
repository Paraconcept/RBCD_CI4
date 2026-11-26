<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Soirée Poule 5 Quilles (tournoi régional, officiel) : trois joueurs A, B, C et trois matchs dans l'ordre du
 * calendrier officiel (B/C, C/A, A/B), à reprises inégales et en deux sets gagnants.
 * Points de match : victoire 2-0 = 3, victoire 2-1 = 2, défaite = 0. Point de set : 1 par set gagné.
 * Forfait (déclaré match par match) : l'absent a 0 partout, le présent 2 points de match et aucun point de set.
 * Pas de classement : le récapitulatif garde l'ordre A, B, C (Total = points de match + points de set).
 */
class ScoreboardSkittlesPoolModel extends Model
{
    public const NAME = 'Soirée Poule 5 Quilles';

    /** Points d'un set selon le billard (la feuille fait biffer la mention inutile). */
    public const BILLIARDS = [
        '230' => ['label' => 'Billard 2,30', 'points' => 100],
        '284' => ['label' => 'Billard 2,84', 'points' => 60],
    ];

    public const SLOTS = [1 => 'A', 2 => 'B', 3 => 'C'];

    /** Calendrier officiel : n° du match => [lettre, lettre] (1 = B contre C, 2 = C contre A, 3 = A contre B) */
    public const PAIRS = [1 => [2, 3], 2 => [3, 1], 3 => [1, 2]];

    public const SETS_TO_WIN = 2;

    /** Match gagné par forfait : points de match du joueur présent ; jamais de point de set (points « bonus »). */
    public const FORFEIT_POINTS = 2;

    public const PLAYER_FIELDS = ['member_id', 'last_name', 'first_name', 'club', 'license'];

    protected $table      = 'scoreboard_skittles_pools';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'name', 'scheduled_at', 'table_id', 'billiard', 'set_points',
        'a_member_id', 'a_last_name', 'a_first_name', 'a_club', 'a_license',
        'b_member_id', 'b_last_name', 'b_first_name', 'b_club', 'b_license',
        'c_member_id', 'c_last_name', 'c_first_name', 'c_club', 'c_license',
        'comment', 'signature_required', 'send_to_regional', 'regional_email', 'send_to_club_ds', 'club_ds_email',
        'sent_at', 'sent_to', 'send_error', 'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Responsable régional du 5 quilles, destinataire de la feuille récapitulative. */
    public static function regional(): array
    {
        $s = new SiteSettingModel();
        return [
            'name'  => (string) $s->getSetting('scoreboard_skittles_name', 'Joël GROENEN'),
            'email' => (string) $s->getSetting('scoreboard_skittles_email', 'joel.groenen@proximus.be'),
        ];
    }

    public static function letter(int $slot): string
    {
        return self::SLOTS[$slot] ?? '?';
    }

    /** Clé des colonnes d'un joueur de la poule : 1 → « a » */
    public static function key(int $slot): string
    {
        return strtolower(self::letter($slot));
    }

    public static function playerName(object $pool, int $slot): string
    {
        $k = self::key($slot);
        return trim($pool->{$k . '_last_name'} . ' ' . $pool->{$k . '_first_name'});
    }

    /** Poules avec l'avancement de leurs matchs. */
    public function withProgress()
    {
        return $this->select("scoreboard_skittles_pools.*, t.name AS table_name,
                COUNT(m.id) AS matches_total,
                SUM(m.status = 'finished') AS matches_finished,
                SUM(m.status = 'live') AS matches_live,
                (SELECT signed_at FROM scoreboard_skittles_signatures s WHERE s.pool_id = scoreboard_skittles_pools.id) AS signed_at")
            ->join('scoreboard_tables t', 't.id = scoreboard_skittles_pools.table_id', 'left')
            ->join('scoreboard_matches m', "m.skittles_pool_id = scoreboard_skittles_pools.id AND m.status <> 'cancelled'", 'left')
            ->groupBy('scoreboard_skittles_pools.id');
    }

    public function getToday(): array
    {
        return $this->where('scheduled_at >=', ScoreboardMatchModel::now('Y-m-d 00:00:00'))
            ->where('scheduled_at <=', ScoreboardMatchModel::now('Y-m-d 23:59:59'))
            ->orderBy('scheduled_at', 'ASC')
            ->findAll();
    }

    /** Matchs de la poule rangés par n° du calendrier (1 = B/C, 2 = C/A, 3 = A/B). */
    public function getMatches(int $id): array
    {
        $out = [];
        foreach ((new ScoreboardMatchModel())->withTable()->where('skittles_pool_id', $id)->where('status <>', 'cancelled')->findAll() as $m) {
            $out[self::matchNumber($m)] = $m;
        }
        ksort($out);
        return $out;
    }

    /** N° du match sur la feuille d'après les lettres de ses deux joueurs (le joueur qui commence passe en p1). */
    public static function matchNumber(object $m): int
    {
        $pair = [(int) $m->p1_pool_slot, (int) $m->p2_pool_slot];
        sort($pair);
        foreach (self::PAIRS as $no => $p) {
            sort($p);
            if ($p === $pair) {
                return $no;
            }
        }
        return 0;
    }

    public function hasStarted(int $id): bool
    {
        return $this->db->table('scoreboard_matches')->where('skittles_pool_id', $id)
            ->whereIn('status', ['live', 'finished'])->countAllResults() > 0;
    }

    /** (Re)crée les trois matchs ; uniquement tant qu'aucun n'a commencé. */
    public function generateMatches(object $pool, ?int $createdBy = null): void
    {
        $matches = new ScoreboardMatchModel();
        $this->db->transStart();
        $this->db->table('scoreboard_matches')->where('skittles_pool_id', $pool->id)->delete();
        foreach (self::PAIRS as $no => [$a, $b]) {
            // final_order : le marquoir propose les matchs dans l'ordre du calendrier
            $matches->insert(self::matchData($pool, $a, $b) + ['final_order' => $no, 'status' => 'scheduled', 'created_by' => $createdBy]);
        }
        $this->db->transComplete();
    }

    /** Données communes aux matchs d'une poule (création, et report des changements tant qu'ils ne sont pas commencés). */
    public static function matchData(object $pool, int $a, int $b): array
    {
        $data = [
            'type' => 'official', 'skittles_pool_id' => $pool->id, 'table_id' => $pool->table_id,
            'scheduled_at' => $pool->scheduled_at, 'competition' => self::NAME . ' — ' . $pool->name,
            'discipline' => ScoreboardMatchModel::SKITTLES, 'format' => 'GB',
            'sets_to_win' => self::SETS_TO_WIN, 'unequal_innings' => 1, 'innings_limit' => null,
            'shot_clock_enabled' => 0, 'signature_required' => 0,
            // Seule la feuille récapitulative de la poule est envoyée
            'send_to_club_ds' => 0, 'send_to_dsr' => 0, 'send_to_cdr' => 0, 'send_to_organizer_ds' => 0,
            'send_to_p1' => 0, 'send_to_p2' => 0, 'recap_to_p1' => 0, 'recap_to_p2' => 0,
        ];
        foreach ([1 => $a, 2 => $b] as $n => $slot) {
            $k = self::key($slot);
            $data['p' . $n . '_pool_slot'] = $slot;
            foreach (self::PLAYER_FIELDS as $f) {
                $data['p' . $n . '_' . $f] = $pool->{$k . '_' . $f};
            }
            $data['p' . $n . '_distance'] = (int) $pool->set_points;
            foreach (['category_id', 'category', 'average_min'] as $f) {
                $data['p' . $n . '_' . $f] = null;
            }
        }
        return $data;
    }

    /**
     * Moyenne de la feuille : points réalisés / points de l'adversaire, TRONQUÉE à 3 décimales (jamais arrondie),
     * « 0,000 » quand l'adversaire n'a rien marqué.
     */
    public static function average(int $own, int $opponent): string
    {
        if ($opponent <= 0) {
            return '0,000';
        }
        $milli = intdiv($own * 1000, $opponent);
        return intdiv($milli, 1000) . ',' . str_pad((string) ($milli % 1000), 3, '0', STR_PAD_LEFT);
    }

    /** Points de match d'un joueur : victoire 2-0 = 3, victoire 2-1 = 2, défaite = 0. */
    public static function matchPoints(int $setsWon, int $setsLost): int
    {
        if ($setsWon < self::SETS_TO_WIN) {
            return 0;
        }
        return $setsLost === 0 ? 3 : 2;
    }

    /**
     * Tout ce que la feuille récapitulative affiche.
     * matches[n] : players [lettre côté 1, lettre côté 2], status, sets (1 à 3, null si non joué) avec pour chaque
     * lettre points réalisés (fautes adverses comprises), point de set et moyenne ; totals par lettre.
     * recap[lettre] : nom, points de match, points de set, total (matchs terminés uniquement).
     */
    public function sheet(object $pool): array
    {
        $model   = new ScoreboardMatchModel();
        $matches = $this->getMatches((int) $pool->id);
        $recap   = [];
        foreach (self::SLOTS as $slot => $letter) {
            $recap[$slot] = ['name' => self::playerName($pool, $slot), 'match_points' => null, 'set_points' => null, 'total' => null];
        }

        $out = [];
        foreach (self::PAIRS as $no => [$a, $b]) {
            $m    = $matches[$no] ?? null;
            $item = ['no' => $no, 'players' => [$a, $b], 'match' => $m, 'status' => $m->status ?? null, 'sets' => [1 => null, 2 => null, 3 => null],
                     'totals' => null, 'absent' => []];
            if (!$m || $m->status === 'scheduled') {
                $out[$no] = $item;
                continue;
            }
            // Côté (1 ou 2) de chaque lettre dans le match
            $side = [(int) $m->p1_pool_slot => 1, (int) $m->p2_pool_slot => 2];

            // Forfait : l'absent a 0 partout (il reste dans la poule), le présent reçoit les points de match du forfait
            if ((int) $m->absent > 0) {
                $item['absent'] = self::absentSlots($m);
                foreach ([$a, $b] as $me) {
                    $mp = in_array($me, $item['absent'], true) ? 0 : self::FORFEIT_POINTS;
                    $item['totals'][$me] = ['points' => 0, 'sets' => 0, 'average' => self::average(0, 0), 'match_points' => $mp];
                    $recap[$me]['match_points'] = ($recap[$me]['match_points'] ?? 0) + $mp;
                    $recap[$me]['set_points']   = ($recap[$me]['set_points'] ?? 0);
                }
                $out[$no] = $item;
                continue;
            }
            $walk = ScoreboardMatchModel::walkInnings($m, $model->stateFor($m)['innings']);
            $pts  = [];
            foreach ($walk['rows'] as $r) {
                $pts[$r['set']][$r['to']] = ($pts[$r['set']][$r['to']] ?? 0) + $r['gain'];
            }
            $tot = [$a => ['points' => 0, 'sets' => 0], $b => ['points' => 0, 'sets' => 0]];
            foreach ($pts as $setNo => $bySide) {
                if ($setNo > 3) {
                    continue;
                }
                $set = [];
                foreach ([$a => $b, $b => $a] as $me => $other) {
                    $mine   = (int) ($bySide[$side[$me]] ?? 0);
                    $theirs = (int) ($bySide[$side[$other]] ?? 0);
                    $won    = $mine >= (int) $m->{'p' . $side[$me] . '_distance'} ? 1 : 0;
                    $set[$me] = ['points' => $mine, 'set_point' => $won, 'average' => self::average($mine, $theirs)];
                    $tot[$me]['points'] += $mine;
                    $tot[$me]['sets']   += $won;
                }
                $item['sets'][$setNo] = $set;
            }
            $finished = $m->status === 'finished';
            foreach ([$a => $b, $b => $a] as $me => $other) {
                $tot[$me]['average']      = self::average($tot[$me]['points'], $tot[$other]['points']);
                $tot[$me]['match_points'] = $finished ? self::matchPoints($tot[$me]['sets'], $tot[$other]['sets']) : null;
                if ($finished) {
                    $recap[$me]['match_points'] = ($recap[$me]['match_points'] ?? 0) + $tot[$me]['match_points'];
                    $recap[$me]['set_points']   = ($recap[$me]['set_points'] ?? 0) + $tot[$me]['sets'];
                }
            }
            $item['totals'] = $tot;
            $out[$no] = $item;
        }
        foreach ($recap as &$r) {
            if ($r['match_points'] !== null) {
                $r['total'] = $r['match_points'] + $r['set_points'];
            }
        }
        unset($r);

        $finished = count(array_filter($out, static fn ($i) => $i['status'] === 'finished'));

        return ['matches' => $out, 'recap' => $recap, 'finished' => $finished, 'complete' => $finished === count(self::PAIRS)];
    }

    /** Lettres des joueurs absents d'un match déclaré forfait (absent : 1 = p1, 2 = p2, 3 = les deux). */
    public static function absentSlots(object $m): array
    {
        $out = [];
        foreach ([1, 2] as $n) {
            if ((int) $m->absent & $n) {
                $out[] = (int) $m->{'p' . $n . '_pool_slot'};
            }
        }
        return $out;
    }

    /** Saison de la feuille : « 2026 - 2027 » (la saison commence en juillet). */
    public static function season(?string $date): string
    {
        return str_replace('-', ' - ', ScoreboardFinalModel::season($date));
    }
}
