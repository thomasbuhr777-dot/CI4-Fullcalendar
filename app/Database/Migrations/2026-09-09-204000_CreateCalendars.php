<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalendars extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'tenant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],

            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '#0d6efd',
            ],

            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('tenant_id');

        $this->forge->addForeignKey(
            'tenant_id',
            'tenants',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('calendars');
    }

    public function down()
    {
        $this->forge->dropTable('calendars');
    }
}