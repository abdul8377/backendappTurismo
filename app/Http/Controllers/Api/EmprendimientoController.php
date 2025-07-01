<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emprendimiento;
use App\Models\Images;
use App\Models\SolicitudEmprendimiento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmprendimientoController extends Controller
{
    // Obtener todos los emprendimientos
    public function index()
    {
        $list = Emprendimiento::with(['tipoDeNegocio', 'images'])->get();
        return response()->json($list, Response::HTTP_OK);
    }

    // Crear nuevo emprendimiento con estado pendiente + crear solicitud automática rol propietario
    public function store(Request $request)
    {
        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'tipo_negocio_id' => 'required|integer|exists:tipos_de_negocio,id',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'estado' => 'in:activo,inactivo,pendiente',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Crear el emprendimiento
        $data = $validator->validated();
        unset($data['imagenes']);
        $data['fecha_registro'] = now();
        $empr = Emprendimiento::create($data);

        // Subir imágenes si las hay
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                // Guardar en disco
                $path = $file->store("emprendimientos/{$empr->getKey()}", 'public');

                // Crear el registro en "images"
                $img = Images::create([
                    'url' => $path,
                    'titulo' => $empr->nombre . ' (Imagen)',
                ]);

                // Insertar en la tabla pivot "imageables"
                DB::table('imageables')->insert([
                    'images_id' => $img->id,
                    'imageable_id' => $empr->getKey(),
                    'imageable_type' => Emprendimiento::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Crear la solicitud para el rol propietario
        /** @var \App\Models\User $user */
        $user = Auth::user();

        DB::transaction(function () use ($request, $user, $empr) {
            SolicitudEmprendimiento::create([
                'emprendimientos_id' => $empr->emprendimientos_id,
                'users_id' => $user->id,
                'rol_solicitado' => 'propietario',
                'estado' => 'pendiente',
                'fecha_solicitud' => now(),
            ]);
        });

        // Cargar relaciones
        $empr->load(['tipoDeNegocio', 'images']);
        return response()->json([
            'message' => 'Emprendimiento creado correctamente',
            'emprendimiento' => $empr
        ], Response::HTTP_CREATED);
    }

    // Obtener detalles de un emprendimiento específico
    public function show($id)
    {
        $empr = Emprendimiento::with(['tipoDeNegocio', 'images'])->findOrFail($id);
        return response()->json($empr, Response::HTTP_OK);
    }

    // Actualizar un emprendimiento
    public function update(Request $request, $id)
    {
        $empr = Emprendimiento::findOrFail($id);

        $v = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_negocio_id' => 'nullable|integer|exists:tipos_de_negocio,id',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'in:activo,inactivo,pendiente',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        // Actualizar datos del emprendimiento
        $data = $v->validated();
        unset($data['imagenes']);
        $empr->update($data);

        // Subir imágenes si las hay
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $path = $file->store("emprendimientos/{$empr->getKey()}", 'public');
                $img = Images::create([
                    'url' => $path,
                    'titulo' => $empr->nombre . ' (Imagen)',
                ]);
                DB::table('imageables')->insert([
                    'images_id' => $img->id,
                    'imageable_id' => $empr->getKey(),
                    'imageable_type' => Emprendimiento::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Recargar relaciones
        $empr->load(['tipoDeNegocio', 'images']);
        return response()->json([
            'message' => 'Emprendimiento actualizado correctamente',
            'emprendimiento' => $empr
        ], Response::HTTP_OK);
    }

    // Eliminar un emprendimiento
    public function destroy($id)
    {
        $empr = Emprendimiento::findOrFail($id);

        // Eliminar imágenes asociadas
        foreach ($empr->images as $img) {
            Storage::disk('public')->delete($img->url);
            $img->delete();
        }

        // Eliminar el emprendimiento
        $empr->delete();

        return response()->json([
            'message' => 'Emprendimiento eliminado correctamente'
        ], Response::HTTP_OK);
    }

    // Activar emprendimiento pendiente y aprobar solicitud propietario automáticamente
    public function activarEmprendimiento($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $emprendimiento = Emprendimiento::findOrFail($id);

        if ($emprendimiento->estado !== 'pendiente') {
            return response()->json(['message' => 'Emprendimiento ya está activo o no es pendiente'], 400);
        }

        DB::transaction(function () use ($emprendimiento, $user) {
            // Cambiar estado a activo
            $emprendimiento->estado = 'activo';
            $emprendimiento->save();

            // Buscar la solicitud pendiente rol propietario
            $solicitud = SolicitudEmprendimiento::where('emprendimientos_id', $emprendimiento->emprendimientos_id)
                ->where('users_id', $user->id)
                ->where('rol_solicitado', 'propietario')
                ->where('estado', 'pendiente')
                ->first();

            if ($solicitud) {
                // Crear relación en pivot como propietario
                $emprendimiento->usuarios()->attach($user->id, [
                    'rol_emprendimiento' => 'propietario',
                    'fecha_asignacion' => now(),
                ]);

                // Cambiar rol global a Emprendedor si no tiene
                if (!$user->hasRole('Emprendedor')) {
                    $user->syncRoles(['Emprendedor']);
                }

                // Actualizar solicitud a aprobada
                $solicitud->estado = 'aprobada';
                $solicitud->fecha_respuesta = now();
                $solicitud->save();
            }
        });

        return response()->json([
            'message' => 'Emprendimiento activado, solicitud aprobada y usuario asignado como propietario',
            'data' => $emprendimiento->load('usuarios'),
        ]);
    }

    // Enviar solicitud para unirse a un emprendimiento existente (rol colaborador)
    public function enviarSolicitud(Request $request)
    {
        $request->validate([
            'codigo_unico' => 'required|string|size:6|exists:emprendimientos,codigo_unico',
            'rol_solicitado' => 'required|in:colaborador,propietario', // Solo colaborador o propietario
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $emprendimiento = Emprendimiento::where('codigo_unico', $request->codigo_unico)
            ->where('estado', 'activo')
            ->firstOrFail();

        // Verificar si el usuario ya pertenece al emprendimiento
        if ($emprendimiento->usuarios()->where('users_id', $user->id)->exists()) {
            return response()->json(['message' => 'Ya perteneces a este emprendimiento'], 400);
        }

        // Verificar si ya tiene solicitud pendiente
        $existeSolicitud = SolicitudEmprendimiento::where('users_id', $user->id)
            ->where('emprendimientos_id', $emprendimiento->emprendimientos_id)
            ->where('estado', 'pendiente')
            ->exists();

        if ($existeSolicitud) {
            return response()->json(['message' => 'Ya tienes una solicitud pendiente para este emprendimiento'], 400);
        }

        $solicitud = SolicitudEmprendimiento::create([
            'emprendimientos_id' => $emprendimiento->emprendimientos_id,
            'users_id' => $user->id,
            'rol_solicitado' => $request->rol_solicitado,
            'estado' => 'pendiente',
            'fecha_solicitud' => now(),
        ]);

        return response()->json([
            'message' => 'Solicitud enviada, esperando aprobación',
            'data' => $solicitud,
        ]);
    }

    // Listar solicitudes pendientes para un emprendimiento (solo propietario/admin)
    public function listarSolicitudesPendientes($emprendimientoId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $emprendimiento = Emprendimiento::findOrFail($emprendimientoId);

        // Validar que el usuario sea propietario o admin
        $esPropietario = $emprendimiento->usuarios()
            ->wherePivot('users_id', $user->id)
            ->wherePivot('rol_emprendimiento', 'propietario')
            ->exists();

        if (!$esPropietario && !$user->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $solicitudes = SolicitudEmprendimiento::where('emprendimientos_id', $emprendimientoId)
            ->where('estado', 'pendiente')
            ->with('usuario')
            ->get();

        return response()->json(['data' => $solicitudes]);
    }

    // Aprobar o rechazar solicitud
    public function responderSolicitud(Request $request, $solicitudId)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $solicitud = SolicitudEmprendimiento::findOrFail($solicitudId);
        $emprendimiento = $solicitud->emprendimiento;

        // Validar permisos propietario/admin
        $esPropietario = $emprendimiento->usuarios()
            ->wherePivot('users_id', $user->id)
            ->wherePivot('rol_emprendimiento', 'propietario')
            ->exists();

        if (!$esPropietario && !$user->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($solicitud->estado !== 'pendiente') {
            return response()->json(['message' => 'Solicitud ya procesada'], 400);
        }

        DB::transaction(function () use ($solicitud, $request, $emprendimiento) {
            if ($request->accion === 'aprobar') {
                // Crear relación en pivot
                $emprendimiento->usuarios()->attach($solicitud->users_id, [
                    'rol_emprendimiento' => $solicitud->rol_solicitado,
                    'fecha_asignacion' => now(),
                ]);

                // Cambiar rol global del usuario
                $usuario = $solicitud->usuario;
                if (!$usuario->hasRole('Emprendedor')) {
                    $usuario->syncRoles(['Emprendedor']);
                }

                $solicitud->estado = 'aprobada';
                $solicitud->save();
            } else {
                // Si rechaza, eliminar solicitud
                $solicitud->delete();
            }
        });

        return response()->json([
            'message' => 'Solicitud ' . ($request->accion === 'aprobar' ? 'aprobada' : 'rechazada'),
        ]);
    }

    // Mostrar solicitudes del usuario
    public function solicitudesUsuario()
    {
        $user = Auth::user();

        $solicitudes = SolicitudEmprendimiento::where('users_id', $user->id)
            ->with('emprendimiento')
            ->get();

        return response()->json(['data' => $solicitudes]);
    }
}
