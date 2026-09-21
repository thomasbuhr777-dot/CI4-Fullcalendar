<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $this->call('TenantSeeder');
        $this->call('CalendarSeeder');
        $this->call('CategorySeeder');
        $this->call('EventSeeder');
    }
}