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
        'is_default',
    ];

    protected $useTimestamps = true;

    /**
     * Liefert den Standard-Mandanten eines Benutzers.
     */
    public function defaultTenantForUser(int $userId): ?array
    {
        return $this->select('tenant_users.*, tenants.name, tenants.slug')
            ->join('tenants', 'tenants.id = tenant_users.tenant_id')
            ->where('tenant_users.user_id', $userId)
            ->where('tenant_users.is_default', 1)
            ->first();
    }
}