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
            'imagen' => 'images/turismo_vivencial.jpg',
            'icono' => 'iconos/turismo_vivencial.png'

            /* id 1 */
        ]);

        CategoriaServicio::create([
            'nombre' => 'Gastronomía Andina',
            'descripcion' => 'Ofrecemos platos tradicionales como la torreja de quinua, sopa de illaco y t’himpo de pejerrey.',
            'imagen' => 'images/gastronomia_andina.jpg',
            'icono' => 'iconos/gastronomia_andina.png'

            /* id 2 */
        ]);

        CategoriaServicio::create([
            'nombre' => 'Ecoturismo y Senderismo',
            'descripcion' => 'Rutas naturales en la península de Capachica, con miradores panorámicos y observación de flora y fauna.',
            'imagen' => 'images/ecoturismo_senderismo.jpg',
            'icono' => 'iconos/ecoturismo_senderismo.png'

            /* id 3 */
        ]);

        CategoriaServicio::create([
            'nombre' => 'Pesca Artesanal',
            'descripcion' => 'Participa en la pesca tradicional en el Lago Titicaca, utilizando métodos ancestrales.',
            'imagen' => 'images/pesca_artesanal.jpg',
            'icono' => 'iconos/pesca_artesanal.png'

            /* id 4 */
        ]);
    }
}
