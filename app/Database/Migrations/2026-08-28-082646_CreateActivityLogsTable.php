<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'project_id' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],

            'user_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'activity' => [
                'type' => 'VARCHAR',
                'constraint' => 150
            ],

            'old_value' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'new_value' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('activity_logs');
    }
}
