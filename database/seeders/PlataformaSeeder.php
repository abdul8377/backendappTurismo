<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Emprendimiento;

class PlataformaSeeder extends Seeder
{
    public function run(): void
    {
        Emprendimiento::updateOrCreate(
            ['emprendimientos_id' => 999],   // PK deseada
            [
                'codigo_unico'   => 'SYSPLT',  // asegúrate de que sea único
                'nombre'         => 'Plataforma',
                'descripcion'    => 'Entidad interna para comisiones y ajustes.',
                'tipo_negocio_id'=> null,
                'direccion'      => '—',
                'telefono'       => '—',
                'estado'         => 'inactivo',
                'fecha_registro' => now(),
            ]
        );
    }
}
