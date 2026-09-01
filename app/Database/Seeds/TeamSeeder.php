<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('teams')->insertBatch([
            ['name' => 'People', 'code' => 'PEOPLE', 'color' => '#EC4899'],
            ['name' => 'Process', 'code' => 'PROCESS', 'color' => '#14B8A6'],
            ['name' => 'Product', 'code' => 'PRODUCT', 'color' => '#F59E0B'],
            ['name' => 'IE', 'code' => 'IE', 'color' => '#3B82F6'],
            ['name' => 'IE Upstream', 'code' => 'IE_UP', 'color' => '#0EA5E9'],
            ['name' => 'IE Downstream', 'code' => 'IE_DOWN', 'color' => '#2563EB'],
            ['name' => 'Tools Development', 'code' => 'TOOLS_DEV', 'color' => '#8B5CF6'],
        ]);
    }
}
