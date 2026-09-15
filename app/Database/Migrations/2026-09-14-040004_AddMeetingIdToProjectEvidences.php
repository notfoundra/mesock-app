<?php
// app/Database/Migrations/2026-09-10-090100_AddMeetingIdToProjectEvidences.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMeetingIdToProjectEvidences extends Migration
{
    public function up()
    {
        $this->forge->addColumn('project_evidences', [
            'meeting_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'daily_task_id',
            ],
        ]);

        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('project_evidences');
    }

    public function down()
    {
        $this->forge->dropForeignKey('project_evidences', 'project_evidences_meeting_id_foreign');
        $this->forge->dropColumn('project_evidences', 'meeting_id');
    }
}
