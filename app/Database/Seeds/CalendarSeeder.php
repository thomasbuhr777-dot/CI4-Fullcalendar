<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CalendarSeeder extends Seeder
{
    public function run()
    {
        $tenant = $this->db->table('tenants')
            ->where('slug', 'muster-gmbh')
            ->get()
            ->getRowArray();

        if (! $tenant) {
            echo "Kein Mandant gefunden.\n";
            return;
        }

        $exists = $this->db->table('calendars')
            ->where([
                'tenant_id' => $tenant['id'],
                'name'      => 'Hauptkalender',
            ])
            ->countAllResults();

        if ($exists === 0) {
            $this->db->table('calendars')->insert([
                'tenant_id'  => $tenant['id'],
                'name'       => 'Hauptkalender',
                'color'      => '#2563EB',
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "Kalender erstellt.\n";
    }
}