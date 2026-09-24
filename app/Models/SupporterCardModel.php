<?php

namespace App\Models;

use CodeIgniter\Model;

class SupporterCardModel extends Model
{
    public const SESSIONS_PER_CARD = 5;
    public const VALIDITY_MONTHS   = 6;

    protected $table      = 'supporter_cards';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'member_id',
        'card_number',
        'purchase_date',
        'expiry_date',
        'amount',
        'notes',
    ];

    /**
     * Cartes du membre (plus récente d'abord), chacune avec ->sessions, ->used et ->status.
     */
    public function getForMember(int $memberId): array
    {
        $cards = $this->where('member_id', $memberId)
                      ->orderBy('purchase_date', 'DESC')->orderBy('id', 'DESC')
                      ->findAll();
        if (!$cards) {
            return [];
        }

        $sessions = (new SupporterCardSessionModel())
            ->whereIn('card_id', array_column($cards, 'id'))
            ->orderBy('session_date')->orderBy('id')
            ->findAll();

        $byCard = [];
        foreach ($sessions as $s) {
            $byCard[$s->card_id][] = $s;
        }
        foreach ($cards as $c) {
            $c->sessions = $byCard[$c->id] ?? [];
            $c->used     = count($c->sessions);
            $c->status   = self::status($c->used, $c->expiry_date);
        }
        return $cards;
    }

    /**
     * Pour la trésorerie : par membre, la dernière carte achetée au plus tard fin $year
     * (->used, ->status) et le nombre de cartes achetées pendant $year.
     *
     * @return array<int, object{latest: object, countYear: int}>
     */
    public function getSummaryByMember(int $year): array
    {
        $cards = $this->db->table('supporter_cards c')
            ->select('c.*, (SELECT COUNT(*) FROM supporter_card_sessions s WHERE s.card_id = c.id) AS used')
            ->where('c.purchase_date <=', "{$year}-12-31")
            ->orderBy('c.purchase_date', 'ASC')->orderBy('c.id', 'ASC')
            ->get()->getResultObject();

        $summary = [];
        foreach ($cards as $c) {
            $c->used   = (int) $c->used;
            $c->status = self::status($c->used, $c->expiry_date);
            $summary[$c->member_id] ??= (object) ['latest' => null, 'countYear' => 0];
            $summary[$c->member_id]->latest = $c;
            if ((int) substr($c->purchase_date, 0, 4) === $year) {
                $summary[$c->member_id]->countYear++;
            }
        }
        return $summary;
    }

    public static function status(int $used, string $expiryDate, ?string $today = null): string
    {
        if ($used >= self::SESSIONS_PER_CARD) {
            return 'complete';
        }
        return $expiryDate < ($today ?? date('Y-m-d')) ? 'expired' : 'active';
    }

    /**
     * Date d'achat + 6 mois, ramenée au dernier jour du mois si besoin (31/08 → 28/02, pas 03/03).
     */
    public static function defaultExpiry(string $purchaseDate): string
    {
        $d      = new \DateTimeImmutable($purchaseDate);
        $target = $d->modify('first day of this month')->modify('+' . self::VALIDITY_MONTHS . ' months');
        $day    = min((int) $d->format('d'), (int) $target->format('t'));
        return $target->setDate((int) $target->format('Y'), (int) $target->format('m'), $day)->format('Y-m-d');
    }
}
