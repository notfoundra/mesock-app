<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenceCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => '#5E72E4',
            ],
            'icon' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('evidence_categories');
    }

    public function down()
    {
        $this->forge->dropTable('evidence_categories');
    }
}
