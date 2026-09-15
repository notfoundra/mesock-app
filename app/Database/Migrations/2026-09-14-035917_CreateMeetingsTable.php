<?php
// app/Database/Migrations/2026-09-10-090000_CreateMeetingsTable.php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingsTable extends Migration
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
                'null'     => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'meeting_date' => [
                'type' => 'DATE',
            ],
            'problem' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expected_outcome' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('meetings');
    }

    public function down()
    {
        $this->forge->dropTable('meetings');
    }
}
