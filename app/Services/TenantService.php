<?php

namespace App\Services;

use App\Models\TenantUserModel;

class TenantService
{
    private ?array $tenant = null;

    public function current(): ?array
    {
        if ($this->tenant !== null) {
            return $this->tenant;
        }

        if (! auth()->loggedIn()) {
            return null;
        }

        $this->tenant = (new TenantUserModel())
            ->defaultTenantForUser(auth()->id());

        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->current()['tenant_id'] ?? null;
    }

    public function name(): ?string
    {
        return $this->current()['name'] ?? null;
    }

    public function slug(): ?string
    {
        return $this->current()['slug'] ?? null;
    }

    public function exists(): bool
    {
        return $this->current() !== null;
    }
}