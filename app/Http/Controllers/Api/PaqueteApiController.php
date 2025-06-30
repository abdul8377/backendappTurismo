<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paquete;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;


class PaqueteApiController extends Controller
{
    public function index(Request $request)
    {
        // Base: eager‐loading de servicios e imágenes
        $query = Paquete::with(['servicios.images', 'emprendimiento']);

        // Si llega un usuario autenticado con rol Emprendedor, lo filtramos
        if ($user = $request->user()) {
            if ($user->hasRole('Emprendedor')) {
                // Obtenemos directamente el emprendimiento asignado al usuario
                $emprId = DB::table('emprendimiento_usuarios')
                    ->where('users_id', $user->id)
                    ->value('emprendimientos_id');

                // Si existe, limitamos la consulta a ese emprendimiento
                if ($emprId) {
                    $query->where('emprendimientos_id', $emprId);
                }
            }
        }

        // Ejecutar y devolver
        $paquetes = $query->get();
        return response()->json($paquetes, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'       => 'required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'estado'       => 'required|in:activo,inactivo',
            'servicios'    => 'required|array',
            'servicios.*'  => 'integer|exists:servicios,servicios_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // 1) Buscar el emprendimiento del usuario
        $user   = $request->user();
        $emprId = DB::table('emprendimiento_usuarios')
            ->where('users_id', $user->id)
            ->value('emprendimientos_id');

        if (! $emprId) {
            return response()->json(['error' => 'El usuario no tiene un emprendimiento asignado'], 403);
        }

        // 2) Crear el paquete usando directamente $emprId
        $paquete = Paquete::create([
            'emprendimientos_id' => $emprId,
            'nombre'             => $data['nombre'],
            'descripcion'        => $data['descripcion'] ?? null,
            'precio_total'       => $data['precio_total'],
            'estado'             => $data['estado'],
        ]);

        // 3) Asociar servicios
        $paquete->servicios()->sync($data['servicios']);

        // 4) Cargar y devolver
        $paquete->load('servicios.images');
        return response()->json($paquete, Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $paquete = Paquete::with(['servicios.images'])
            ->findOrFail($id);

        return response()->json($paquete, Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $paquete = Paquete::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre'       => 'sometimes|required|string|max:255',
            'descripcion'  => 'nullable|string',
            'precio_total' => 'sometimes|required|numeric|min:0',      // puede cambiar el precio
            'estado'       => 'sometimes|required|in:activo,inactivo',
            'servicios'    => 'sometimes|required|array',
            'servicios.*'  => 'integer|exists:servicios,servicios_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $paquete->update([
            'nombre'       => $data['nombre']       ?? $paquete->nombre,
            'descripcion'  => $data['descripcion']  ?? $paquete->descripcion,
            'precio_total' => $data['precio_total'] ?? $paquete->precio_total,
            'estado'       => $data['estado']       ?? $paquete->estado,
        ]);

        if (isset($data['servicios'])) {
            $paquete->servicios()->sync($data['servicios']);
        }

        $paquete->load(['servicios.images']);

        return response()->json($paquete, Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $paquete = Paquete::findOrFail($id);

        $paquete->servicios()->detach();
        $paquete->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }







    // 1) Listar los servicios asociados a un paquete
    public function listServicios($paqueteId)
    {
        $paquete = Paquete::with('servicios.images')->findOrFail($paqueteId);
        return response()->json($paquete->servicios, Response::HTTP_OK);
    }

    // 2) Agregar un servicio a un paquete
    public function addServicio(Request $request, $paqueteId)
    {
        $validator = Validator::make($request->all(), [
            'servicio_id' => 'required|integer|exists:servicios,servicios_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $paquete = Paquete::findOrFail($paqueteId);
        $servicioId = $request->input('servicio_id');

        // Evitamos duplicados
        if (! $paquete->servicios->contains($servicioId)) {
            $paquete->servicios()->attach($servicioId);
        }

        // Devolvemos la lista actualizada
        $paquete->load('servicios.images');
        return response()->json($paquete->servicios, Response::HTTP_OK);
    }

    // 3) Eliminar un servicio de un paquete
    public function removeServicio($paqueteId, $servicioId)
    {
        $paquete = Paquete::findOrFail($paqueteId);

        // Si está asociado, lo quitamos
        if ($paquete->servicios->contains($servicioId)) {
            $paquete->servicios()->detach($servicioId);
        }


        $paquete->load('servicios.images');
        return response()->json($paquete->servicios, Response::HTTP_OK);
    }
}
