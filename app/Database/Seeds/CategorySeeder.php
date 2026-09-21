<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $tenant = $this->db->table('tenants')
            ->where('slug', 'muster-gmbh')
            ->get()
            ->getRowArray();

        if (! $tenant) {
            return;
        }

        $categories = [
            ['Meeting', '#2563EB'],
            ['Urlaub', '#16A34A'],
            ['Privat', '#EA580C'],
        ];

        foreach ($categories as [$name, $color]) {

            $exists = $this->db->table('categories')
                ->where([
                    'tenant_id' => $tenant['id'],
                    'name'      => $name,
                ])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('categories')->insert([
                    'tenant_id'  => $tenant['id'],
                    'name'       => $name,
                    'color'      => $color,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo "Kategorien erstellt.\n";
    }
}