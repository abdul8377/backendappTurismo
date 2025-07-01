<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ────────────────────────────────────────────────────────────────
|  CONTROLADORES (importados una sola vez para legibilidad)
|──────────────────────────────────────────────────────────────── */
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TipoDeNegocioController;
use App\Http\Controllers\Api\ZonaTuristicaApiController;
use App\Http\Controllers\Api\CategoriaServicioApiController;
use App\Http\Controllers\Api\CategoryProductsApiController;
use App\Http\Controllers\Api\ProductoApiController;
use App\Http\Controllers\Api\ServicioApiController;
use App\Http\Controllers\Api\EmprendimientoController;
use App\Http\Controllers\Api\Emprendimiento\RetiroController   as EmpRetiroController;
use App\Http\Controllers\Api\Admin\RetiroController            as AdminRetiroController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\FavoritoController;
use App\Http\Controllers\Api\MetodoPagoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\EmprendimientoUsuario\EmprendimientoUsuarioController;
use App\Http\Controllers\Api\ReservaApiController;
use App\Http\Controllers\Api\DetalleReservaApiController;
use App\Http\Controllers\PaymentController;


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


// Route::post('/create-token', [PaymentController::class, 'createToken']);

/* --- Catálogos públicos (solo lectura) --------------------------- */
Route::get('emprendimientos',          [EmprendimientoController::class, 'index']);
Route::get('emprendimientos/{id}',     [EmprendimientoController::class, 'show']);

Route::get('tipos-de-negocio',                 [TipoDeNegocioController::class, 'index']);
Route::get('tipos-de-negocio/{id}',            [TipoDeNegocioController::class, 'show']);
Route::get('tipos-de-negocio/{id}/emprendimientos',
                                             [TipoDeNegocioController::class, 'getEmprendimientosByTipo']);

Route::get('zonas-turisticas',         [ZonaTuristicaApiController::class, 'index']);
Route::get('zonas-turisticas/{id}',    [ZonaTuristicaApiController::class, 'show']);

Route::get('categorias-servicios',     [CategoriaServicioApiController::class, 'index']);
Route::get('categorias-servicios/{id}',[CategoriaServicioApiController::class, 'show']);

/* Productos y categorías (sólo index / show públicos) */
Route::apiResource('productos',            ProductoApiController::class)->only(['index','show']);
Route::apiResource('categorias-productos', CategoryProductsApiController::class)->only(['index','show']);

/* Servicios: lista y detalle */
Route::get('servicios',       [ServicioApiController::class, 'index']);
Route::get('servicios/{id}',  [ServicioApiController::class, 'show']);

