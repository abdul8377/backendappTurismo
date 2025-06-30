<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetodoPago;

class MetodoPagoController extends Controller
{
    /**
     * Listar todos los métodos de pago
     */
    public function index()
    {
        $metodos = MetodoPago::all();
        return response()->json($metodos);
    }

    /**
     * Crear un nuevo método de pago
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        $metodoPago = MetodoPago::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo,
        ]);

        return response()->json($metodoPago, 201); // Retorna el método de pago recién creado
    }

    /**
     * Mostrar un método de pago específico
     */
    public function show($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado.'], 404);
        }

        return response()->json($metodoPago);
    }

    /**
     * Actualizar un método de pago existente
     */
    public function update(Request $request, $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado.'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'required|boolean',
        ]);

        $metodoPago->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->activo,
        ]);

        return response()->json($metodoPago);
    }

    /**
     * Eliminar un método de pago
     */
    public function destroy($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado.'], 404);
        }

        $metodoPago->delete();

        return response()->json(['message' => 'Método de pago eliminado con éxito.']);
    }

    /**
     * Suspender un método de pago (activar/desactivar)
     */
    public function suspend($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado.'], 404);
        }

        // Cambiar el estado de 'activo' a 'false'
        $metodoPago->activo = false;
        $metodoPago->save();

        return response()->json(['message' => 'Método de pago suspendido.', 'metodo_pago' => $metodoPago]);
    }

    /**
     * Activar un método de pago
     */
    public function activate($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado.'], 404);
        }

        // Cambiar el estado de 'activo' a 'true'
        $metodoPago->activo = true;
        $metodoPago->save();

        return response()->json(['message' => 'Método de pago activado.', 'metodo_pago' => $metodoPago]);
    }
}
