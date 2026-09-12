<?php
// app/Database/Migrations/2026-09-09-170000_AddGoalsAndProblemsToProjects.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGoalsAndProblemsToProjects extends Migration
{
    public function up()
    {
        $this->forge->addColumn('projects', [
            'goals' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'description',
            ],
            'problems' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'goals',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('projects', ['goals', 'problems']);
    }
}
