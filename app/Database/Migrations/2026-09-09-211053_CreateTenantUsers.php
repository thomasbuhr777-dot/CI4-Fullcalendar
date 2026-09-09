<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTenantUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'tenant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // Shield verwendet BIGINT UNSIGNED für users.id
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            // Standardmandant eines Benutzers
            'is_default' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        // Ein Benutzer darf einem Mandanten nur einmal zugeordnet sein.
        $this->forge->addUniqueKey(['tenant_id', 'user_id']);

        $this->forge->addKey('tenant_id');
        $this->forge->addKey('user_id');

        $this->forge->addForeignKey(
            'tenant_id',
            'tenants',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('tenant_users');
    }

    public function down()
    {
        $this->forge->dropTable('tenant_users');
    }
}