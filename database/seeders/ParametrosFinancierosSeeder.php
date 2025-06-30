<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParametroFinanciero;

class ParametrosFinancierosSeeder extends Seeder
{
    public function run(): void
    {
        ParametroFinanciero::updateOrCreate(
            ['clave' => 'comision_porcentaje'],
            ['valor' => '10']   // 10 % de comisión por defecto
        );

        ParametroFinanciero::updateOrCreate(
            ['clave' => 'dias_retenidos'],
            ['valor' => '3']    // fondos retenidos 3 días
        );
    }
}
