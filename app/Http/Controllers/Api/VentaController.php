<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\MetodoPago;
use App\Models\Pago;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;

class VentaController extends Controller
{
    /**
     * Toma los ítems del carrito y los convierte en una Venta + DetalleVenta.
     */
    public function store(Request $request)
    {
        $user  = $request->user();

        // Filtrar los carritos con estado 'en proceso'
        $items = $user->carrito()->where('estado', 'en proceso')->get();

        // Si no hay carritos 'en proceso', devolver un mensaje
        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'No tienes productos o servicios en el carrito.'
            ], 422);
        }

        // Verificar stock de productos antes de proceder
        foreach ($items as $item) {
            if ($item->productos_id) {
                $producto = Producto::find($item->productos_id);
                if ($producto->stock < $item->cantidad) {
                    return response()->json([
                        'message' => "No hay suficiente stock de {$producto->nombre}."
                    ], 422);
                }
            }
        }

        // Transacción para atomicidad
        $venta = DB::transaction(function () use ($user, $items, $request) {
            // Crear fila en `ventas`
            $venta = Venta::create([
                'user_id'      => $user->id,
                'codigo_venta' => Str::upper(Str::random(12)),
                'total'        => $items->sum('subtotal'),
                'estado'       => 'pendiente',
                'metodo_pago_id' => $request->metodo_pago_id,  // Método de pago proporcionado
            ]);

            // Crear detalle de la venta y actualizar el stock
            foreach ($items as $it) {
                DetalleVenta::create([
                    'venta_id'       => $venta->venta_id,
                    'user_id'        => $user->id,
                    'productos_id'   => $it->productos_id,
                    'servicios_id'   => $it->servicios_id,
                    'cantidad'       => $it->cantidad,
                    'precio_unitario'=> $it->precio_unitario,
                    'subtotal'       => $it->subtotal,
                ]);

                // Actualizar el stock de los productos
                if ($it->productos_id) {
                    $producto = Producto::find($it->productos_id);
                    $producto->stock -= $it->cantidad;
                    $producto->save();
                }
            }

            // Actualizar el carrito a "completado" después de la venta
            $user->carrito()->where('estado', 'en proceso')->update(['estado' => 'completado']);
            return $venta;
        });

        // Procesar el pago
        return $this->procesarPago($venta, $request);
    }

/**
 * Procesar el pago y actualizar la venta.
 */
public function procesarPago(Venta $venta, Request $request)
{
    // Obtener el método de pago
    $metodoPago = MetodoPago::find($venta->metodo_pago_id);

    // Verificar si el método de pago es válido
    if (!$metodoPago) {
        return response()->json(['message' => 'Método de pago no válido.'], 400);
    }

    // Llamada al controlador de pagos (Stripe o similar)
    try {
        // Establecer la clave secreta de Stripe
        Stripe::setApiKey(config('stripe.secret'));

        // Crear el pago en Stripe
        $charge = Charge::create([
            'amount' => $venta->total * 100, // monto en centavos
            'currency' => 'usd',
            'source' => $request->token, // Obtenido desde el frontend
            'description' => 'Compra en appTurismo',
        ]);

        // Si el pago es exitoso, actualizar la venta
        $venta->estado = 'completado';
        $venta->fecha_pago = now();
        $venta->save();

        // Registrar el pago en la tabla 'pagos'
        Pago::create([
            'metodo_pago_id' => $metodoPago->metodo_pago_id,
            'user_id' => $venta->user_id,
            'monto' => $venta->total,
            'referencia' => $charge->id,
            'estado' => 'completado'
        ]);

        // Respuesta exitosa
        return response()->json([
            'venta_id' => $venta->venta_id,
            'codigo_venta' => $venta->codigo_venta,
            'total' => $venta->total,
            'estado' => $venta->estado,
        ], 200);

    } catch (ApiErrorException $e) {
        // Si el pago falla, actualizamos el estado de la venta a 'cancelado'
        $venta->estado = 'cancelado';
        $venta->save();

        // Aquí imprimimos detalles del error para saber si el problema es con el token
        return response()->json([
            'message' => 'Pago fallido',
            'error' => $e->getMessage(),
            'stripe_error' => $e->getError()->message, // Esto nos da el mensaje específico de Stripe
            'code' => $e->getCode() // El código del error de Stripe
        ], 400);
    }
}


    /**
     * Listar todas las compras (ventas) del usuario.
     */
    public function listarCompras(Request $request)
    {
        $user = $request->user();

        // Obtener todas las ventas con sus detalles
        $ventas = Venta::with(['detalleVentas', 'metodoPago'])
                       ->where('user_id', $user->id)
                       ->get();

        return response()->json($ventas);
    }
}
