<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('metodo_pagos')->insert([
            'nombre' => 'Visa',
            'descripcion' => 'Pago mediante tarjeta Visa a través de Stripe.',
            'activo' => true,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
