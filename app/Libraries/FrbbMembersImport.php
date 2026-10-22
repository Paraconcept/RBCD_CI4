<?php

namespace App\Libraries;

use App\Models\BarClientModel;

/**
 * Joueurs affiliés à la FRBB Liège-Luxembourg → clients de la caisse.
 *
 * La licence est la clé : l'affiliation fédérale va de septembre à juin et la carte RBCD de janvier à décembre,
 * si bien qu'un joueur parti dans un autre club figure à la fois sur la liste fédérale et parmi les membres RBCD
 * jusqu'en décembre. Un joueur déjà connu (membre RBCD, actif ou non, ou client à la même licence) n'est donc jamais recréé.
 */
class FrbbMembersImport
{
    public const URL = 'https://www.frbb-liege-lux.be/nos-membres';

    private \CodeIgniter\Database\BaseConnection $db;
    private BarClientModel $clients;

    public function __construct()
    {
        $this->db      = db_connect();
        $this->clients = new BarClientModel();
    }

    /** @return array<int, array{license: ?string, name: string}> */
    public function fetch(): array
    {
        $response = service('curlrequest')->get(self::URL, ['timeout' => 20, 'http_errors' => false]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Le site de la fédération ne répond pas (code ' . $response->getStatusCode() . ').');
        }
        $rows = self::parse((string) $response->getBody());
        if (count($rows) < 50) {
            // Page modifiée ou incomplète : mieux vaut ne rien importer que d'importer à moitié
            throw new \RuntimeException('La liste des membres de la fédération est introuvable ou incomplète (' . count($rows) . ' noms lus).');
        }
        return $rows;
    }

    /** Lit la page « Nos membres » : une carte par joueur, licence (ou « — ») puis « NOM Prénom ». */
    public static function parse(string $html): array
    {
        preg_match_all('#<a[^>]*class="member-item[^"]*"[^>]*>(.*?)</a>#s', $html, $items);
        $rows = [];
        foreach ($items[1] as $item) {
            if (!preg_match('#member-name">([^<]*)<#', $item, $n)) {
                continue;
            }
            $name = BarClientModel::normalize(html_entity_decode($n[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            preg_match('#member-licence[^"]*">([^<]*)<#', $item, $l);
            $license = strtoupper(trim(html_entity_decode($l[1] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($name !== '') {
                $rows[] = ['license' => preg_match('/\d/', $license) ? $license : null, 'name' => $name];
            }
        }
        return $rows;
    }

    /** Licence réduite à ses chiffres : « 4210B » (second club) et « 4210 » sont le même joueur. */
    public static function licenseKey(?string $license): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $license);
        return $digits !== '' ? $digits : null;
    }

    /**
     * Retourne les compteurs : added, members (déjà clients comme membres RBCD), linked (client existant complété),
     * known (déjà importés).
     */
    public function import(array $rows): array
    {
        $count = ['added' => 0, 'members' => 0, 'linked' => 0, 'known' => 0];

        $memberByLicense = $memberByName = [];
        foreach ($this->db->table('members')->select('id, last_name, first_name, frbb_license')->get()->getResultObject() as $m) {
            if ($k = self::licenseKey($m->frbb_license)) {
                $memberByLicense[$k] = (int) $m->id;
            }
            $memberByName[BarClientModel::key($m->last_name . ' ' . $m->first_name)] = (int) $m->id;
        }
        $clientByLicense = $clientByMember = $clientByName = [];
        foreach ($this->clients->findAll() as $c) {
            if ($k = self::licenseKey($c->frbb_license)) {
                $clientByLicense[$k] = $c;
            }
            if ($c->member_id) {
                $clientByMember[(int) $c->member_id] = $c;
            }
            $clientByName[BarClientModel::key($c->name)] ??= $c;
        }

        $this->db->transStart();
        foreach ($rows as $r) {
            $lic = self::licenseKey($r['license']);
            $key = BarClientModel::key($r['name']);

            if ($lic && isset($clientByLicense[$lic])) {
                $count['known']++;
                continue;
            }
            // Membre RBCD qui a son client « membre » (même s'il a changé de club en cours d'année) : rien à créer.
            // Un ancien membre sans client est importé normalement ; s'il redevient membre, la synchronisation reprend ce client.
            $memberId = ($lic ? $memberByLicense[$lic] ?? null : null) ?? $memberByName[$key] ?? null;
            if ($memberId !== null && isset($clientByMember[$memberId])) {
                $count['members']++;
                if ($r['license'] && !$clientByMember[$memberId]->frbb_license) {
                    $this->clients->update($clientByMember[$memberId]->id, ['frbb_license' => $r['license']]);
                }
                continue;
            }
            // Client déjà tapé au bar sous ce nom : on lui ajoute sa licence au lieu de le recréer
            if (isset($clientByName[$key])) {
                if ($r['license'] && !$clientByName[$key]->frbb_license) {
                    $this->clients->update($clientByName[$key]->id, ['frbb_license' => $r['license']]);
                    $count['linked']++;
                } else {
                    $count['known']++;
                }
                continue;
            }
            $id = $this->clients->insert(['name' => $r['name'], 'frbb_license' => $r['license']]);
            $client = $this->clients->find($id);
            $clientByName[$key] = $client;
            if ($lic) {
                $clientByLicense[$lic] = $client;
            }
            $count['added']++;
        }
        $this->db->transComplete();

        return $count;
    }
}
