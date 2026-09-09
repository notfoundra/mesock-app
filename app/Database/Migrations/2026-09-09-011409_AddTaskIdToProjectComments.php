<?php
// app/Database/Migrations/2026-09-09-140000_AddTaskIdToProjectComments.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskIdToProjectComments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('project_comments', [
            'task_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'project_id',
            ],
        ]);

        $this->forge->addForeignKey('task_id', 'project_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('project_comments');
    }

    public function down()
    {
        $this->forge->dropForeignKey('project_comments', 'project_comments_task_id_foreign');
        $this->forge->dropColumn('project_comments', 'task_id');
    }
}
