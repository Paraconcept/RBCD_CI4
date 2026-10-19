<?php

namespace App\Models;

use CodeIgniter\Model;

class BarClientModel extends Model
{
    protected $table         = 'bar_clients';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'member_id', 'last_visit_at'];

    public static function normalize(string $name): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', $name)), 'UTF-8');
    }

    /**
     * Suggestions de l'autocomplétion : début du nom, 8 au maximum, membres puis passages récents.
     * La collation utf8mb4_general_ci ignore les accents : « FRANCOIS » trouve « FRANÇOIS ».
     */
    public function search(string $query, int $limit = 8): array
    {
        $query = self::normalize($query);
        if (mb_strlen($query) < 2) {
            return [];
        }

        return $this->select('id, name, member_id, last_visit_at')
            ->like('name', $query, 'after')
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

    /** Un client par membre actif (tous, ou ceux de $memberIds), au nom « NOM PRÉNOM » tenu à jour. Retourne [ajoutés, renommés]. */
    public function syncMembers(?array $memberIds = null): array
    {
        $added = $renamed = 0;
        $builder = $this->db->table('members')->select('id, last_name, first_name')->where('is_active', 1);
        if ($memberIds !== null) {
            $builder->whereIn('id', array_map('intval', $memberIds));
        }
        $members = $builder->get()->getResultObject();
        if (!$members) {
            return [0, 0];
        }
        $byMember = [];
        foreach ($this->where('member_id IS NOT NULL')->findAll() as $c) {
            $byMember[(int) $c->member_id] = $c;
        }

        foreach ($members as $m) {
            $name   = self::normalize($m->last_name . ' ' . $m->first_name);
            $client = $byMember[(int) $m->id] ?? null;
            if (!$client) {
                $this->insert(['name' => $name, 'member_id' => $m->id]);
                $added++;
            } elseif ($client->name !== $name) {
                $this->update($client->id, ['name' => $name]);
                $renamed++;
            }
        }

        return [$added, $renamed];
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
        $this->delete($fromId);
        $this->update($intoId, $data);
        $this->db->transComplete();
    }
}
