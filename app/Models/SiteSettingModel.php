<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table      = 'site_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = ['key', 'value'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $row = $this->where('key', $key)->first();
        if ($row) {
            $this->update($row->id, ['value' => $value]);
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }
    }
}
