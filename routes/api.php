<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\CategoriaServicioApiController;
use App\Http\Controllers\Api\CategoryProductsApiController;
use App\Http\Controllers\Api\DetalleReservaApiController;
use App\Http\Controllers\Api\EmprendimientoController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\Api\ServicioApiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ZonaTuristicaApiController;
use App\Http\Controllers\EmprendimientoUsuario\EmprendimientoUsuarioController;
use App\Http\Controllers\Api\TipoDeNegocioController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas para autenticación
|--------------------------------------------------------------------------
| Estas rutas permiten el registro, login y recuperación de contraseña.
| No requieren autenticación porque son para usuarios no logueados.
*/

Route::prefix('auth')->group(function () {
    Route::post('check-email', [AuthController::class, 'checkEmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('/password/email', [AuthController::class, 'sendResetPasswordEmail']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin autenticación) para consultas
|--------------------------------------------------------------------------
| Estas rutas son solo para obtener información pública o listados.
| No modifican datos, por eso no requieren token.
*/
// Nota: Se quitaron las rutas a métodos no implementados en EmprendimientoController
// Route::get('/emprendimientos', [EmprendimientoController::class, 'index']);
// Route::get('/emprendimientos/{id}', [EmprendimientoController::class, 'show']);


Route::get('tipos-de-negocio', [TipoDeNegocioController::class, 'index']);
Route::get('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'show']);
Route::get('tipos-de-negocio/{id}/emprendimientos', [TipoDeNegocioController::class, 'getEmprendimientosByTipo']);
Route::post('tipos-de-negocio', [TipoDeNegocioController::class, 'store']);
Route::put('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'update']);
Route::delete('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'destroy']);


Route::get('/zonas-turisticas', [ZonaTuristicaApiController::class, 'index']);
Route::get('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'show']);

Route::get('/categorias-servicios', [CategoriaServicioApiController::class, 'index']);
Route::get('/categorias-servicios/{id}', [CategoriaServicioApiController::class, 'show']);


Route::apiResource('productos', ProductoApiController::class);
Route::apiResource('categorias-productos', CategoryProductsApiController::class);
Route::get('/productos/{id}', [ProductoApiController::class, 'show']);

Route::apiResource('emprendimientos', EmprendimientoController::class);

//Route::apiResource('servicios', ServicioApiController::class);
Route::get('/servicios', [ServicioApiController::class, 'index']);
Route::get('/servicios/{id}', [ServicioApiController::class, 'show']);



Route::apiResource('reservas', ReservaApiController::class);
Route::apiResource('detalle-reservas', DetalleReservaApiController::class);



/*
|--------------------------------------------------------------------------
| Rutas protegidas con middleware auth:sanctum
|--------------------------------------------------------------------------
| Estas rutas requieren autenticación vía token Sanctum.
| Incluyen creación, actualización, eliminación y operaciones sensibles.
*/
Route::middleware('auth:sanctum')->group(function () {
    // Logout y obtener datos del usuario autenticado
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);

    // Usuarios: activar/desactivar y cambiar contraseña
    Route::patch('users/{id}/active', [UserController::class, 'toggleActive']);
    Route::patch('users/{id}/password', [UserController::class, 'changePassword']);

    // Emprendimiento Usuario: gestión de relaciones con emprendimientos
    Route::post('/emprendimientos-usuarios', [EmprendimientoUsuarioController::class, 'store']);
    Route::put('/emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'update']);
    Route::delete('/emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'destroy']);
    Route::get('/emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'show']);

    // Categorías de servicios: creación, actualización, eliminación
    Route::post('/categorias-servicios', [CategoriaServicioApiController::class, 'store']);
    Route::put('/categorias-servicios/{id}', [CategoriaServicioApiController::class, 'update']);
    Route::delete('/categorias-servicios/{id}', [CategoriaServicioApiController::class, 'destroy']);

    // Zonas turísticas: creación, actualización, eliminación
    Route::post('/zonas-turisticas', [ZonaTuristicaApiController::class, 'store']);
    Route::put('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'update']);
    Route::delete('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'destroy']);

    Route::get('/favoritos', [FavoritoController::class, 'index']);
    Route::post('/favoritos', [FavoritoController::class, 'store']);
    Route::delete('/favoritos/{id}', [FavoritoController::class, 'destroy']);

    // Ver el carrito
    Route::get('carrito', [CarritoController::class, 'index']);

    // Agregar un ítem al carrito
    Route::post('carrito', [CarritoController::class, 'store']);

    // Actualizar un ítem en el carrito
    Route::put('carrito/{carrito}', [CarritoController::class, 'update']);

    // Eliminar un ítem del carrito
    Route::delete('carrito/{carrito}', [CarritoController::class, 'destroy']);


    // Crea la venta (mueve ítems de carrito → ventas + detalle_ventas)
    Route::post('ventas', [VentaController::class, 'store']);

    // Procesa el pago y actualiza la venta (con ID de la venta en la URL)
    Route::post('ventas/{venta}/pagar', [VentaController::class, 'procesarPago']);

    // Listar todas las compras (ventas) realizadas por el usuario autenticado
    Route::get('compras', [VentaController::class, 'listarCompras']);




});

Route::post('/pagar', [PagoController::class, 'procesarPago']);
/*
|--------------------------------------------------------------------------
| Opcional: rutas para manejo de imágenes (si se activan)
|--------------------------------------------------------------------------
*/
// Route::post('/imagenes', [ImageableController::class, 'store']);
// Route::get('/imagenes/{type}/{id}', [ImageableController::class, 'index']);
///asdas
use App\Http\Controllers\Api\MetodoPagoController;

Route::prefix('metodos-pago')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [MetodoPagoController::class, 'index']); // Listar todos los métodos de pago
    Route::post('/', [MetodoPagoController::class, 'store']); // Crear un nuevo método de pago
    Route::get('{id}', [MetodoPagoController::class, 'show']); // Obtener un método de pago específico
    Route::put('{id}', [MetodoPagoController::class, 'update']); // Actualizar un método de pago
    Route::delete('{id}', [MetodoPagoController::class, 'destroy']); // Eliminar un método de pago
    Route::patch('{id}/suspend', [MetodoPagoController::class, 'suspend']); // Suspender un método de pago
    Route::patch('{id}/activate', [MetodoPagoController::class, 'activate']); // Activar un método de pago
});
