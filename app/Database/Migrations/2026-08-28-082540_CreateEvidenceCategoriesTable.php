<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenceCategoriesTable extends Migration
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
                'constraint' => 50
            ],

            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('evidence_categories');
    }

    public function down()
    {
        $this->forge->dropTable('evidence_categories');
    }
}
