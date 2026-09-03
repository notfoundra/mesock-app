<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'user_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],

            'team_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,   // tambahin ini
            ],

            'employee_id' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true
            ],

            'fullname' => [
                'type' => 'VARCHAR',
                'constraint' => 150
            ],

            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],

            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'default.png'
            ],

            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],

            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true
            ],

            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');

        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'team_id',
            'teams',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        $this->forge->createTable('user_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('user_profiles');
    }
}
