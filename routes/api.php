<?php

/* ────────────────────────────────────────────────────────────────
|  CONTROLADORES (importados una sola vez para legibilidad)
|──────────────────────────────────────────────────────────────── */

use App\Http\Controllers\Api\Admin\AdministradorRetiroController;
use App\Http\Controllers\Api\Admin\RetiroController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\CategoriaServicioApiController;
use App\Http\Controllers\Api\CategoryProductsApiController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DetalleReservaApiController;
use App\Http\Controllers\Api\Emprendimiento\EmprendedorRetiroController;
use App\Http\Controllers\Api\EmprendimientoController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\MetodoPagoController;
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

/* =================================================================
|  1.  RUTAS PÚBLICAS  (no requieren token)
|================================================================= */

/* --- Autenticación ------------------------------------------------ */
Route::prefix('auth')->group(function () {
    Route::post('check-email',          [AuthController::class, 'checkEmail']);
    Route::post('login',                [AuthController::class, 'login']);
    Route::post('register',             [AuthController::class, 'register']);
    Route::post('password/email',       [AuthController::class, 'sendResetPasswordEmail']);
    Route::post('password/reset',       [AuthController::class, 'resetPassword']);
});

// Catálogos públicos (solo lectura)
Route::get('emprendimientos',          [EmprendimientoController::class, 'index']);
Route::get('emprendimientos/{id}',     [EmprendimientoController::class, 'show']);
Route::get('tipos-de-negocio',         [TipoDeNegocioController::class, 'index']);
Route::get('tipos-de-negocio/{id}',    [TipoDeNegocioController::class, 'show']);
Route::get('tipos-de-negocio/{id}/emprendimientos', [TipoDeNegocioController::class, 'getEmprendimientosByTipo']);
Route::get('zonas-turisticas',         [ZonaTuristicaApiController::class, 'index']);
Route::get('zonas-turisticas/{id}',    [ZonaTuristicaApiController::class, 'show']);
Route::get('categorias-servicios',     [CategoriaServicioApiController::class, 'index']);
Route::get('categorias-servicios/{id}',[CategoriaServicioApiController::class, 'show']);
Route::apiResource('productos',        ProductoApiController::class)->only(['index','show']);
Route::apiResource('categorias-productos', CategoryProductsApiController::class)->only(['index','show']);
Route::get('servicios',                [ServicioApiController::class, 'index']);
Route::get('servicios/{id}',           [ServicioApiController::class, 'show']);

/* =================================================================
|  2.  RUTAS PROTEGIDAS  (requieren token Sanctum)
|================================================================= */
Route::middleware('auth:sanctum')->group(function () {

    // Rutas para gestionar emprendimientos
    Route::get('/emprendimientos', [EmprendimientoController::class, 'index']);
    Route::post('/emprendimientos', [EmprendimientoController::class, 'store']);
    Route::get('/emprendimientos/{id}', [EmprendimientoController::class, 'show']);
    Route::put('/emprendimientos/{id}', [EmprendimientoController::class, 'update']);
    Route::delete('/emprendimientos/{id}', [EmprendimientoController::class, 'destroy']);
    Route::put('/emprendimientos/{id}/activar', [EmprendimientoController::class, 'activarEmprendimiento']);
    Route::post('/emprendimientos/solicitudes', [EmprendimientoController::class, 'enviarSolicitud']);
    Route::get('/emprendimientos/{id}/solicitudes-pendientes', [EmprendimientoController::class, 'listarSolicitudesPendientes']);
    Route::post('/emprendimientos/solicitudes/{solicitudId}/responder', [EmprendimientoController::class, 'responderSolicitud']);
    Route::get('/emprendimientos/solicitudes', [EmprendimientoController::class, 'solicitudesUsuario']);
    Route::get('/emprendimientos/estado-solicitud', [EmprendimientoController::class, 'estadoSolicitudEmprendedor']);

    // Logout del usuario
    Route::post('auth/logout', function (Request $r) { return (new AuthController)->logout($r); });
    Route::get('user', fn(Request $r) => $r->user());
    Route::get('users/{id}', [UserController::class, 'show']);
});

