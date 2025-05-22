<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServicio;
use App\Models\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaServicioApiController extends Controller
{
    // Mostrar la lista de categorías de servicio
    public function index()
    {
        try {
            $categorias = CategoriaServicio::with(['images', 'servicios'])
                ->withCount('servicios')
                ->get();
            return response()->json($categorias, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Error al obtener las categorías de servicio',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // Mostrar los detalles de una categoría de servicio
    public function show($id)
    {
        try {
            $categoria = CategoriaServicio::with('servicios')->findOrFail($id);
            return response()->json($categoria, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Categoría de servicio no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la categoría de servicio', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'icono' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Crear la categoría
            $categoria = CategoriaServicio::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
            ]);

            // Guarda la imagen si está presente
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('categorias', 'public');

                $imagen = Images::create([
                    'url' => $imagenPath,
                    'titulo' => $categoria->nombre,
                ]);

                // Asocia la imagen a la categoría en la tabla imageable
                DB::table('imageables')->insert([
                    'images_id' => $imagen->id,
                    'imageable_id' => $categoria->categorias_servicios_id,
                    'imageable_type' => CategoriaServicio::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Guarda el icono si está presente
            if ($request->hasFile('icono')) {
                $iconoPath = $request->file('icono')->store('icons', 'public');

                $icono = Images::create([
                    'url' => $iconoPath,
                    'titulo' => $categoria->nombre . ' (Icono)',
                ]);

                // Asocia el icono a la categoría en la tabla imageable
                DB::table('imageables')->insert([
                    'images_id' => $icono->id,
                    'imageable_id' => $categoria->categorias_servicios_id,
                    'imageable_type' => CategoriaServicio::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json($categoria, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la categoría de servicio', 'message' => $e->getMessage()], 500);
        }
    }

    // Actualizar la categoría de servicio
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'icono' => 'nullable|string',
            ]);

            $categoria = CategoriaServicio::findOrFail($id);
            $categoria->update($validated);

            return response()->json($categoria, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Categoría de servicio no encontrada'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Error de validación', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar la categoría de servicio', 'message' => $e->getMessage()], 500);
        }
    }

    // Eliminar una categoría de servicio
    public function destroy($id)
    {
        try {
            $categoria = CategoriaServicio::findOrFail($id);
            $categoria->delete();

            return response()->json(['message' => 'Categoría de servicio eliminada correctamente'], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Categoría de servicio no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la categoría de servicio', 'message' => $e->getMessage()], 500);
        }
    }
}