/* =================================================================
|  2.  RUTAS PROTEGIDAS  (requieren token Sanctum)
|================================================================= */
Route::middleware('auth:sanctum')->group(function () {

      // Obtener todos los emprendimientos
    Route::get('/emprendimientos', [EmprendimientoController::class, 'index']);

    // Crear un nuevo emprendimiento
    Route::post('/emprendimientos', [EmprendimientoController::class, 'store']);

    // Obtener un emprendimiento específico
    Route::get('/emprendimientos/{id}', [EmprendimientoController::class, 'show']);

    // Actualizar un emprendimiento
    Route::put('/emprendimientos/{id}', [EmprendimientoController::class, 'update']);

    // Eliminar un emprendimiento
    Route::delete('/emprendimientos/{id}', [EmprendimientoController::class, 'destroy']);

    // Activar un emprendimiento pendiente
    Route::put('/emprendimientos/{id}/activar', [EmprendimientoController::class, 'activarEmprendimiento']);

    // Enviar solicitud para unirse a un emprendimiento
    Route::post('/emprendimientos/solicitudes', [EmprendimientoController::class, 'enviarSolicitud']);

    // Listar solicitudes pendientes para un emprendimiento (solo propietario/admin)
    Route::get('/emprendimientos/{id}/solicitudes-pendientes', [EmprendimientoController::class, 'listarSolicitudesPendientes']);

    // Aprobar o rechazar una solicitud
    Route::put('/emprendimientos/solicitudes/{solicitudId}/responder', [EmprendimientoController::class, 'responderSolicitud']);

    // Obtener todas las solicitudes de un usuario
    Route::get('/emprendimientos/solicitudes', [EmprendimientoController::class, 'solicitudesUsuario']);

    // Obtener el estado de la solicitud de emprendimiento
    Route::get('/emprendimientos/estado-solicitud', [EmprendimientoController::class, 'estadoSolicitudEmprendedor']);


    /* ------ Información del usuario y logout -------------------- */
    Route::post('auth/logout', function (Request $r) { return (new AuthController)->logout($r); });
    Route::get('user', fn(Request $r) => $r->user());
    Route::get   ('users/{id}',            [UserController::class, 'show']);

    Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);

    /* ------ Usuarios (Admin / Moderador) ------------------------ */
    Route::middleware('role:Administrador|Moderador')->group(function () {
        Route::get   ('users',                 [UserController::class, 'index']);
        Route::patch ('users/{id}/active',     [UserController::class, 'toggleActive']);
        Route::patch ('users/{id}/password',   [UserController::class, 'changePassword']);
    });

    /* ------ Gestión de catálogos por Administrador -------------- */
    Route::middleware('role:Administrador')->group(function () {
        Route::apiResource('tipos-de-negocio',          TipoDeNegocioController::class)->except(['index','show']);
        Route::apiResource('zonas-turisticas',          ZonaTuristicaApiController::class)->except(['index','show']);
        Route::apiResource('categorias-servicios',      CategoriaServicioApiController::class)->except(['index','show']);
        Route::apiResource('categorias-productos',      CategoryProductsApiController::class)->only(['store','update','destroy']);
    });

    /* ------ Productos creados por Emprendedor ------------------- */
    Route::middleware('role:Emprendedor')->group(function () {
        Route::apiResource('productos', ProductoApiController::class)->only(['store','update','destroy']);
    });

    /* ------ Relaciones Emprendimiento‑Usuario ------------------- */
    Route::middleware('role:Emprendedor')->group(function () {
        Route::post  ('emprendimientos-usuarios',        [EmprendimientoUsuarioController::class, 'store']);
        Route::put   ('emprendimientos-usuarios/{id}',   [EmprendimientoUsuarioController::class, 'update']);
        Route::delete('emprendimientos-usuarios/{id}',   [EmprendimientoUsuarioController::class, 'destroy']);
        Route::get   ('emprendimientos-usuarios/{id}',   [EmprendimientoUsuarioController::class, 'show']);
    });

    /* ------ Favoritos (Usuario) --------------------------------- */
    Route::get   ('favoritos',      [FavoritoController::class, 'index']);
    Route::post  ('favoritos',      [FavoritoController::class, 'store']);
    Route::delete('favoritos/{id}', [FavoritoController::class, 'destroy']);

    /* ------ Carrito (Usuario autenticado) ----------------------- */
    Route::controller(CarritoController::class)->group(function () {
        Route::get   ('carrito',            'index');
        Route::post  ('carrito',            'store');
        Route::put   ('carrito/{carrito}',  'update');
        Route::delete('carrito/{carrito}',  'destroy');
    });

    /* ------ Checkout / Ventas ----------------------------------- */
    Route::controller(VentaController::class)->group(function () {
        Route::post('/checkout', 'store');
        Route::post('/ventas/{venta}/pagar', 'procesarPago');
        Route::get('/compras', 'listarCompras'); // ← esta ruta es importante
    });


    /* ------ Reservas y detalles (cualquier auth) ---------------- */
    Route::apiResource('reservas',        ReservaApiController::class);
    Route::apiResource('detalle-reservas',DetalleReservaApiController::class);

    /* ------ Métodos de pago (Administrador) --------------------- */
    Route::prefix('metodos-pago')->middleware('role:Administrador')->group(function () {
        Route::get   ('/',            [MetodoPagoController::class, 'index']);
        Route::post  ('/',            [MetodoPagoController::class, 'store']);
        Route::get   ('{id}',         [MetodoPagoController::class, 'show']);
        Route::put   ('{id}',         [MetodoPagoController::class, 'update']);
        Route::delete('{id}',         [MetodoPagoController::class, 'destroy']);
        Route::patch ('{id}/suspend', [MetodoPagoController::class, 'suspend']);
        Route::patch ('{id}/activate',[MetodoPagoController::class, 'activate']);
    });

    /* -------------------------------------------------------------
    |  Panel Emprendedor  (saldo, movimientos y retiros)
    |-------------------------------------------------------------- */
    Route::middleware('role:Emprendedor')
          ->prefix('emprendimiento')
          ->controller(EmpRetiroController::class)
          ->group(function () {
              Route::get ('/saldo',           'saldo');
              Route::get ('/movimientos',     'movimientos');
              Route::post('/retiros',         'store');
              Route::get ('/retiros/{id}',    'show');
          });

    /* -------------------------------------------------------------
    |  Panel Administrador  (retiros y movimientos globales)
    |-------------------------------------------------------------- */
    Route::middleware('role:Administrador')
          ->prefix('admin')
          ->controller(AdminRetiroController::class)
          ->group(function () {
              Route::get ('/retiros',               'index');
              Route::post('/retiros/{id}/aprobar',  'aprobar');
              Route::post('/retiros/{id}/rechazar', 'rechazar');
              Route::get ('/movimientos',           'movimientos');
          });

    /*
|------------------------------------------------------------------
|  CHAT (WebSockets / Reverb)
|  Prefijo: /api/chat
|------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')
      ->prefix('chat')
      ->controller(ChatController::class)
      ->group(function () {

          // 1. Crear o recuperar una conversación
          //    POST /api/chat/abrir
          //    Body JSON:
          //    {
          //       "tipo"        : "usuario_emprendimiento" | "usuario_usuario" | "usuario_moderador",
          //       "destino_id"  : <id emprendimiento o usuario>
          //    }
          Route::post('/abrir', 'abrir');

          // 2. Obtener los mensajes de la conversación (paginados)
          //    GET /api/chat/{conversaciones_id}/mensajes?per_page=30
          Route::get('/{conversaciones_id}/mensajes', 'mensajes');

          // 3. Enviar un nuevo mensaje
          //    POST /api/chat/{conversaciones_id}/mensajes
          //    Body JSON: { "contenido": "Hola...", "imagen_url": null }
          Route::post('/{conversaciones_id}/mensajes', 'enviar');
      });
});

Route::prefix('emprendimiento')->middleware('auth:sanctum')->group(function () {

    // Obtener todos los emprendimientos
    Route::get('/', [EmprendimientoController::class, 'index']);

    // Crear un nuevo emprendimiento (solo usuarios autenticados)
    Route::post('/', [EmprendimientoController::class, 'store']);

    // Obtener los detalles de un emprendimiento específico
    Route::get('{id}', [EmprendimientoController::class, 'show']);

    // Actualizar un emprendimiento (solo usuarios autenticados)
    Route::put('{id}', [EmprendimientoController::class, 'update']);

    // Eliminar un emprendimiento (solo usuarios autenticados)
    Route::delete('{id}', [EmprendimientoController::class, 'destroy']);

    // Activar un emprendimiento pendiente y aprobar solicitud propietario
    Route::post('{id}/activar', [EmprendimientoController::class, 'activarEmprendimiento']);

    // Enviar solicitud para unirse a un emprendimiento existente (rol colaborador)
    Route::post('solicitud', [EmprendimientoController::class, 'enviarSolicitud']);

    // Listar solicitudes pendientes para un emprendimiento (solo propietario/admin)
    Route::get('{emprendimientoId}/solicitudes-pendientes', [EmprendimientoController::class, 'listarSolicitudesPendientes']);

    // Aprobar o rechazar solicitud
    Route::post('solicitud/{solicitudId}/respuesta', [EmprendimientoController::class, 'responderSolicitud']);

    // Mostrar todas las solicitudes de un usuario
    Route::get('mis-solicitudes', [EmprendimientoController::class, 'solicitudesUsuario']);

    // Ver el estado de la solicitud de emprendimiento del usuario
    Route::get('estado-solicitud', [EmprendimientoController::class, 'estadoSolicitudEmprendedor']);
});
