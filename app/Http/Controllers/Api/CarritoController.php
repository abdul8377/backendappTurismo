<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\Servicio;

class CarritoController extends Controller
{
    /**
     * 1. Mostrar todos los ítems del carrito del usuario autenticado.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // eager-load relaciones para mostrar detalles si se desea
        $items = Carrito::with(['producto', 'servicio'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json($items);
    }

    /**
     * 2. Agregar un nuevo ítem al carrito.
     */
    public function store(Request $request)
    {
        $request->validate([
            'productos_id' => 'required_without:servicios_id|exists:productos,productos_id',
            'servicios_id' => 'required_without:productos_id|exists:servicios,servicios_id',
            'cantidad'     => 'integer|min:1',
        ]);

        $user = $request->user();
        $cantidad = $request->input('cantidad', 1);

        // Obtener precio unitario desde la tabla correspondiente
        if ($request->filled('productos_id')) {
            $producto = Producto::findOrFail($request->productos_id);
            $precioUnitario = $producto->precio;
        } else {
            $servicio = Servicio::findOrFail($request->servicios_id);
            $precioUnitario = $servicio->precio;
        }

        // Crear el ítem en el carrito
        $item = Carrito::create([
            'user_id'         => $user->id,
            'productos_id'    => $request->productos_id,
            'servicios_id'    => $request->servicios_id,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal'        => $precioUnitario * $cantidad,
            'estado'          => 'en proceso',  // El carrito está en proceso por defecto
        ]);

        // Actualizar el total del carrito
        $this->updateTotal($user);

        return response()->json($item, 201);
    }

    /**
     * 3. Actualizar la cantidad (y subtotal) de un ítem ya en el carrito.
     */
    public function update(Request $request, $carrito)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        // Obtener el ítem del carrito
        $item = Carrito::where('carrito_id', $carrito)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Actualizar la cantidad y el subtotal
        $item->cantidad = $request->cantidad;
        $item->subtotal = $item->precio_unitario * $item->cantidad;
        $item->save();

        // Actualizar el total del carrito
        $this->updateTotal($user);

        return response()->json($item);
    }

    /**
     * 4. Eliminar un ítem del carrito.
     */
    public function destroy(Request $request, $carrito)
    {
        $user = $request->user();

        // Obtener el ítem del carrito
        $item = Carrito::where('carrito_id', $carrito)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Eliminar el ítem
        $item->delete();

        // Actualizar el total del carrito
        $this->updateTotal($user);

        return response()->json([
            'message' => 'Ítem eliminado del carrito.'
        ]);
    }

    /**
     * Método para actualizar el total del carrito
     */
    protected function updateTotal($user)
    {
        // Sumar todos los subtotales de los productos en el carrito
        $totalCarrito = $user->carrito()->sum('subtotal');

        // Actualizar el total en la tabla 'carritos'
        $user->carrito()->update(['total_carrito' => $totalCarrito]);
    }
}
