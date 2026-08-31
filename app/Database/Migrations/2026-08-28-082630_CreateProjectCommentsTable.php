<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectCommentsTable extends Migration
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

            'parent_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true
            ],

            'comment' => [
                'type' => 'TEXT'
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'project_comments', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('project_comments');
    }

    public function down()
    {
        $this->forge->dropTable('project_comments');
    }
}
