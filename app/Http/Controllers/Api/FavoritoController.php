<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorito;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    /**
     * Obtener todos los favoritos del usuario autenticado.
     */
    public function index()
    {
        $user = Auth::user();

        $favoritos = Favorito::where('users_id', $user->id)->get();

        return response()->json($favoritos);
    }

    /**
     * Guardar un nuevo favorito.
     */
    public function store(Request $request)
    {
        // Validación de entrada
        $data = $request->validate([
            'productos_id' => 'nullable|exists:productos,productos_id',
            'servicios_id' => 'nullable|exists:servicios,servicios_id',
        ]);

        // Verifica que al menos uno esté presente
        if (empty($data['productos_id']) && empty($data['servicios_id'])) {
            return response()->json([
                'error' => 'Debe especificar un producto o servicio.'
            ], 422);
        }

        $favorito = Favorito::create([
            'users_id' => Auth::id(),
            'productos_id' => $data['productos_id'] ?? null,
            'servicios_id' => $data['servicios_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'favorito' => $favorito
        ], 201);
    }

    /**
     * Eliminar un favorito existente (opcional pero recomendado).
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $favorito = Favorito::where('favoritos_id', $id)
            ->where('users_id', $user->id)
            ->firstOrFail();

        $favorito->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorito eliminado exitosamente.'
        ]);
    }
}
