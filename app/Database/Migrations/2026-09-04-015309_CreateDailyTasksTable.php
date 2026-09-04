<?php
// app/Database/Migrations/2026-09-04-100200_CreateDailyTasksTable.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDailyTasksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'template_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'team_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'task_date' => [
                'type' => 'DATE',
            ],
            'is_done' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'done_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'done_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['template_id', 'task_date']); // anti-dobel generate
        $this->forge->addForeignKey('template_id', 'task_templates', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('team_id', 'teams', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('done_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('daily_tasks');
    }

    public function down()
    {
        $this->forge->dropTable('daily_tasks');
    }
}
