<?php

namespace Database\Seeders;

use App\Models\CategoriaServicio;
use Illuminate\Database\Seeder;

class CategoriaServicioSeeder extends Seeder
{
    public function run()
    {
        CategoriaServicio::create([
            'nombre' => 'Turismo Vivencial',
            'descripcion' => 'Experiencias auténticas donde los visitantes conviven con familias locales y aprenden sobre sus tradiciones.',
            'imagen_url' => 'images/turismo_vivencial.jpeg',
            'icono_url' => 'iconos/turismo_vivencial.png'
        ]);

        CategoriaServicio::create([
            'nombre' => 'Gastronomía Andina',
            'descripcion' => 'Ofrecemos platos tradicionales como la torreja de quinua, sopa de illaco y t’himpo de pejerrey.',
            'imagen_url' => 'images/gastronomia_andina.jpg',
            'icono_url' => 'iconos/gastronomia_andina.png'
        ]);

        CategoriaServicio::create([
            'nombre' => 'Ecoturismo y Senderismo',
            'descripcion' => 'Rutas naturales en la península de Capachica, con miradores panorámicos y observación de flora y fauna.',
            'imagen_url' => 'images/ecoturismo_senderismo.jpg',
            'icono_url' => 'iconos/ecoturismo_senderismo.png'
        ]);

        CategoriaServicio::create([
            'nombre' => 'Pesca Artesanal',
            'descripcion' => 'Participa en la pesca tradicional en el Lago Titicaca, utilizando métodos ancestrales.',
            'imagen_url' => 'images/pesca_artesanal.webp',
            'icono_url' => 'iconos/pesca_artesanal.png'
        ]);
    }
}