/* =================================================================
|  3.  RUTAS POR ROLES (Administrador, Moderador y Emprendedor)
|================================================================= */

/* --- Rutas para Administradores -------------------------------- */
Route::middleware('role:Administrador')->group(function () {
    Route::apiResource('tipos-de-negocio', TipoDeNegocioController::class)->except(['index','show']);
    Route::apiResource('zonas-turisticas', ZonaTuristicaApiController::class)->except(['index','show']);
    Route::apiResource('categorias-servicios', CategoriaServicioApiController::class)->except(['index','show']);
    Route::apiResource('categorias-productos', CategoryProductsApiController::class)->only(['store', 'update', 'destroy']);
});

/* --- Rutas para Emprendedores ----------------------------------- */
Route::middleware('role:Emprendedor')->group(function () {
    // Rutas para gestionar productos
    Route::apiResource('productos', ProductoApiController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('servicios', ServicioApiController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('paquetes', PaqueteApiController::class)->only(['store', 'update', 'destroy']);

    // Rutas para gestionar relaciones emprendimiento-usuario
    Route::post('emprendimientos-usuarios', [EmprendimientoUsuarioController::class, 'store']);
    Route::put('emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'update']);
    Route::delete('emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'destroy']);
    Route::get('emprendimientos-usuarios/{id}', [EmprendimientoUsuarioController::class, 'show']);
});

/* --- Métodos de pago (Administrador) ----------------------------- */
Route::prefix('metodos-pago')->middleware('role:Administrador')->group(function () {
    Route::get('/', [MetodoPagoController::class, 'index']);
    Route::post('/', [MetodoPagoController::class, 'store']);
    Route::get('{id}', [MetodoPagoController::class, 'show']);
    Route::put('{id}', [MetodoPagoController::class, 'update']);
    Route::delete('{id}', [MetodoPagoController::class, 'destroy']);
    Route::patch('{id}/suspend', [MetodoPagoController::class, 'suspend']);
    Route::patch('{id}/activate', [MetodoPagoController::class, 'activate']);
});

/* --- Panel Emprendedor (saldo, movimientos y retiros) ------------- */
Route::middleware('role:Emprendedor')->prefix('emprendimiento')->controller(EmprendedorRetiroController::class)->group(function () {
    Route::get('/saldo', 'saldo');
    Route::get('/movimientos', 'movimientos');
    Route::post('/retiros', 'store');
    Route::get('/retiros/{id}', 'show');
});

/* --- Panel Administrador (retiros y movimientos globales) ---------- */
Route::middleware('role:Administrador')->prefix('admin')->controller(AdministradorRetiroController::class)->group(function () {
    Route::get('/retiros', 'index');
    Route::post('/retiros/{id}/aprobar', 'aprobar');
    Route::post('/retiros/{id}/rechazar', 'rechazar');
    Route::get('/movimientos', 'movimientos');
});

/* --- Rutas de Carrito y Favoritos (Usuario autenticado) ------------ */
Route::middleware('auth:sanctum')->group(function () {
    // Carrito
    Route::get('carrito', [CarritoController::class, 'index']);
    Route::post('carrito', [CarritoController::class, 'store']);
    Route::put('carrito/{carrito}', [CarritoController::class, 'update']);
    Route::delete('carrito/{carrito}', [CarritoController::class, 'destroy']);

    // Favoritos
    Route::get('favoritos', [FavoritoController::class, 'index']);
    Route::post('favoritos', [FavoritoController::class, 'store']);
    Route::delete('favoritos/{id}', [FavoritoController::class, 'destroy']);
});

/* --- Rutas de Chat (WebSockets / Reverb) -------------------------- */
Route::middleware('auth:sanctum')->prefix('chat')->controller(ChatController::class)->group(function () {
    Route::post('/abrir', 'abrir');
    Route::get('/{conversaciones_id}/mensajes', 'mensajes');
    Route::post('/{conversaciones_id}/mensajes', 'enviar');
});
