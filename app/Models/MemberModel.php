<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table      = 'members';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    protected $allowedFields = [
        'last_name', 'first_name', 'gender', 'birth_date',
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
}
