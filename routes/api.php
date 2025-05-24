<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaServicioApiController;
use App\Http\Controllers\Api\ImageableController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ZonaTuristicaApiController;
use App\Http\Controllers\EmprendimientoUsuario\EmprendimientoUsuarioController;
use App\Http\Controllers\Api\TipoDeNegocioController;
use App\Http\Controllers\ApiEmprendedor\ApiCategoriaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('check-email', [AuthController::class, 'checkEmail']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('/password/email', [AuthController::class, 'sendResetPasswordEmail']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Ruta para obtener el usuario autenticado
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




    Route::get('users', [UserController::class, 'index']);
    Route::patch('users/{id}/active', [UserController::class, 'toggleActive']);
    Route::patch('users/{id}/password', [UserController::class, 'changePassword']);




    Route::get('/emprendimientos', [EmprendimientoUsuarioController::class, 'index']);
    Route::post('/emprendimientos', [EmprendimientoUsuarioController::class, 'store']);
    Route::get('/emprendimientos/{id}', [EmprendimientoUsuarioController::class, 'show']);
    Route::put('/emprendimientos/{id}', [EmprendimientoUsuarioController::class, 'update']);
    Route::delete('/emprendimientos/{id}', [EmprendimientoUsuarioController::class, 'destroy']);

    // Route::get('/tipos-negocio', [TipoDeNegocioController::class, 'index']);
    // Route::post('/tipos-negocio', [TipoDeNegocioController::class, 'store']);
    // Route::get('/tipos-negocio/{id}', [TipoDeNegocioController::class, 'show']);
    // Route::put('/tipos-negocio/{id}', [TipoDeNegocioController::class, 'update']);
    // Route::delete('/tipos-negocio/{id}', [TipoDeNegocioController::class, 'destroy']);

    // Categorías de servicios
    Route::apiResource('categorias-servicios', CategoriaServicioApiController::class);

    // Rutas zonas turísticas
    Route::get('/zonas-turisticas', [ZonaTuristicaApiController::class, 'index']);
    Route::post('/zonas-turisticas', [ZonaTuristicaApiController::class, 'store']);
    Route::get('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'show']);
    Route::put('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'update']);
    Route::delete('/zonas-turisticas/{id}', [ZonaTuristicaApiController::class, 'destroy']);

// Rutas para imágenes (se podrían gestionar como una API separada si es necesario)
// Route::post('/imagenes', [ImageableController::class, 'store']);
// Route::get('/imagenes/{type}/{id}', [ImageableController::class, 'index']);


// Listar todos los tipos de negocio
Route::get('tipos-de-negocio', [TipoDeNegocioController::class, 'index']);

// Ver un tipo de negocio específico (y los emprendimientos vinculados)
Route::get('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'show']);

// Crear un nuevo tipo de negocio
Route::post('tipos-de-negocio', [TipoDeNegocioController::class, 'store']);

// Editar un tipo de negocio existente
Route::put('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'update']);

// Eliminar un tipo de negocio (si no tiene emprendimientos vinculados)
Route::delete('tipos-de-negocio/{id}', [TipoDeNegocioController::class, 'destroy']);

// Obtener los emprendimientos vinculados a un tipo de negocio específico
Route::get('tipos-de-negocio/{id}/emprendimientos', [TipoDeNegocioController::class, 'getEmprendimientosByTipo']);

Route::get('/categorias/combinadas', [ApiCategoriaController::class, 'index']);

Route::get('/categorias-productos/{id}/productos', [ApiCategoriaController::class, 'productosPorCategoria']);

Route::get('/categorias-servicios/{id}/servicios', [ApiCategoriaController::class, 'serviciosPorCategoria']);
