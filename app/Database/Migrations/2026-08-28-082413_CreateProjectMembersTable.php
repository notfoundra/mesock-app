<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectMembersTable extends Migration
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

            'role' => [
                'type' => 'ENUM',
                'constraint' => ['PIC', 'Reviewer']
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['project_id', 'user_id']);

        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('project_members');
    }

    public function down()
    {
        $this->forge->dropTable('project_members');
    }
}
