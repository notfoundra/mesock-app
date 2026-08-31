<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('priorities')->insertBatch([
            ['name' => 'Critical', 'color' => '#F5365C', 'sort_order' => 1],
            ['name' => 'High', 'color' => '#FB6340', 'sort_order' => 2],
            ['name' => 'Medium', 'color' => '#11CDEF', 'sort_order' => 3],
            ['name' => 'Low', 'color' => '#2DCE89', 'sort_order' => 4],
        ]);
    }
}
