<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrioritiesTable extends Migration
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
                'constraint' => 50,
            ],
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'default' => 1,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('priorities');
    }

    public function down()
    {
        $this->forge->dropTable('priorities');
    }
}
