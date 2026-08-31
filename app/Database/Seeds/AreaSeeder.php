<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreaSeeder extends Seeder
{
     $this->db->table('area')->insertBatch([
          [
            ['name'=>'KK1','code'=>'KK1','group_area'=>'Knitting'],
            ['name'=>'KK2','code'=>'KK2','group_area'=>'Knitting'],
            ['name'=>'KK11','code'=>'KK11','group_area'=>'Knitting'],
            ['name'=>'Packing','code'=>'PACKING','group_area'=>'Finishing'],
            ['name'=>'Gudang Benang','code'=>'GBN','group_area'=>'Warehouse'],
            ['name'=>'Supermarket Material','code'=>'SUPERMARKET','group_area'=>'Warehouse'],
            ['name'=>'ME Office','code'=>'ME_OFFICE','group_area'=>'Office'],
        ]
        ]);
}
