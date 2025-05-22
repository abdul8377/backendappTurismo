<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposDeNegocioSeeder extends Seeder
{
    public function run()
    {
        DB::table('tipos_de_negocio')->insert([
            [
                'nombre' => 'Restaurante',
                'descripcion' => 'Establecimiento dedicado a la preparación y venta de platos típicos andinos, como la torreja de quinua y el t’himpo de pejerrey.',
                'created_at' => now(),
                'updated_at' => now(),
                /* id 1 */
            ],
            [
                'nombre' => 'Tienda de Artesanías',
                'descripcion' => 'Negocio que ofrece productos artesanales elaborados por comunidades locales, como textiles bordados y cerámica.',
                'created_at' => now(),
                'updated_at' => now(),

                /* id 2 */
            ],
            [
                'nombre' => 'Agencia de Viajes',
                'descripcion' => 'Empresa que organiza y ofrece paquetes turísticos para visitar comunidades como Llachón, Ccotos y Escallani.',
                'created_at' => now(),
                'updated_at' => now(),

                /* id 3 */
            ],
            [
                'nombre' => 'Alojamiento Rural',
                'descripcion' => 'Establecimiento que ofrece hospedaje en casas de familias locales, permitiendo experiencias de turismo vivencial.',
                'created_at' => now(),
                'updated_at' => now(),

                /* id 4 */
            ],
            [
                'nombre' => 'Tienda Online',
                'descripcion' => 'Plataforma digital que comercializa productos locales, como artesanías y productos agrícolas, a través de internet.',
                'created_at' => now(),
                'updated_at' => now(),

                /* id 5 */
            ]
        ]);
    }
}
