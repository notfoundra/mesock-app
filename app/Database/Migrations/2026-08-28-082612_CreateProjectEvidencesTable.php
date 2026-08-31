<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectEvidencesTable extends Migration
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

            'task_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true
            ],

            'category_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],

            'original_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],

            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],

            'file_size' => [
                'type' => 'INT'
            ],

            'caption' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'uploaded_by' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('task_id', 'project_tasks', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('category_id', 'evidence_categories', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'RESTRICT', 'CASCADE');

        $this->forge->createTable('project_evidences');
    }

    public function down()
    {
        $this->forge->dropTable('project_evidences');
    }
}
