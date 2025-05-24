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
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Crear el usuario para el Restaurante (Tipo de Negocio 1)
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

        $role = Role::findOrCreate('Emprendedor');
        $user1->assignRole($role);

        // Crear perfil para el emprendedor 1
        $perfil1 = PerfilEmprendedor::create([
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

        // Crear el emprendimiento 1 (Restaurante)
        $emprendimiento1 = Emprendimiento::create([
            'nombre' => 'Restaurante Andino',
            'descripcion' => 'Establecimiento dedicado a la preparación y venta de platos típicos andinos.',
            'tipo_negocio_id' => 1, // Relación con el tipo de negocio Restaurante
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654321',
            'estado' => 'activo',
        ]);

        // Relacionar emprendimiento con el usuario (Emprendedor)
        $user1->emprendimientos()->attach($emprendimiento1->emprendimientos_id);

        // Crear productos para el restaurante (categoría alimentos típicos)
        $categoriaProducto = CategoriaProducto::find(4); // Categoría de alimentos típicos

        Producto::create([
            'emprendimientos_id' => $emprendimiento1->emprendimientos_id,
            'nombre' => 'Torreja de Quinua',
            'descripcion' => 'Plato típico de quinua servido con salsa de frutas nativas.',
            'precio' => 15.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 50,
        ]);

        Producto::create([
            'emprendimientos_id' => $emprendimiento1->emprendimientos_id,
            'nombre' => 'T’himpo de Pejerrey',
            'descripcion' => 'Sopa tradicional con pejerrey, una delicia del Lago Titicaca.',
            'precio' => 18.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 40,
        ]);

        Producto::create([
            'emprendimientos_id' => $emprendimiento1->emprendimientos_id,
            'nombre' => 'Sopa de Illaco',
            'descripcion' => 'Sopa tradicional andina hecha con ingredientes locales.',
            'precio' => 12.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 30,
        ]);

        // 2. Crear el usuario para la Tienda de Artesanías (Tipo de Negocio 2)
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

        // Crear perfil para el emprendedor 2
        $perfil2 = PerfilEmprendedor::create([
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

        // Crear el emprendimiento 2 (Tienda de Artesanías)
        $emprendimiento2 = Emprendimiento::create([
            'nombre' => 'Tienda Artesanal Capachica',
            'descripcion' => 'Venta de productos artesanales como textiles bordados y cerámica.',
            'tipo_negocio_id' => 2, // Relación con el tipo de negocio Tienda de Artesanías
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654322',
            'estado' => 'activo',
        ]);

        // Relacionar emprendimiento con el usuario (Emprendedor)
        $user2->emprendimientos()->attach($emprendimiento2->emprendimientos_id);

        // Crear productos para la tienda de artesanías (categoría artesanías textiles y cerámica)
        $categoriaProducto = CategoriaProducto::find(1); // Categoría de Artesanías Textiles

        Producto::create([
            'emprendimientos_id' => $emprendimiento2->emprendimientos_id,
            'nombre' => 'Poncho de Lana',
            'descripcion' => 'Poncho tejido a mano con lana de alpaca.',
            'precio' => 40.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 100,
        ]);

        // Cambiar la categoría de productos para Cerámica
        $categoriaProducto = CategoriaProducto::find(2); // Categoría Cerámica

        Producto::create([
            'emprendimientos_id' => $emprendimiento2->emprendimientos_id,
            'nombre' => 'Figura de Cerámica',
            'descripcion' => 'Figura artesanal hecha a mano, típica de la región de Capachica.',
            'precio' => 25.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 50,
        ]);

        Producto::create([
            'emprendimientos_id' => $emprendimiento2->emprendimientos_id,
            'nombre' => 'Plato de Cerámica Pintada',
            'descripcion' => 'Plato pintado a mano con motivos tradicionales.',
            'precio' => 30.00,
            'unidad' => 'Unidad',
            'categorias_productos_id' => $categoriaProducto->categorias_productos_id,
            'stock' => 60,
        ]);

        // 3. Crear el usuario para la Agencia de Viajes (Tipo de Negocio 3)
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

        // Crear perfil para el emprendedor 3
        $perfil3 = PerfilEmprendedor::create([
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

        // Crear el emprendimiento 3 (Agencia de Viajes)
        $emprendimiento3 = Emprendimiento::create([
            'nombre' => 'Turismo Capachica',
            'descripcion' => 'Ofrecemos paquetes turísticos a las comunidades de Capachica, Llachón, y Escallani.',
            'tipo_negocio_id' => 3, // Relación con el tipo de negocio Agencia de Viajes
            'direccion' => 'Capachica, Puno, Perú',
            'telefono' => '987654323',
            'estado' => 'activo',
        ]);

        // Relacionar emprendimiento con el usuario (Emprendedor)
        $user3->emprendimientos()->attach($emprendimiento3->emprendimientos_id);

        // Crear servicios para la agencia de viajes (categoría servicios)
        $categoriaServicio = CategoriaServicio::find(1); // Suponiendo que Turismo Vivencial ya existe

        Servicio::create([
            'emprendimientos_id' => $emprendimiento3->emprendimientos_id,
            'nombre' => 'Tour Cultural por Capachica',
            'descripcion' => 'Recorrido cultural por las comunidades locales, con enfoque en la historia y tradiciones.',
            'precio' => 50.00,
            'capacidad_maxima' => 10,
            'duracion_servicio' => 4,
            'imagen_destacada' => 'images/tour_capachica.jpg',
            'categorias_servicios_id' => $categoriaServicio->categorias_servicios_id,
        ]);
    }
}
