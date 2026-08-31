<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('project_statuses')->insertBatch([
            ['name' => 'Pending', 'slug' => 'pending', 'color' => '#8392AB', 'sort_order' => 1],
            ['name' => 'To Do', 'slug' => 'todo', 'color' => '#5E72E4', 'sort_order' => 2],
            ['name' => 'In Progress', 'slug' => 'in-progress', 'color' => '#FB6340', 'sort_order' => 3],
            ['name' => 'Review', 'slug' => 'review', 'color' => '#11CDEF', 'sort_order' => 4],
            ['name' => 'Done', 'slug' => 'done', 'color' => '#2DCE89', 'sort_order' => 5],
            ['name' => 'Overdue', 'slug' => 'overdue', 'color' => '#F5365C', 'sort_order' => 6],
        ]);
    }
}
