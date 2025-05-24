<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasProductosSeeder extends Seeder
{
    public function run()
    {
        DB::table('categorias_productos')->insert([
            [
                'nombre' => 'Artesanías Textiles',
                'descripcion' => 'Productos elaborados con técnicas tradicionales, como ponchos, frazadas y sombreros bordados.',
                'imagen_url' => 'images/artesanias_textiles.jpg',
                'icono_url' => 'iconos/artesanias_textiles.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cerámica',
                'descripcion' => 'Artículos de barro cocido, como ollas, platos y figuras decorativas, elaborados por artesanos locales.',
                'imagen_url' => 'images/ceramica.jpg',
                'icono_url' => 'iconos/ceramica.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Productos Pesqueros',
                'descripcion' => 'Pescado fresco y otros productos del Lago Titicaca, como el pejerrey y el camarón.',
                'imagen_url' => 'images/productos_pesqueros.jpg',
                'icono_url' => 'iconos/productos_pesqueros.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Alimentos Típicos',
                'descripcion' => 'Alimentos preparados con ingredientes locales, como la quinua, el maíz, el camote y otros productos autóctonos.',
                'imagen_url' => 'images/alimentos_tipicos.jpg',
                'icono_url' => 'iconos/alimentos_tipicos.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Ropa Típica',
                'descripcion' => 'Ropa y accesorios tradicionales como ponchos, sombreros, y mantas, hechos a mano por artesanos locales.',
                'imagen_url' => 'images/ropa_tipica.jpg',
                'icono_url' => 'iconos/ropa_tipica.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
