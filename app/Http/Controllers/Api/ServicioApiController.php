<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Images;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ServicioApiController extends Controller
{
    public function index(Request $request)
    {

        $query = Servicio::with(['emprendimiento', 'images']);


        $user = $request->user();
        if ($user) {

            $pivot = DB::table('emprendimiento_usuarios')
                ->where('users_id', $user->id)
                ->first();

            if ($pivot && $pivot->emprendimientos_id) {
                // Filtra sólo sus servicios
                $query->where('emprendimientos_id', $pivot->emprendimientos_id);
            }
        } else {

            if ($request->filled('tipo_negocio_id')) {
                $tipoNegocioId = $request->input('tipo_negocio_id');
                $query->whereHas('emprendimiento', function ($q) use ($tipoNegocioId) {
                    $q->where('tipo_negocio_id', $tipoNegocioId);
                });
            }
        }

        // 4) Ejecuta y devuelve
        $servicios = $query->get();
        return response()->json($servicios, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'                  => 'required|string|max:150',
            'descripcion'             => 'nullable|string',
            'precio'                  => 'required|numeric|min:0',
            'capacidad_maxima'        => 'required|integer|min:1',
            'duracion_servicio'       => 'nullable|string',
            'estado'                  => 'nullable|in:activo,inactivo',
            'imagenes.*'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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

        $servicio = Servicio::create($data);

        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {

                $path = $file->store("servicios/{$servicio->getKey()}", 'public');


                $img = Images::create([
                    'url'    => $path,
                    'titulo' => $servicio->nombre . ' (Imagen)',
                ]);

                // Insertar en pivote `imageables`
                DB::table('imageables')->insert([
                    'images_id'      => $img->id,
                    'imageable_id'   => $servicio->getKey(),
                    'imageable_type' => Servicio::class,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        // 3) Precargamos relaciones y devolvemos
        $servicio->load(['emprendimiento', 'images']);
        return response()->json([
            'message'  => 'Servicio creado correctamente',
            'servicio' => $servicio
        ], Response::HTTP_CREATED);
    }

    /**
     * GET /api/servicios/{id}
     * Muestra un servicio específico
     */
    public function show($id)
    {
        $servicio = Servicio::with(['emprendimiento', 'images'])
            ->findOrFail($id);

        return response()->json($servicio, Response::HTTP_OK);
    }

    /**
     * PUT /api/servicios/{id}
     * Actualiza un servicio y, opcionalmente, agrega nuevas imágenes
     */
    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'emprendimientos_id'      => 'sometimes|required|integer|exists:emprendimientos,emprendimientos_id',
            'nombre'                  => 'sometimes|required|string|max:150',
            'descripcion'             => 'nullable|string',
            'precio'                  => 'sometimes|required|numeric|min:0',
            'capacidad_maxima'        => 'sometimes|required|integer|min:1',
            'duracion_servicio'       => 'nullable|string',
            'estado'                  => 'nullable|in:activo,inactivo',
            'imagenes.*'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'imagenes_a_eliminar' => 'nullable|array',
            'imagenes_a_eliminar.*' => 'integer|exists:images,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $data = $validator->validated();
        unset($data['imagenes']);
        $servicio->update($data);


        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $path = $file->store("servicios/{$servicio->getKey()}", 'public');
                $img  = Images::create([
                    'url'    => $path,
                    'titulo' => $servicio->nombre . ' (Imagen)',
                ]);
                DB::table('imageables')->insert([
                    'images_id'      => $img->id,
                    'imageable_id'   => $servicio->getKey(),
                    'imageable_type' => Servicio::class,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
        if ($request->filled('imagenes_a_eliminar')) {
            foreach ($request->input('imagenes_a_eliminar') as $imgId) {
                $img = Images::find($imgId);
                if ($img) {
                    // Borra archivo físico
                    Storage::disk('public')->delete($img->url);

                    // Borra pivote y la imagen
                    DB::table('imageables')->where('images_id', $img->id)->delete();
                    $img->delete();
                }
            }
        }


        $servicio->load(['emprendimiento', 'images']);
        return response()->json([
            'message'  => 'Servicio actualizado correctamente',
            'servicio' => $servicio
        ], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);

        foreach ($servicio->images as $img) {
            Storage::disk('public')->delete($img->url);
            $img->delete();
        }

        $servicio->delete();

        return response()->json([
            'message' => 'Servicio eliminado correctamente'
        ], Response::HTTP_OK);
    }

}
