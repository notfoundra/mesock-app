<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],

            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'unique' => true
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '#5E72E4'
            ],

            'sort_order' => [
                'type' => 'INT',
                'default' => 1
            ],

            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('teams');
    }

    public function down()
    {
        $this->forge->dropTable('teams');
    }
}
