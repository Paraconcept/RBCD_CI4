<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table      = 'members';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'last_name', 'first_name', 'gender', 'birth_date', 'reg_nat',
        'address', 'postal_code', 'city', 'phone', 'mobile', 'email',
        'photo', 'is_federated', 'frbb_license',
        'is_junior', 'is_supporter', 'is_school',
        'is_active',
        'show_birth_date', 'show_address', 'show_phone', 'show_mobile', 'show_email', 'show_photo',
        'show_birth_date_members', 'show_address_members', 'show_phone_members',
        'show_mobile_members', 'show_email_members', 'show_photo_members',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Un membre devient client de la caisse du bar à sa création ou à sa réactivation ; son nom y est tenu à jour.
    // Un membre retiré de la liste des clients n'y revient donc pas à chaque enregistrement de sa fiche.
    protected $afterInsert  = ['addBarClient'];
    protected $beforeUpdate = ['rememberActive'];
    protected $afterUpdate  = ['syncBarClient'];

    private array $wasActive = [];

    protected function addBarClient(array $data): array
    {
        if (!empty($data['id'])) {
            (new BarClientModel())->syncMembers((array) $data['id']);
        }
        return $data;
    }

    protected function rememberActive(array $data): array
    {
        $ids = (array) ($data['id'] ?? []);
        $this->wasActive = $ids
            ? array_column($this->db->table('members')->select('id, is_active')->whereIn('id', $ids)->get()->getResultArray(), 'is_active', 'id')
            : [];
        return $data;
    }

    protected function syncBarClient(array $data): array
    {
        $ids = (array) ($data['id'] ?? []);
        if ($ids) {
            $reactivated = !empty($data['data']['is_active'])
                ? array_values(array_filter($ids, fn ($id) => empty($this->wasActive[$id])))
                : [];
            (new BarClientModel())->syncMembers($ids, $reactivated);
        }
        return $data;
    }
}
