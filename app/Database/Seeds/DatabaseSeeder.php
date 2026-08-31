<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(TeamSeeder::class);
        $this->call(AreaSeeder::class);
        $this->call(ProjectCategorySeeder::class);
        $this->call(PrioritySeeder::class);
        $this->call(ProjectStatusSeeder::class);
        $this->call(EvidenceCategorySeeder::class);
    }
}
