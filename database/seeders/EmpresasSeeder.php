<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empresas')->insert([
            ['id' => 1, 'nombre' => 'BPT',      'slug' => 'bpt',      'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Dunosusa',  'slug' => 'dunosusa', 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
