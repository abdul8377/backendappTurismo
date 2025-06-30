<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZonaTuristica;
use App\Models\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
class ZonaTuristicaApiController extends Controller
{

    public function index()
    {
        $zonas = ZonaTuristica::with('images')->get();
        return response()->json($zonas, Response::HTTP_OK);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'ubicacion'   => 'nullable|string|max:255',
            'imagen'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado'      => 'in:activo,inactivo',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }

        $data = $validator->validated();



        $zona = ZonaTuristica::create($data);

        if ($request->hasFile('imagen')) {
            foreach($request->file('imagen') as $file){
                $path = $file->store("ZonasTuristicas/{$zona->getKey()}", 'public');

                $imagen = Images::create([
                    'url'    => $path,
                    'titulo' => $zona->nombre,
                ]);

                DB::table('imageables')->insert([
                    'images_id'      => $imagen->id,
                    'imageable_id'   => $zona->zonas_turisticas_id,
                    'imageable_type' => ZonaTuristica::class,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
        $zona->load('images');
        return response()->json([
            'message' => 'Zona turística creada correctamente',
            'zona'    => $zona->load('images')
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $zona = ZonaTuristica::with('images')->findOrFail($id);
        return response()->json($zona, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $zona = ZonaTuristica::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre'      => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'ubicacion'   => 'nullable|string|max:255',
            'estado'      => 'in:activo,inactivo',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'imagen_a_eliminar' => 'nullable|array',
            'imagen_a_eliminar.*' => 'integer|exists:images,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }

        $data = $validator->validated();
        unset($data['imagen']);
        $zona->update($data);

        if ($request->hasFile('imagen')) {
            foreach ($request->file('imagen') as $file){
            $path = $file->store("zonasTuriticas/{$zona->getKey()}", 'public');

                $imagen = Images::create([
                    'url'    => $path,
                    'titulo' => $zona->nombre,
                ]);
                DB::table('imageables')->insert([
                    'images_id'      => $imagen->id,
                    'imageable_id'   => $zona->zonas_turisticas_id,
                    'imageable_type' => ZonaTuristica::class,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        if ($request->filled('imagen_a_eliminar')) {
            foreach ($request->input('imagen_a_eliminar') as $imgId) {
                $img = Images::find($imgId);
                if ($img) {
                    Storage::disk('public')->delete($img->url);
                    DB::table('imageables')->where('images_id', $img->id)->delete();
                    $img->delete();
                }
            }
        }

        $zona->load(['images']);
        return response()->json([
            'message'  => 'zona turitica actualizada correctamente',
            'zona' => $zona
        ], Response::HTTP_OK);
    }

    // 5. Eliminar
    public function destroy($id)
    {
        $zona = ZonaTuristica::findOrFail($id);

        foreach ($zona->images as $img) {
            Storage::disk('public')->delete($img->url);
            DB::table('imageables')->where('images_id', $img->id)->delete();
            $img->delete();
        }

        $zona->delete();

        return response()->json([
            'message' => 'zona turitica eliminada correctamente'
        ], Response::HTTP_OK);

    }
}
