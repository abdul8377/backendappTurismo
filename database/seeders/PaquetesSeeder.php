<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaquetesSeeder extends Seeder
{
    public function run()
    {
        $fixtures = [
            [
                'emprendimientos_id' => 1,
                'nombre'             => 'Aventura Selva Tropical',
                'descripcion'        => 'Tour de 3 días explorando la selva con canopy, rafting y caminatas nocturnas.',
                'precio_total'       => 1500.00,
                'estado'             => 'activo',
                // IDs de servicios que ya existen en tu tabla `servicios`
                'servicios'          => [1, 2, 3],
            ],
            [
                'emprendimientos_id' => 6,
                'nombre'             => 'Tour Cultural Andino',
                'descripcion'        => 'Visitas a comunidades locales, degustación de comida típica y taller de tejidos.',
                'precio_total'       => 800.50,
                'estado'             => 'activo',
                'servicios'          => [2, 4],
            ],
            [
                'emprendimientos_id' => 1,
                'nombre'             => 'Escapada Lago Titicaca',
                'descripcion'        => 'Paseo en bote por el lago, estadía en isla flotante y almuerzo con pescados locales.',
                'precio_total'       => 1200.75,
                'estado'             => 'inactivo',
                'servicios'          => [1,2,3],
            ],
        ];

        foreach ($fixtures as $item) {
            // 1) Inserta el paquete y obtiene su ID
            $paqueteId = DB::table('paquetes')->insertGetId([
                'emprendimientos_id' => $item['emprendimientos_id'],
                'nombre'             => $item['nombre'],
                'descripcion'        => $item['descripcion'],
                'precio_total'       => $item['precio_total'],
                'estado'             => $item['estado'],
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            // 2) Inserta manualmente cada relación paquete–servicio
            foreach ($item['servicios'] as $servicioId) {
                DB::table('detalle_servicio_paquete')->insert([
                    'paquetes_id'  => $paqueteId,
                    'servicios_id' => $servicioId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
