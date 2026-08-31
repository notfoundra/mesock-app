<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EvidenceCategorySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('evidence_categories')->insertBatch([
            ['name' => 'Before', 'color' => '#FB6340'],
            ['name' => 'After', 'color' => '#2DCE89'],
            ['name' => 'Meeting', 'color' => '#5E72E4'],
            ['name' => 'Issue', 'color' => '#F5365C'],
            ['name' => 'Report', 'color' => '#11CDEF'],
            ['name' => 'Document', 'color' => '#8392AB'],
        ]);
    }
}
