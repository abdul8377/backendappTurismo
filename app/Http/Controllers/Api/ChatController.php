<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{Conversacion, Mensaje, User, Emprendimiento};

class ChatController extends Controller
{
    /* ================================================================
     | 1.  Crear (o reutilizar) una conversación
     |================================================================ */
    public function abrir(Request $request)
    {
        $request->validate([
            'tipo' => [
                'required',
                Rule::in([
                    'usuario_emprendimiento',
                    'usuario_usuario',
                    'usuario_moderador',
                ]),
            ],
            'destino_id' => 'required|integer|min:1',
        ]);

        $user       = $request->user();
        $tipo       = $request->input('tipo');
        $destinoId  = (int) $request->input('destino_id');

        $conversacion = match ($tipo) {
            /* 1‑A  Usuario  ⇄  Emprendimiento  */
            'usuario_emprendimiento' => Conversacion::firstOrCreate(
                [
                    'users_id'           => $user->id,
                    'emprendimientos_id' => $destinoId,
                    'tipo'               => $tipo,
                ]
            ),

            /* 1‑B  Usuario  ⇄  Usuario  */
            'usuario_usuario' => Conversacion::entreUsuarios($user->id, $destinoId)
                                ->first()
                                ?? Conversacion::create([
                                       'users_id'              => $user->id,
                                       'destinatario_users_id' => $destinoId,
                                       'tipo'                  => $tipo,
                                   ]),

            /* 1‑C  Usuario  ⇄  Moderador (aleatorio) */
            'usuario_moderador' => $this->abrirConModerador($user),

            default => null
        };

        return response()->json($conversacion, 201);
    }

    /* ================================================================
     | 2.  Enviar mensaje a la conversación
     |================================================================ */
    public function enviar(Request $request, int $conversaciones_id)
    {
        $request->validate([
            'contenido'   => 'required_without:imagen_url|string',
            'imagen_url'  => 'nullable|string',
        ]);

        $conv = Conversacion::findOrFail($conversaciones_id);
        $user = $request->user();

        // Autorización básica: el usuario debe pertenecer a la conversación
        abort_unless(
            $this->usuarioTieneAcceso($conv, $user->id),
            403,
            'No autorizado en esta conversación.'
        );

        // Determinar emisor según tipo
        $emisor = $conv->tipo === 'usuario_emprendimiento' && $user->id !== $conv->users_id
                  ? 'emprendimiento'
                  : 'usuario';

        $mensaje = $conv->mensajes()->create([
            'emisor'      => $emisor,
            'contenido'   => $request->input('contenido'),
            'imagen_url'  => $request->input('imagen_url'),
        ]);

        return response()->json($mensaje, 201);
    }

    /* ================================================================
     | 3.  Listar mensajes (paginados)
     |================================================================ */
    public function mensajes(Request $request, int $conversaciones_id)
    {
        $conv = Conversacion::findOrFail($conversaciones_id);
        $user = $request->user();

        abort_unless(
            $this->usuarioTieneAcceso($conv, $user->id),
            403,
            'No autorizado en esta conversación.'
        );

        $mensajes = $conv->mensajes()
                         ->orderBy('mensajes_id', 'asc')
                         ->paginate($request->query('per_page', 30));

        return response()->json($mensajes);
    }

    /* ================================================================
     |  Helpers
     |================================================================ */
    /** Selecciona aleatoriamente un moderador y abre conversación */
    private function abrirConModerador(User $usuario): Conversacion
    {
        $moderador = User::role('Moderador')
                         ->inRandomOrder()
                         ->firstOrFail();

        return Conversacion::entreUsuarios($usuario->id, $moderador->id)->first()
               ?? Conversacion::create([
                       'users_id'              => $usuario->id,
                       'destinatario_users_id' => $moderador->id,
                       'tipo'                  => 'usuario_moderador',
                  ]);
    }

    /** Verifica si el usuario forma parte de la conversación */
    private function usuarioTieneAcceso(Conversacion $conv, int $userId): bool
    {
        return in_array(
            $userId,
            [$conv->users_id, $conv->destinatario_users_id]
        );
    }
}
