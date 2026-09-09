<?php
// app/Database/Migrations/2026-09-09-150000_AddDailyTaskSupportToEvidenceAndComments.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDailyTaskSupportToEvidenceAndComments extends Migration
{
    public function up()
    {
        // project_evidences
        $this->forge->modifyColumn('project_evidences', [
            'project_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        $this->forge->addColumn('project_evidences', [
            'daily_task_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'task_id',
            ],
        ]);

        $this->forge->addForeignKey('daily_task_id', 'daily_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('project_evidences');

        // project_comments
        $this->forge->modifyColumn('project_comments', [
            'project_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ]);

        $this->forge->addColumn('project_comments', [
            'daily_task_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'task_id',
            ],
        ]);

        $this->forge->addForeignKey('daily_task_id', 'daily_tasks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('project_comments');
    }

    public function down()
    {
        $this->forge->dropForeignKey('project_evidences', 'project_evidences_daily_task_id_foreign');
        $this->forge->dropColumn('project_evidences', 'daily_task_id');

        $this->forge->dropForeignKey('project_comments', 'project_comments_daily_task_id_foreign');
        $this->forge->dropColumn('project_comments', 'daily_task_id');
    }
}
