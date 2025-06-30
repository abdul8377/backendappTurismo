<?php

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\Api\CategoriaServicioApiController;
use App\Http\Controllers\Api\CategoryProductsApiController;
use App\Http\Controllers\Api\DetalleReservaApiController;
use App\Http\Controllers\Api\EmprendimientoController;
use App\Http\Controllers\Api\PaqueteApiController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\Api\ServicioApiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ZonaTuristicaApiController;
use App\Http\Controllers\EmprendimientoUsuario\EmprendimientoUsuarioController;
use App\Http\Controllers\Api\TipoDeNegocioController;
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
*/

Route::get('emprendimientos', [EmprendimientoController::class, 'index']);
Route::get('emprendimientos/{id}', [EmprendimientoController::class, 'show']);
Route::post('emprendimientos', [EmprendimientoController::class, 'store']);
Route::put('emprendimientos/{id}', [EmprendimientoController::class, 'update']);
Route::delete('emprendimientos/{id}', [EmprendimientoController::class, 'destroy']);

Route::get('users', [UserController::class, 'index']);
Route::get('users/{id}', [UserController::class, 'show']);

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


//Route::apiResource('productos', ProductoApiController::class);
Route::apiResource('categorias-productos', CategoryProductsApiController::class);


Route::apiResource('emprendimientos', EmprendimientoController::class);

//Route::apiResource('servicios', ServicioApiController::class);
Route::get('/servicios', [ServicioApiController::class, 'index']);
Route::get('/servicios/{id}', [ServicioApiController::class, 'show']);

Route::get('/productos', [ProductoApiController::class, 'index']);

Route::apiResource('reservas', ReservaApiController::class);
Route::apiResource('detalle-reservas', DetalleReservaApiController::class);


Route::get('productos', [ProductoApiController::class, 'index']);

Route::get('paquetes', [PaqueteApiController::class, 'index']);


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

    // Emprendimientos: creación, activación, solicitudes y respuestas
    // Route::post('/emprendimientos', [EmprendimientoController::class, 'store']);
    // Route::post('/emprendimientos/{id}/activar', [EmprendimientoController::class, 'activarEmprendimiento']);
    // Route::post('/emprendimientos/solicitud', [EmprendimientoController::class, 'enviarSolicitud']);
    // Route::get('/emprendimientos/{id}/solicitudes', [EmprendimientoController::class, 'listarSolicitudesPendientes']);
    // Route::post('/solicitudes/{id}/responder', [EmprendimientoController::class, 'responderSolicitud']);
    // Route::get('/solicitudes', [EmprendimientoController::class, 'solicitudesUsuario']);
    // Route::get('/emprendimientos/estado-solicitud', [EmprendimientoController::class, 'estadoSolicitudEmprendedor']);

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

    // Tipos de negocio: creación, actualización, eliminación
    // Route::post('tipos-de-negocio', [TipoDeNegocioController::class, 'store']);
    // Route::put('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'update']);
    // Route::delete('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'destroy']);
});

// CRUD emprendedor (todos los métodos bajo /api/emprendedor/productos)
Route::middleware(['auth:sanctum', 'role:Emprendedor'])->prefix('emprendedor')->group(function () {
    Route::get('/productos',          [ProductoApiController::class, 'index']);
    Route::post('/productos',          [ProductoApiController::class, 'store']);
    Route::get('//productos/{id}',          [ProductoApiController::class, 'show']);
    Route::put('/productos/{id}',     [ProductoApiController::class, 'update']);
    Route::delete('/productos/{id}',     [ProductoApiController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'role:Emprendedor'])->prefix('emprendedor')->group(function () {
    Route::get('/servicios',          [ServicioApiController::class, 'index']);
    Route::post('/servicios',          [ServicioApiController::class, 'store']);
    Route::get('/servicios/{id}',          [ServicioApiController::class, 'show']);
    Route::put('/servicios/{id}',     [ServicioApiController::class, 'update']);
    Route::delete('/servicios/{id}',     [ServicioApiController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', 'role:Emprendedor'])->prefix('emprendedor')->group(function () {
    Route::get('/paquetes',          [PaqueteApiController::class, 'index']);
    Route::post('/paquetes',          [PaqueteApiController::class, 'store']);
    Route::get('/paquetes/{id}',          [PaqueteApiController::class, 'show']);
    Route::put('/paquetes/{id}',     [PaqueteApiController::class, 'update']);
    Route::delete('/paquetes/{id}',     [PaqueteApiController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:Administrador'])->prefix('emprendedor')->group(function () {
    Route::get('/zonas-turisticas',          [ZonaTuristicaApiController::class, 'index']);
    Route::post('/zonas-turisticas',          [ZonaTuristicaApiController::class, 'store']);
    Route::get('/zonas-turisticas/{id}',          [ZonaTuristicaApiController::class, 'show']);
    Route::put('/zonas-turisticas/{id}',     [ZonaTuristicaApiController::class, 'update']);
    Route::delete('/zonas-turisticas/{id}',     [ZonaTuristicaApiController::class, 'destroy']);
});
