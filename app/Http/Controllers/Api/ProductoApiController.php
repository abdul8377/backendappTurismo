<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Images;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoApiController extends Controller
{
    public function index(Request $request)
{

    $user = $request->user();

    if ($user && $user->hasRole('Emprendedor')) {
        $ids = DB::table('emprendimiento_usuarios')
            ->where('users_id', $user->id)
            ->pluck('emprendimientos_id');

        $productos = Producto::with('images')
            ->whereIn('emprendimientos_id', $ids)
            ->get();

        return response()->json($productos, Response::HTTP_OK);
    }


    if ($user && $user->hasRole('Administrador')) {
        $productos = Producto::with('images')->get();
        return response()->json($productos, Response::HTTP_OK);
    }

    $productos = Producto::with('images')
        ->where('estado', 'activo')
        ->get();

    return response()->json($productos, Response::HTTP_OK);
    }

    public function store(Request $request)
{

    $validator = Validator::make($request->all(), [
        'categorias_productos_id' => 'required|integer|exists:categorias_productos,categorias_productos_id',
        'nombre'                  => 'required|string|max:150',
        'descripcion'             => 'nullable|string',
        'precio'                  => 'required|numeric|min:0',
        'stock'                   => 'required|integer|min:0',
        'estado'                  => 'nullable|in:activo,inactivo',
        'imagenes.*'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }


    $data = $validator->validated();


    $user = $request->user();
    $emprId = DB::table('emprendimiento_usuarios')
        ->where('users_id', $user->id)
        ->value('emprendimientos_id');

    if (! $emprId) {
        return response()->json(['error' => 'El usuario no tiene un emprendimiento asignado'], 403);
    }
    $data['emprendimientos_id'] = $emprId;

    $producto = Producto::create($data);

    if ($request->hasFile('imagenes')) {
        foreach ($request->file('imagenes') as $file) {
            $path = $file->store("productos/{$producto->getKey()}", 'public');

            $img = Images::create([
                'url'    => $path,
                'titulo' => "{$producto->nombre} (Imagen)",
            ]);

            DB::table('imageables')->insert([
                'images_id'      => $img->id,
                'imageable_id'   => $producto->getKey(),
                'imageable_type' => Producto::class,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    // 6) Devuelve la respuesta
    $producto->load(['categoria', 'images']);
    return response()->json([
        'message'  => 'producto creado correctamente',
        'producto' => $producto
    ], Response::HTTP_CREATED);
}


    public function show($id)
    {
        $prod = Producto::with('images')->findOrFail($id);
        return response()->json($prod, Response::HTTP_OK);
    }


    public function update(Request $request, $id)

    {
        $producto = Producto::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion'     => 'nullable|string',
            'precio'          => 'sometimes|required|numeric|min:0',
            'stock'           => 'sometimes|required|integer|min:0',
            'estado'          => 'nullable|in:activo,inactivo',
            'imagenes.*'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'categorias_productos_id' => 'sometimes|required|integer|exists:categorias_productos,categorias_productos_id',
        ]);

         if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $data = $validator->validated();
        unset($data['imagenes']);



            // 1) Si vienen imágenes nuevas, primero elimino las antiguas
        if ($request->hasFile('imagenes')) {
            // borro archivos y registros de imágenes
            foreach ($producto->images as $img) {
                Storage::disk('public')->delete($img->url);
                $img->delete();
            }
            // limpio pivot
            $producto->images()->detach();
        }

        // 2) Actualizo datos básicos
        $producto->update($data);

        // 3) Re-agrego las nuevas
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $path = $file->store("productos/{$producto->getKey()}", 'public');
                $img  = Images::create([
                    'url'    => $path,
                    'titulo' => $producto->nombre . ' (Imagen)',
                ]);
                DB::table('imageables')->insert([
                    'images_id'      => $img->id,
                    'imageable_id'   => $producto->getKey(),
                    'imageable_type' => Producto::class,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        $producto->load(['categoria', 'images']);
        return response()->json([
            'message'  => 'producto actualizado correctamente',
            'producto' => $producto
        ], Response::HTTP_OK);
    }


    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        foreach ($producto->images as $img) {
            Storage::disk('public')->delete($img->url);
            $img->delete();
        }

        $producto->delete();

        return response()->json([
            'message' => 'producto eliminado correctamente'
        ], Response::HTTP_OK);
    }
}
