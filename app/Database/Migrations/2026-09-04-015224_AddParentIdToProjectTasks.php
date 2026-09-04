<?php
// app/Database/Migrations/2026-09-04-100000_AddParentIdToProjectTasks.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddParentIdToProjectTasks extends Migration
{
    public function up()
    {
        $this->forge->addColumn('project_tasks', [
            'parent_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'project_id',
            ],
        ]);

        $this->forge->addForeignKey('parent_id', 'project_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('project_tasks');
    }

    public function down()
    {
        $this->forge->dropForeignKey('project_tasks', 'project_tasks_parent_id_foreign');
        $this->forge->dropColumn('project_tasks', 'parent_id');
    }
}
