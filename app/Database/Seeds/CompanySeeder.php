<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'code' => 'BBI',
                'name' => 'Bosowa Bandar Indonesia',
                'description' => 'PT Bosowa Bandar Indonesia',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'BBA',
                'name' => 'Bosowa Bandar Agensi',
                'description' => 'PT Bosowa Bandar Agensi',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'JAPELIN',
                'name' => 'Jasa Pelabuhan Indonesia',
                'description' => 'PT Jasa Pelabuhan Indonesia',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('companies')->insertBatch($data);
    }
}
