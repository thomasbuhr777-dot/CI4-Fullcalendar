<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EventSeeder extends Seeder
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

        $calendar = $this->db->table('calendars')
            ->where('tenant_id', $tenant['id'])
            ->get()
            ->getRowArray();

        $category = $this->db->table('categories')
            ->where([
                'tenant_id' => $tenant['id'],
                'name'      => 'Meeting',
            ])
            ->get()
            ->getRowArray();

        $user = $this->db->table('users')
            ->orderBy('id')
            ->get()
            ->getRowArray();

        if (! $calendar || ! $category || ! $user) {
            return;
        }

        $this->db->table('events')->insert([
            'tenant_id'   => $tenant['id'],
            'calendar_id' => $calendar['id'],
            'category_id' => $category['id'],

            'title'       => 'CI4 + FullCalendar Projekt',

            'description' => 'Erster Termin aus MySQL.',

            'start'       => '2026-09-22 10:00:00',
            'end'         => '2026-09-22 12:00:00',

            'all_day'     => 0,

            'created_by'  => $user['id'],

            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        echo "Testtermin erstellt.\n";
    }
}