<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvents extends Migration
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

'calendar_id' => [
    'type' => 'INT',
    'constraint' => 11,
    'unsigned' => true,
],

'category_id' => [
    'type' => 'INT',
    'constraint' => 11,
    'unsigned' => true,
    'null' => true,
],

            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'start' => [
                'type' => 'DATETIME',
            ],

            'end' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'all_day' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],

'created_by' => [
    'type'       => 'INT',
    'constraint' => 20,
    'unsigned'   => true,
],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('tenant_id');
        $this->forge->addKey('calendar_id');
        $this->forge->addKey('category_id');
        $this->forge->addKey('start');


        $this->forge->addForeignKey(
            'tenant_id',
            'tenants',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'calendar_id',
            'calendars',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'category_id',
            'categories',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'created_by',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );
        

        $this->forge->createTable('events');
    }

    public function down()
    {
        $this->forge->dropTable('events');
    }
}