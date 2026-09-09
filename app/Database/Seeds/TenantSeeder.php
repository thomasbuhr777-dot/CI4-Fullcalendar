<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('tenants')->insert([
            'name'   => 'Muster GmbH',
            'slug'   => 'muster-gmbh',
            'active' => 1,
        ]);
    }
}