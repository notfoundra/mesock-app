<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'project_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'unique' => true
            ],

            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'team_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'area_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'category_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'priority_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'status_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'progress' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0
            ],

            'start_date DATE',

            'due_date DATE',

            'completed_at DATETIME NULL',

            'created_by' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'deleted_at DATETIME NULL'
        ]);

        $this->forge->addKey('id', true);

        foreach (
            [
                'team_id',
                'area_id',
                'category_id',
                'priority_id',
                'status_id',
                'created_by'
            ] as $key
        ) {
            $this->forge->addKey($key);
        }

        $this->forge->addForeignKey('team_id', 'teams', 'id');
        $this->forge->addForeignKey('area_id', 'areas', 'id');
        $this->forge->addForeignKey('category_id', 'project_categories', 'id');
        $this->forge->addForeignKey('priority_id', 'priorities', 'id');
        $this->forge->addForeignKey('status_id', 'project_statuses', 'id');
        $this->forge->addForeignKey('created_by', 'users', 'id');

        $this->forge->createTable('projects');
    }

    public function down()
    {
        $this->forge->dropTable('projects');
    }
}
