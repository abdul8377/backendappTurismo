<?php

namespace Database\Seeders;

use App\Models\CategoriaServicio;
use App\Models\CategoriaProducto;
use App\Models\Emprendimiento;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\User;
use App\Models\PerfilEmprendedor;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class EmprendedorSeeder extends Seeder
{
    public function run()
    {
        $role = Role::findOrCreate('Emprendedor');

        // --- USUARIO 1 - Restaurante Andino ---
        $user1 = User::create([
            'name' => 'Juan',
            'last_name' => 'Perez',
            'user' => bcrypt('user123'),
            'user_code_plain' => 'user123',
            'email' => 'juan.perez1@example.com',
            'password' => bcrypt('password123'),
            'country' => 'Perú',
            'zip_code' => '12345',
        ]);
        $user1->assignRole($role);

        PerfilEmprendedor::create([
            'users_id' => $user1->id,
            'dni' => '12345678',
            'telefono_contacto' => '987654321',
            'experiencia' => 'Emprendedor en el sector de gastronomía andina.',
            'estado_validacion' => 'pendiente',
            'descripcion_emprendimiento' => 'Restaurante dedicado a la comida típica andina, como la torreja de quinua y t’himpo de pejerrey.',
            'gmail_contacto' => 'juan.perez1@gmail.com',
            'gmail_confirmado' => true,
            'codigo' => 'EMPR001',
        ]);

        $emprendimiento1 = Emprendimiento::create([
            'nombre' => 'Restaurante Andino',
            'descripcion' => 'Establecimiento dedicado a la preparación y venta de platos típicos andinos.',
            'tipo_negocio_id' => 1,
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654321',
            'estado' => 'activo',
        ]);
        $user1->emprendimientos()->attach($emprendimiento1->emprendimientos_id);

        $categoriaAlimentos = CategoriaProducto::find(4); // Alimentos Típicos

        // Productos existentes + 10 nuevos productos para restaurante andino con imagen_url
        $productos = [
            [
                'nombre' => 'Torreja de Quinua',
                'descripcion' => 'Plato típico de quinua servido con salsa de frutas nativas.',
                'precio' => 15.00,
                'unidad' => 'Unidad',
                'stock' => 50,
                'imagen_url' => 'images/torreja_quinua.jpg',
            ],
            [
                'nombre' => 'T’himpo de Pejerrey',
                'descripcion' => 'Sopa tradicional con pejerrey, una delicia del Lago Titicaca.',
                'precio' => 18.00,
                'unidad' => 'Unidad',
                'stock' => 40,
                'imagen_url' => 'images/thimpo_pejerrey.jpg',
            ],
            [
                'nombre' => 'Sopa de Illaco',
                'descripcion' => 'Sopa tradicional andina hecha con ingredientes locales.',
                'precio' => 12.00,
                'unidad' => 'Unidad',
                'stock' => 30,
                'imagen_url' => 'images/sopa_illaco.jpg',
            ],
            [
                'nombre' => 'Pachamanca',
                'descripcion' => 'Plato tradicional cocido bajo tierra con piedras calientes.',
                'precio' => 25.00,
                'unidad' => 'Unidad',
                'stock' => 20,
                'imagen_url' => 'images/pachamanca.jpg',
            ],
            [
                'nombre' => 'Caldo de Gallina',
                'descripcion' => 'Sopa nutritiva y tradicional hecha con gallina criolla.',
                'precio' => 20.00,
                'unidad' => 'Unidad',
                'stock' => 35,
                'imagen_url' => 'images/caldo_gallina.jpg',
            ],
            // 10 productos nuevos
            [
                'nombre' => 'Ceviche de Trucha',
                'descripcion' => 'Ceviche fresco hecho con trucha del lago Titicaca.',
                'precio' => 22.00,
                'unidad' => 'Unidad',
                'stock' => 45,
                'imagen_url' => 'images/ceviche_trucha.jpg',
            ],
            [
                'nombre' => 'Charqui de Alpaca',
                'descripcion' => 'Carne seca tradicional de alpaca.',
                'precio' => 35.00,
                'unidad' => 'Paquete',
                'stock' => 60,
                'imagen_url' => 'images/charqui_alpaca.jpg',
            ],
            [
                'nombre' => 'Queso Fresco',
                'descripcion' => 'Queso fresco producido en la zona andina.',
                'precio' => 18.00,
                'unidad' => 'Unidad',
                'stock' => 50,
                'imagen_url' => 'images/queso_fresco.jpg',
            ],
            [
                'nombre' => 'Chicha de Jora',
                'descripcion' => 'Bebida tradicional fermentada de maíz.',
                'precio' => 10.00,
                'unidad' => 'Botella',
                'stock' => 80,
                'imagen_url' => 'images/chicha_jora.jpg',
            ],
            [
                'nombre' => 'Humitas',
                'descripcion' => 'Pasteles de maíz envueltos en hojas de choclo.',
                'precio' => 12.00,
                'unidad' => 'Unidad',
                'stock' => 70,
                'imagen_url' => 'images/humitas.jpg',
            ],
            [
                'nombre' => 'Caldo de Carnero',
                'descripcion' => 'Caldo tradicional de carne de carnero.',
                'precio' => 23.00,
                'unidad' => 'Unidad',
                'stock' => 25,
                'imagen_url' => 'images/caldo_carnero.jpg',
            ],
        ];

        foreach ($productos as $producto) {
            Producto::create(array_merge($producto, [
                'emprendimientos_id' => $emprendimiento1->emprendimientos_id,
                'categorias_productos_id' => $categoriaAlimentos->categorias_productos_id,
            ]));
        }

        // --- USUARIO 2 - Tienda Artesanal ---
        $user2 = User::create([
            'name' => 'Maria',
            'last_name' => 'Lopez',
            'user' => bcrypt('user456'),
            'user_code_plain' => 'user456',
            'email' => 'maria.lopez@example.com',
            'password' => bcrypt('password456'),
            'country' => 'Perú',
            'zip_code' => '12346',
        ]);
        $user2->assignRole($role);

        PerfilEmprendedor::create([
            'users_id' => $user2->id,
            'dni' => '87654321',
            'telefono_contacto' => '987654322',
            'experiencia' => 'Emprendedora en el sector de productos artesanales.',
            'estado_validacion' => 'pendiente',
            'descripcion_emprendimiento' => 'Tienda dedicada a la venta de productos textiles y cerámicos, hechos a mano por comunidades locales.',
            'gmail_contacto' => 'maria.lopez@gmail.com',
            'gmail_confirmado' => true,
            'codigo' => 'EMPR002',
        ]);

        $emprendimiento2 = Emprendimiento::create([
            'nombre' => 'Tienda Artesanal Capachica',
            'descripcion' => 'Venta de productos artesanales como textiles bordados y cerámica.',
            'tipo_negocio_id' => 2,
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654322',
            'estado' => 'activo',
        ]);
        $user2->emprendimientos()->attach($emprendimiento2->emprendimientos_id);

        $categoriaTextiles = CategoriaProducto::find(1); // Artesanías Textiles
        $categoriaCeramica = CategoriaProducto::find(2); // Cerámica

        // Productos adicionales tienda artesanal
        $productosArtesania = [
            [
                'nombre' => 'Poncho de Lana',
                'descripcion' => 'Poncho tejido a mano con lana de alpaca.',
                'precio' => 40.00,
                'unidad' => 'Unidad',
                'stock' => 100,
                'imagen_url' => 'images/poncho_lana.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Figura de Cerámica',
                'descripcion' => 'Figura artesanal hecha a mano, típica de la región de Capachica.',
                'precio' => 25.00,
                'unidad' => 'Unidad',
                'stock' => 50,
                'imagen_url' => 'images/figura_ceramica.jpg',
                'categorias_productos_id' => $categoriaCeramica->categorias_productos_id,
            ],
            [
                'nombre' => 'Plato de Cerámica Pintada',
                'descripcion' => 'Plato pintado a mano con motivos tradicionales.',
                'precio' => 30.00,
                'unidad' => 'Unidad',
                'stock' => 60,
                'imagen_url' => 'images/plato_ceramica.jpg',
                'categorias_productos_id' => $categoriaCeramica->categorias_productos_id,
            ],
            [
                'nombre' => 'Bufanda de Alpaca',
                'descripcion' => 'Bufanda suave y cálida hecha con lana de alpaca.',
                'precio' => 35.00,
                'unidad' => 'Unidad',
                'stock' => 75,
                'imagen_url' => 'images/bufanda_alpaca.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Sombrero Andino',
                'descripcion' => 'Sombrero tradicional tejido a mano.',
                'precio' => 22.00,
                'unidad' => 'Unidad',
                'stock' => 80,
                'imagen_url' => 'images/sombrero_andino.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Alfombra Tejida',
                'descripcion' => 'Alfombra decorativa con diseños tradicionales.',
                'precio' => 60.00,
                'unidad' => 'Unidad',
                'stock' => 40,
                'imagen_url' => 'images/alfombra_tejida.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Juego de Vasos Cerámicos',
                'descripcion' => 'Vasos hechos a mano con pinturas tradicionales.',
                'precio' => 45.00,
                'unidad' => 'Set',
                'stock' => 30,
                'imagen_url' => 'images/vasos_ceramicos.jpg',
                'categorias_productos_id' => $categoriaCeramica->categorias_productos_id,
            ],
            [
                'nombre' => 'Collar de Piedras',
                'descripcion' => 'Collar artesanal hecho con piedras naturales.',
                'precio' => 28.00,
                'unidad' => 'Unidad',
                'stock' => 70,
                'imagen_url' => 'images/collar_piedras.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Bolso de Mano',
                'descripcion' => 'Bolso hecho a mano con materiales naturales.',
                'precio' => 38.00,
                'unidad' => 'Unidad',
                'stock' => 55,
                'imagen_url' => 'images/bolso_mano.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
            [
                'nombre' => 'Tapiz Decorativo',
                'descripcion' => 'Tapiz con motivos andinos para decoración.',
                'precio' => 50.00,
                'unidad' => 'Unidad',
                'stock' => 25,
                'imagen_url' => 'images/tapiz_decorativo.jpg',
                'categorias_productos_id' => $categoriaTextiles->categorias_productos_id,
            ],
        ];

        foreach ($productosArtesania as $producto) {
            Producto::create(array_merge($producto, [
                'emprendimientos_id' => $emprendimiento2->emprendimientos_id,
            ]));
        }

        // --- USUARIO 3 - Agencia de Viajes ---
        $user3 = User::create([
            'name' => 'Carlos',
            'last_name' => 'Sánchez',
            'user' => bcrypt('user789'),
            'user_code_plain' => 'user789',
            'email' => 'carlos.sanchez@example.com',
            'password' => bcrypt('password789'),
            'country' => 'Perú',
            'zip_code' => '12347',
        ]);
        $user3->assignRole($role);

        PerfilEmprendedor::create([
            'users_id' => $user3->id,
            'dni' => '11223344',
            'telefono_contacto' => '987654323',
            'experiencia' => 'Emprendedor en el sector de servicios turísticos.',
            'estado_validacion' => 'pendiente',
            'descripcion_emprendimiento' => 'Agencia de viajes especializada en tours a comunidades andinas.',
            'gmail_contacto' => 'carlos.sanchez@gmail.com',
            'gmail_confirmado' => true,
            'codigo' => 'EMPR003',
        ]);

        $emprendimiento3 = Emprendimiento::create([
            'nombre' => 'Turismo Capachica',
            'descripcion' => 'Ofrecemos paquetes turísticos a las comunidades de Capachica, Llachón, y Escallani.',
            'tipo_negocio_id' => 3,
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654323',
            'estado' => 'activo',
        ]);
        $user3->emprendimientos()->attach($emprendimiento3->emprendimientos_id);

        $categoriaServicio = CategoriaServicio::find(1); // Turismo Vivencial

        // Servicios existentes + 10 nuevos servicios con imagen_url e imagen_destacada
        $servicios = [
            [
                'nombre' => 'Tour Cultural por Capachica',
                'descripcion' => 'Recorrido cultural por las comunidades locales, con enfoque en la historia y tradiciones.',
                'precio' => 50.00,
                'capacidad_maxima' => 10,
                'duracion_servicio' => 4,
                'imagen_url' => 'images/tour_cultural_capachica.jpg',
                'imagen_destacada' => 'images/tour_cultural_destacada.jpg',
            ],
            [
                'nombre' => 'Excursión a Llachón',
                'descripcion' => 'Excursión guiada a la comunidad de Llachón con actividades culturales y gastronómicas.',
                'precio' => 45.00,
                'capacidad_maxima' => 15,
                'duracion_servicio' => 6,
                'imagen_url' => 'images/excursion_llachon.jpg',
                'imagen_destacada' => 'images/excursion_llachon_destacada.jpg',
            ],
            [
                'nombre' => 'Senderismo en la Península',
                'descripcion' => 'Ruta de senderismo con guías expertos, observación de flora y fauna.',
                'precio' => 40.00,
                'capacidad_maxima' => 12,
                'duracion_servicio' => 5,
                'imagen_url' => 'images/senderismo_penisula.jpg',
                'imagen_destacada' => 'images/senderismo_destacada.jpg',
            ],
            // 10 servicios adicionales
            [
                'nombre' => 'Tour Gastronómico Andino',
                'descripcion' => 'Descubre la gastronomía típica con visitas a productores locales.',
                'precio' => 55.00,
                'capacidad_maxima' => 8,
                'duracion_servicio' => 5,
                'imagen_url' => 'images/tour_gastronomico.jpg',
                'imagen_destacada' => 'images/tour_gastronomico_destacada.jpg',
            ],
            [
                'nombre' => 'Tour Fotográfico',
                'descripcion' => 'Ruta para amantes de la fotografía con paisajes naturales increíbles.',
                'precio' => 60.00,
                'capacidad_maxima' => 6,
                'duracion_servicio' => 6,
                'imagen_url' => 'images/tour_fotografico.jpg',
                'imagen_destacada' => 'images/tour_fotografico_destacada.jpg',
            ],
            [
                'nombre' => 'Visita a Comunidades',
                'descripcion' => 'Visita a comunidades tradicionales con actividades culturales.',
                'precio' => 45.00,
                'capacidad_maxima' => 10,
                'duracion_servicio' => 7,
                'imagen_url' => 'images/visita_comunidades.jpg',
                'imagen_destacada' => 'images/visita_comunidades_destacada.jpg',
            ],
            [
                'nombre' => 'Tour de Avistamiento de Aves',
                'descripcion' => 'Excursión para observar aves endémicas en su hábitat natural.',
                'precio' => 50.00,
                'capacidad_maxima' => 10,
                'duracion_servicio' => 4,
                'imagen_url' => 'images/tour_avistamiento_aves.jpg',
                'imagen_destacada' => 'images/tour_avistamiento_aves_destacada.jpg',
            ],
            [
                'nombre' => 'Tour Nocturno Estelar',
                'descripcion' => 'Observación de estrellas con guías astronómicos expertos.',
                'precio' => 55.00,
                'capacidad_maxima' => 5,
                'duracion_servicio' => 3,
                'imagen_url' => 'images/tour_nocturno.jpg',
                'imagen_destacada' => 'images/tour_nocturno_destacada.jpg',
            ],
            [
                'nombre' => 'Tour Ecológico',
                'descripcion' => 'Ruta de ecoturismo con aprendizaje sobre flora y fauna locales.',
                'precio' => 45.00,
                'capacidad_maxima' => 12,
                'duracion_servicio' => 6,
                'imagen_url' => 'images/tour_ecologico.jpg',
                'imagen_destacada' => 'images/tour_ecologico_destacada.jpg',
            ],
            [
                'nombre' => 'Paseo en Kayak',
                'descripcion' => 'Paseo en kayak por el Lago Titicaca, con guía y equipo incluido.',
                'precio' => 65.00,
                'capacidad_maxima' => 8,
                'duracion_servicio' => 4,
                'imagen_url' => 'images/paseo_kayak.jpg',
                'imagen_destacada' => 'images/paseo_kayak_destacada.jpg',
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create(array_merge($servicio, [
                'emprendimientos_id' => $emprendimiento3->emprendimientos_id,
                'categorias_servicios_id' => $categoriaServicio->categorias_servicios_id,
            ]));
        }
    }
}
