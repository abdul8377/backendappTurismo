<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FavoritosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegúrate de que existan usuarios con ID 1 y 2,
        // productos con ID 1 y 2, servicios con ID 1 y 2, etc.
        DB::table('favoritos')->insert([
            [
                'users_id'      => 3,
                'productos_id'  => 1,
                'servicios_id'  => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'users_id'      => 3,
                'productos_id'  => null,
                'servicios_id'  => 2,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
            [
                'users_id'      => 3,
                'productos_id'  => 2,
                'servicios_id'  => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ],
        ]);
    }
}
