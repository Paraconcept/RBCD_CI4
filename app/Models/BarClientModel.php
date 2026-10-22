<?php

namespace App\Models;

use CodeIgniter\Model;

class BarClientModel extends Model
{
    protected $table         = 'bar_clients';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'member_id', 'frbb_license', 'last_visit_at'];

    public static function normalize(string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $name)), 'UTF-8');
    }

    /**
     * Suggestions de l'autocomplétion : début du nom de famille ou du prénom (« NOM PRÉNOM »), 8 au maximum,
     * ceux qui commencent par la saisie d'abord.
     * La collation utf8mb4_general_ci ignore les accents : « FRANCOIS » trouve « FRANÇOIS ».
     */
    public function search(string $query, int $limit = 8): array
    {
        $query = self::normalize($query);
        if (mb_strlen($query) < 2) {
            return [];
        }
        $starts = $this->db->escape($this->db->escapeLikeString($query) . '%');

        return $this->select('id, name, member_id, last_visit_at')
            ->groupStart()
                ->like('name', $query, 'after')
                ->orLike('name', ' ' . $query, 'both')
            ->groupEnd()
            ->orderBy("name LIKE {$starts} ESCAPE '!' DESC", '', false)
            ->orderBy('name')
            ->orderBy('last_visit_at', 'DESC')
            ->findAll($limit);
    }

    /** Client existant au nom exact, ou nouveau client : les suggestions passent toujours avant la création. */
    public function findOrCreate(string $name): object
    {
        $name   = self::normalize($name);
        $exists = $this->where('name', $name)->orderBy('member_id IS NULL', '', false)->first();
        if ($exists) {
            return $exists;
        }
        $id = $this->insert(['name' => $name]);
        return $this->find($id);
    }

    /**
     * Membres actifs (tous, ou ceux de $memberIds) : nom « NOM PRÉNOM » tenu à jour, et client créé s'il manque.
     * Un joueur déjà client sans être membre (importé de la fédération, ou tapé au bar) est repris plutôt que recréé :
     * même licence d'abord, sinon même nom.
     * Avec $addOnly, seuls ces membres-là sont ajoutés (les autres membres retirés de la caisse n'y reviennent pas).
     * Les membres devenus inactifs sont d'abord détachés de leur client (voir detachInactive).
     * Retourne [ajoutés, renommés, détachés].
     */
    public function syncMembers(?array $memberIds = null, ?array $addOnly = null): array
    {
        $detached = $this->detachInactive($memberIds);
        $addOnly = $addOnly !== null ? array_flip(array_map('intval', $addOnly)) : null;
        $added = $renamed = 0;
        $builder = $this->db->table('members')->select('id, last_name, first_name, frbb_license')->where('is_active', 1);
        if ($memberIds !== null) {
            $builder->whereIn('id', array_map('intval', $memberIds));
        }
        $members = $builder->get()->getResultObject();
        if (!$members) {
            return [0, 0, $detached];
        }
        $byMember = $freeByLicense = $freeByName = [];
        foreach ($this->findAll() as $c) {
            if ($c->member_id) {
                $byMember[(int) $c->member_id] = $c;
                continue;
            }
            if ($k = \App\Libraries\FrbbMembersImport::licenseKey($c->frbb_license)) {
                $freeByLicense[$k] = $c;
            }
            $freeByName[$c->name] ??= $c;
        }

        foreach ($members as $m) {
            $name   = self::normalize($m->last_name . ' ' . $m->first_name);
            $client = $byMember[(int) $m->id] ?? null;
            if (!$client) {
                if ($addOnly !== null && !isset($addOnly[(int) $m->id])) {
                    continue;
                }
                $free = $freeByLicense[\App\Libraries\FrbbMembersImport::licenseKey($m->frbb_license) ?? ''] ?? $freeByName[$name] ?? null;
                if ($free) {
                    $this->update($free->id, ['name' => $name, 'member_id' => $m->id]);
                    unset($freeByName[$free->name]);
                } else {
                    $this->insert(['name' => $name, 'member_id' => $m->id]);
                }
                $added++;
            } elseif ($client->name !== $name) {
                $this->update($client->id, ['name' => $name]);
                $renamed++;
            }
        }

        return [$added, $renamed, $detached];
    }

    /**
     * Membre devenu inactif (carte RBCD non renouvelée, joueur parti dans un autre club) : son client reste,
     * avec son nom, sa licence et ses tickets, mais n'est plus « membre » (nom modifiable, comme un joueur de la fédération).
     * S'il redevient membre, syncMembers reprend ce client grâce à sa licence, ou à défaut à son nom.
     * Retourne le nombre de clients détachés.
     */
    private function detachInactive(?array $memberIds = null): int
    {
        $builder = $this->db->table('bar_clients c')
            ->select('c.id, c.frbb_license AS client_license, m.frbb_license AS member_license')
            ->join('members m', 'm.id = c.member_id')
            ->where('m.is_active', 0);
        if ($memberIds !== null) {
            $builder->whereIn('m.id', array_map('intval', $memberIds));
        }
        $rows = $builder->get()->getResultObject();
        foreach ($rows as $r) {
            $license = $r->client_license ?: (trim((string) $r->member_license) ?: null);
            $this->update($r->id, ['member_id' => null, 'frbb_license' => $license]);
        }
        return count($rows);
    }

    // Prénoms courants, en plus de ceux des membres : sert à repérer un nom tapé « PRÉNOM NOM »
    private const COMMON_FIRST_NAMES = [
        'ALAIN', 'ALBERT', 'ALEXANDRE', 'ALFRED', 'ALINE', 'ANDRE', 'ANDREE', 'ANNE', 'ANNIE', 'ANTOINE', 'ARNAUD', 'ARTHUR',
        'BENOIT', 'BERNARD', 'BRIGITTE', 'BRUNO', 'CAROLE', 'CATHERINE', 'CHANTAL', 'CHARLES', 'CHRISTIAN', 'CHRISTINE', 'CHRISTOPHE',
        'CLAUDE', 'CLAUDINE', 'COLETTE', 'DANIEL', 'DANIELLE', 'DAVID', 'DENIS', 'DIDIER', 'DOMINIQUE', 'EDOUARD', 'ELISE', 'EMILE',
        'ERIC', 'ETIENNE', 'FABIENNE', 'FERNAND', 'FRANCINE', 'FRANCIS', 'FRANCOIS', 'FRANCOISE', 'FREDERIC', 'GABRIEL', 'GEORGES',
        'GERARD', 'GHISLAIN', 'GILBERT', 'GILLES', 'GUY', 'HENRI', 'HUBERT', 'ISABELLE', 'JACQUELINE', 'JACQUES', 'JEAN', 'JEANNE',
        'JEROME', 'JOSE', 'JOSEPH', 'JOSETTE', 'JULES', 'JULIEN', 'LAURENT', 'LEON', 'LOUIS', 'LUC', 'LUCIEN', 'MARC', 'MARCEL',
        'MARIE', 'MARTINE', 'MAURICE', 'MICHEL', 'MICHELE', 'MONIQUE', 'NADINE', 'NATHALIE', 'NICOLAS', 'NICOLE', 'OLIVIER',
        'PASCAL', 'PATRICIA', 'PATRICK', 'PAUL', 'PHILIPPE', 'PIERRE', 'RAYMOND', 'RENE', 'ROBERT', 'ROGER', 'SERGE', 'SOPHIE',
        'STEPHANE', 'SYLVIE', 'THERESE', 'THIERRY', 'THOMAS', 'VINCENT', 'WILLY', 'YVES', 'YVETTE', 'YVONNE',
    ];

    /** Clé de comparaison : majuscules sans accents ni ponctuation. */
    public static function key(string $name): string
    {
        helper('text');
        $plain = convert_accented_characters(self::normalize($name));
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^A-Z ]+/', ' ', strtoupper($plain))));
    }

    /**
     * Raison pour laquelle deux noms désignent sans doute la même personne, ou null.
     * « inverse » : mêmes mots dans un autre ordre ; « proche » : une ou deux lettres de différence ;
     * « abrege » : un prénom réduit à une ou deux lettres (« DUPONT J » / « DUPONT JEAN ») ;
     * « identique » : même nom aux accents, tirets ou espaces près.
     * Un nom d'un seul mot n'est jamais comparé : « JEAN » ressemblerait à tous les Jean.
     */
    public static function resemblance(string $a, string $b): ?string
    {
        $a = self::key($a);
        $b = self::key($b);
        $wa = explode(' ', $a);
        $wb = explode(' ', $b);
        if ($a === '' || $b === '' || count($wa) < 2 || count($wb) < 2) {
            return null;
        }
        if ($a === $b) {
            return 'identique';
        }
        $sa = $wa; sort($sa);
        $sb = $wb; sort($sb);
        if ($sa === $sb) {
            return 'inverse';
        }
        // Une seule lettre d'écart : au-delà, « DANIEL » / « DANIELLE » seraient confondus
        $tolerance = min(strlen($a), strlen($b)) >= 16 ? 2 : 1;
        if (levenshtein($a, $b) <= $tolerance || levenshtein(implode(' ', $sa), implode(' ', $sb)) <= $tolerance) {
            return 'proche';
        }
        if (count($wa) === count($wb)) {
            foreach ([$wb, array_reverse($wb)] as $other) {
                $equal = 0;
                $ok    = true;
                foreach ($wa as $i => $w) {
                    $o = $other[$i];
                    if ($w === $o) {
                        $equal++;
                    } elseif (min(strlen($w), strlen($o)) > 2 || (!str_starts_with($w, $o) && !str_starts_with($o, $w))) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok && $equal >= 1) {
                    return 'abrege';
                }
            }
        }
        return null;
    }

    /** Clients qui ressemblent à $name (pas le nom exact : la caisse reprend déjà ce client), 4 au maximum. */
    public function suspects(string $name, int $limit = 4): array
    {
        $exact = self::normalize($name);
        $found = [];
        foreach ($this->select('id, name, member_id, last_visit_at')->findAll() as $c) {
            $reason = self::resemblance($name, $c->name);
            if ($reason !== null && $c->name !== $exact) {
                $c->reason = $reason;
                $found[]   = $c;
            }
        }
        usort($found, static fn ($x, $y) => [$y->member_id !== null, (string) $y->last_visit_at] <=> [$x->member_id !== null, (string) $x->last_visit_at]);
        return array_slice($found, 0, $limit);
    }

    /**
     * « JEAN DUPONT » → « DUPONT JEAN » : si le nom commence par un ou plusieurs prénoms connus suivis d'un mot qui n'en est pas un.
     * Si tous les mots peuvent être des prénoms (« PIERRE MARTIN »), voir otherOrder().
     */
    public function nameFirst(string $name): ?string
    {
        $words = explode(' ', self::normalize($name));
        $keys  = explode(' ', self::key($name));
        if (count($words) < 2 || count($words) !== count($keys)) {
            return null;
        }
        $known = $this->firstNames();
        $k = 0;
        while ($k < count($keys) && isset($known[$keys[$k]])) {
            $k++;
        }
        if ($k === 0 || $k === count($keys)) {
            return null;
        }
        return implode(' ', array_merge(array_slice($words, $k), array_slice($words, 0, $k)));
    }

    private ?array $firstNames = null;

    /**
     * « PIERRE MARTIN » : tous les mots peuvent être des prénoms, l'ordre est impossible à deviner.
     * Retourne l'autre ordre possible (dernier mot en tête), pour que le barman choisisse.
     */
    public function otherOrder(string $name): ?string
    {
        $words = explode(' ', self::normalize($name));
        $keys  = explode(' ', self::key($name));
        if (count($words) < 2 || count($words) !== count($keys)) {
            return null;
        }
        $known = $this->firstNames();
        foreach ($keys as $k) {
            if (!isset($known[$k])) {
                return null;
            }
        }
        return implode(' ', array_merge([array_pop($words)], $words));
    }

    /** @return array<string, true> prénoms (clés sans accents) des membres et prénoms courants */
    private function firstNames(): array
    {
        if ($this->firstNames !== null) {
            return $this->firstNames;
        }
        $names = array_fill_keys(self::COMMON_FIRST_NAMES, true);
        foreach ($this->db->table('members')->select('first_name')->distinct()->get()->getResultObject() as $m) {
            foreach (explode(' ', self::key((string) $m->first_name)) as $w) {
                if (strlen($w) >= 2) {
                    $names[$w] = true;
                }
            }
        }
        return $this->firstNames = $names;
    }

    /**
     * Paires de clients qui sont sans doute la même personne, pour Admin › Clients.
     * Deux membres ne sont jamais proposés (homonymes réels, et ils ne peuvent pas être fusionnés), ni deux licences différentes,
     * ni les paires marquées « pas un doublon ». Le client proposé à conserver est le membre, sinon le joueur de la fédération
     * (nom officiel), sinon celui qui a le plus de tickets, sinon celui écrit « NOM PRÉNOM », sinon le nom le plus complet.
     */
    public function probableDuplicates(): array
    {
        $ignored = array_flip(self::ignoredPairs());
        $clients = $this->getForAdmin();
        $pairs   = [];
        foreach ($clients as $i => $a) {
            for ($j = $i + 1; $j < count($clients); $j++) {
                $b = $clients[$j];
                // Deux licences différentes : deux joueurs différents, même si les noms se ressemblent
                $licA = \App\Libraries\FrbbMembersImport::licenseKey($a->frbb_license);
                $licB = \App\Libraries\FrbbMembersImport::licenseKey($b->frbb_license);
                if (($a->member_id && $b->member_id) || ($licA && $licB && $licA !== $licB) || isset($ignored[self::pairKey((int) $a->id, (int) $b->id)])) {
                    continue;
                }
                $reason = self::resemblance($a->name, $b->name);
                if ($reason === null) {
                    continue;
                }
                $keepA = $this->keepScore($a) >= $this->keepScore($b);
                $pairs[] = (object) ['keep' => $keepA ? $a : $b, 'drop' => $keepA ? $b : $a, 'reason' => $reason];
            }
        }
        return $pairs;
    }

    private function keepScore(object $c): array
    {
        return [$c->member_id !== null, $c->frbb_license !== null, (int) $c->ticket_count, $this->nameFirst($c->name) === null, strlen($c->name)];
    }

    public static function pairKey(int $a, int $b): string
    {
        return min($a, $b) . '-' . max($a, $b);
    }

    /** @return string[] paires « id-id » marquées « pas un doublon » dans l'admin */
    public static function ignoredPairs(): array
    {
        $list = json_decode((string) (new SiteSettingModel())->getSetting('bar_clients_not_duplicates', '[]'), true);
        return is_array($list) ? $list : [];
    }

    public static function ignorePair(int $a, int $b): void
    {
        $list   = self::ignoredPairs();
        $list[] = self::pairKey($a, $b);
        (new SiteSettingModel())->setSetting('bar_clients_not_duplicates', json_encode(array_values(array_unique($list))));
    }

    /** Liste admin avec le nombre de tickets et le membre lié. */
    public function getForAdmin(): array
    {
        return $this->db->table('bar_clients c')
            ->select("c.*, CONCAT(m.last_name, ' ', m.first_name) AS member_name, COUNT(t.id) AS ticket_count")
            ->join('members m', 'm.id = c.member_id', 'left')
            ->join('bar_tickets t', 't.client_id = c.id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.name')
            ->get()->getResultObject();
    }

    /** Fusionne un doublon dans le client conservé : ses tickets passent sur ce dernier. */
    public function merge(int $fromId, int $intoId): void
    {
        $from = $this->find($fromId);
        $into = $this->find($intoId);
        if (!$from || !$into || $fromId === $intoId) {
            return;
        }

        $this->db->transStart();
        $this->db->table('bar_tickets')->where('client_id', $fromId)->update(['client_id' => $intoId]);
        $data = ['last_visit_at' => max((string) $from->last_visit_at, (string) $into->last_visit_at) ?: null];
        // Le lien au membre est unique : on supprime le doublon avant de reporter son membre sur le client conservé
        if (!$into->member_id && $from->member_id) {
            $data['member_id'] = $from->member_id;
        }
        if (!$into->frbb_license && $from->frbb_license) {
            $data['frbb_license'] = $from->frbb_license;
        }
        $this->delete($fromId);
        $this->update($intoId, $data);
        $this->db->transComplete();
    }
}
