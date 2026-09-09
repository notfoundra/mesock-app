<?php
// app/Database/Migrations/2026-09-09-150100_AddDescriptionToDailyTasks.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToDailyTasks extends Migration
{
    public function up()
    {
        $this->forge->addColumn('daily_tasks', [
            'description' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'title',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('daily_tasks', 'description');
    }
}
