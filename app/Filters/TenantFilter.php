<?php

namespace App\Filters;

use App\Models\TenantUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Nur für eingeloggte Benutzer.
        if (! auth()->loggedIn()) {
            return;
        }

        // Bereits in der Session? Dann nichts tun.
        if (session()->has('tenant_id')) {
            return;
        }

        $tenant = (new TenantUserModel())->tenantOfUser(auth()->id());

        if (! $tenant) {
            // Benutzer gehört keinem Mandanten an.
            return redirect()->to('/logout');
        }

        session()->set([
            'tenant_id'   => $tenant['tenant_id'],
            'tenant_name' => $tenant['name'],
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nichts
    }
}