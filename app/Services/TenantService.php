<?php

namespace App\Services;

use App\Models\TenantUserModel;

class TenantService
{
    public function current(): ?array
    {
        if (! auth()->loggedIn()) {
            return null;
        }

        $userId = auth()->id();

        return (new TenantUserModel())
            ->tenantOfUser($userId);
    }

    public function id(): ?int
    {
        return session('tenant_id');
    }
}