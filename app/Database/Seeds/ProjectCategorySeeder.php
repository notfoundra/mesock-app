<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('project_categories')->insertBatch([
            ['name' => 'Improvement', 'color' => '#5E72E4'],
            ['name' => 'Development', 'color' => '#2DCE89'],
            ['name' => 'Audit', 'color' => '#FB6340'],
            ['name' => 'Maintenance', 'color' => '#11CDEF'],
            ['name' => 'Bug Fix', 'color' => '#F5365C'],
            ['name' => 'Research', 'color' => '#8392AB'],
        ]);
    }
}
