<?php
// app/Database/Migrations/2026-09-09-160000_CreateProjectMilestonesTable.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectMilestonesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'target_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_done' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('project_milestones');
    }

    public function down()
    {
        $this->forge->dropTable('project_milestones');
    }
}
