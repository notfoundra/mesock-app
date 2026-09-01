<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectTasksTable extends Migration
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

            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'order_no' => [
                'type' => 'INT',
                'default' => 1
            ],

            'is_done' => [
                'type' => 'BOOLEAN',
                'default' => false
            ],

            'done_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],

            'done_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('done_by', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('project_tasks');
    }

    public function down()
    {
        $this->forge->dropTable('project_tasks');
    }
}
