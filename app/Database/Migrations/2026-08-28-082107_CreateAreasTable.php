<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAreasTable extends Migration
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

            'group_area' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('areas');
    }

    public function down()
    {
        $this->forge->dropTable('areas');
    }
}
