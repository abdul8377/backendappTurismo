<?php

namespace App\Http\Controllers\ApiEmprendedor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use App\Models\CategoriaProducto;
use Illuminate\Http\JsonResponse;

class ApiCategoriaController extends Controller
{
    /**
     * Retorna todas las categorías de servicios y productos.
     */
    public function index(): JsonResponse
    {
        $categoriasServicios = CategoriaServicio::all();
        $categoriasProductos = CategoriaProducto::all();

        return response()->json([
            'servicios' => $categoriasServicios,
            'productos' => $categoriasProductos
        ], 200);
    }

    /**
     * Retorna los productos de una categoría producto por su ID.
     */
    public function productosPorCategoria(int $id): JsonResponse
    {
        $categoria = CategoriaProducto::with('productos')->find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría de producto no encontrada'], 404);
        }

        return response()->json([
            'categoria' => $categoria->nombre,
            'productos' => $categoria->productos
        ], 200);
    }

    /**
     * Retorna los servicios de una categoría servicio por su ID.
     */
    public function serviciosPorCategoria(int $id): JsonResponse
    {
        $categoria = CategoriaServicio::with('servicios')->find($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría de servicio no encontrada'], 404);
        }

        return response()->json([
            'categoria' => $categoria->nombre,
            'servicios' => $categoria->servicios
        ], 200);
    }
}
