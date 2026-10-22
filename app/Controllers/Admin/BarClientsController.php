<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarClientModel;

/**
 * Clients de la caisse : ajoutés à la volée au bar, et un par membre actif (synchronisés automatiquement).
 */
class BarClientsController extends BaseController
{
    private BarClientModel $clients;

    public function __construct()
    {
        $this->clients = new BarClientModel();
    }

    public function index(): string
    {
        return view('admin/bar_clients/index', [
            'title'       => 'Caisse — Clients',
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Clients']],
            'clients'     => $this->clients->getForAdmin(),
            'duplicates'  => $this->clients->probableDuplicates(),
        ]);
    }

    public function update(int $id)
    {
        $client = $this->clients->find($id);
        if (!$client) {
            return redirect()->back()->with('error', 'Client introuvable.');
        }
        if ($client->member_id) {
            return redirect()->back()->with('error', 'Le nom d\'un membre se modifie sur sa fiche membre : la caisse le reprend automatiquement.');
        }
        $name = BarClientModel::normalize((string) $this->request->getPost('name'));
        if (mb_strlen($name) < 2) {
            return redirect()->back()->with('error', 'Le nom doit compter au moins 2 lettres.');
        }
        $this->clients->update($id, ['name' => $name]);

        return redirect()->to(base_url('admin/caisse/clients'))->with('success', 'Client renommé : ' . esc($name) . '.');
    }

    public function merge(int $id)
    {
        $into = (int) $this->request->getPost('into_id');
        $from = $this->clients->find($id);
        $to   = $this->clients->find($into);
        if (!$from || !$to || $id === $into) {
            return redirect()->back()->with('error', 'Choisissez le client à conserver.');
        }
        if ($from->member_id && $to->member_id) {
            return redirect()->back()->with('error', 'Deux membres différents ne peuvent pas être fusionnés.');
        }
        $this->clients->merge($id, $into);

        return redirect()->to(base_url('admin/caisse/clients'))->with('success', '« ' . esc($from->name) . ' » fusionné dans « ' . esc($to->name) . ' » : ses tickets lui sont rattachés.');
    }

    public function notDuplicate()
    {
        $a = $this->clients->find((int) $this->request->getPost('a'));
        $b = $this->clients->find((int) $this->request->getPost('b'));
        if (!$a || !$b) {
            return redirect()->back()->with('error', 'Client introuvable.');
        }
        BarClientModel::ignorePair((int) $a->id, (int) $b->id);

        return redirect()->to(base_url('admin/caisse/clients'))->with('success', '« ' . esc($a->name) . ' » et « ' . esc($b->name) . ' » ne seront plus proposés comme doublons.');
    }

    public function delete(int $id)
    {
        $client = $this->clients->find($id);
        if (!$client) {
            return redirect()->back();
        }
        // Les tickets gardent leur client : l'historique ne doit jamais perdre un nom
        if (db_connect()->table('bar_tickets')->where('client_id', $id)->countAllResults()) {
            return redirect()->back()->with('error', '« ' . esc($client->name) . ' » a des tickets : il ne peut pas être supprimé. Fusionnez-le plutôt dans le bon client.');
        }
        $this->clients->delete($id);

        return redirect()->to(base_url('admin/caisse/clients'))->with('success', '« ' . esc($client->name) . ' » est retiré de la liste des clients.'
            . ($client->member_id ? ' Il reviendra si sa fiche membre est réactivée, ou avec « Synchroniser avec les membres ».' : ''));
    }

    public function importFrbb()
    {
        $import = new \App\Libraries\FrbbMembersImport();
        try {
            $rows = $import->fetch();
        } catch (\Throwable $e) {
            return redirect()->to(base_url('admin/caisse/clients'))->with('error', 'Import impossible : ' . esc($e->getMessage()));
        }
        $c = $import->import($rows);

        return redirect()->to(base_url('admin/caisse/clients'))->with('success',
            count($rows) . ' joueurs lus sur le site de la FRBB Liège-Luxembourg : <b>' . $c['added'] . ' ajouté(s)</b>, '
            . $c['members'] . ' déjà clients comme membres du RBCD, ' . $c['linked'] . ' client(s) existant(s) complété(s) avec leur licence, '
            . $c['known'] . ' déjà importé(s).');
    }

    public function sync()
    {
        [$added, $renamed] = $this->clients->syncMembers();

        return redirect()->to(base_url('admin/caisse/clients'))
            ->with('success', $added || $renamed
                ? 'Synchronisation faite : ' . $added . ' membre(s) ajouté(s), ' . $renamed . ' nom(s) mis à jour.'
                : 'Tous les membres actifs sont déjà clients de la caisse.');
    }
}
