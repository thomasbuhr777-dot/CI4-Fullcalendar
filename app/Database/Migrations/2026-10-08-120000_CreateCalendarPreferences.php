<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalendarPreferences extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'tenant_id' => ['type' => 'INT', 'unsigned' => true],
            'view' => ['type' => 'VARCHAR', 'constraint' => 20],
            'state' => ['type' => 'VARCHAR', 'constraint' => 2],
            'years' => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'tenant_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tenant_id', 'tenants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('calendar_preferences');
    }

    public function down()
    {
        $this->forge->dropTable('calendar_preferences');
    }
}
