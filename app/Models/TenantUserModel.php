<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantUserModel extends Model
{
    protected $table      = 'tenant_users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'tenant_id',
        'user_id',
        'role'
    ];

    protected $useTimestamps = true;

    public function tenantOfUser(int $userId)
    {
        return $this->select('tenant_users.*, tenants.name, tenants.slug')
            ->join('tenants', 'tenants.id = tenant_users.tenant_id')
            ->where('user_id', $userId)
            ->first();
    }
}