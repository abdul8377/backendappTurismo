<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Venta, DetalleVenta, Producto, Servicio, MetodoPago, Pago, MovimientoCuenta, ParametroFinanciero
};
use Stripe\{Stripe, PaymentIntent};
use Stripe\Exception\ApiErrorException;

class VentaController extends Controller
{
    /** ▸ Crear la venta inicial (sin pago aún) */
    public function store(Request $request)
    {
        $user  = $request->user();
        $items = $user->carrito()->where('estado', 'en proceso')->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Carrito vacío.'], 422);
        }

        foreach ($items as $item) {
            if ($item->productos_id) {
                $producto = Producto::find($item->productos_id);
                if ($producto->stock < $item->cantidad) {
                    return response()->json(['message' => "No hay suficiente stock de {$producto->nombre}."], 422);
                }
            }
        }

        $venta = DB::transaction(function () use ($user, $items, $request) {
            $venta = Venta::create([
                'user_id'        => $user->id,
                'codigo_venta'   => Str::upper(Str::random(12)),
                'total'          => $items->sum('subtotal'),
                'estado'         => 'pendiente',
                'metodo_pago_id' => $request->metodo_pago_id,
            ]);

            $comisionPct = ParametroFinanciero::float('comision_porcentaje', 10);

            foreach ($items as $it) {
                $emprendimientoId = $it->productos_id
                    ? Producto::find($it->productos_id)->emprendimientos_id
                    : Servicio::find($it->servicios_id)->emprendimientos_id;

                if ($it->productos_id) {
                    Producto::find($it->productos_id)->decrement('stock', $it->cantidad);
                }

                $detalle = DetalleVenta::create([
                    'venta_id'          => $venta->venta_id,
                    'emprendimientos_id'=> $emprendimientoId,
                    'user_id'           => $user->id,
                    'productos_id'      => $it->productos_id,
                    'servicios_id'      => $it->servicios_id,
                    'cantidad'          => $it->cantidad,
                    'precio_unitario'   => $it->precio_unitario,
                    'subtotal'          => $it->subtotal,
                ]);

                MovimientoCuenta::create([
                    'emprendimientos_id' => $emprendimientoId,
                    'venta_id'           => $venta->venta_id,
                    'detalle_venta_id'   => $detalle->detalle_venta_id,
                    'tipo'               => MovimientoCuenta::TIPO_VENTA,
                    'monto'              => $detalle->subtotal * (1 - $comisionPct / 100),
                    'estado'             => MovimientoCuenta::EST_PENDIENTE,
                ]);

                MovimientoCuenta::create([
                    'emprendimientos_id' => config('app.id_plataforma'),
                    'venta_id'           => $venta->venta_id,
                    'detalle_venta_id'   => $detalle->detalle_venta_id,
                    'tipo'               => MovimientoCuenta::TIPO_COMISION,
                    'monto'              => -$detalle->subtotal * ($comisionPct / 100),
                    'estado'             => MovimientoCuenta::EST_PENDIENTE,
                ]);
            }

            $user->carrito()->where('estado', 'en proceso')->update(['estado' => 'completado']);

            return $venta;
        });

        return response()->json([
            'venta_id' => $venta->venta_id,
            'codigo_venta' => $venta->codigo_venta,
            'total' => $venta->total,
            'estado' => $venta->estado,
        ], 200);
    }

    /** ▸ Verificar el pago usando PaymentIntent */
    public function procesarPago(Venta $venta, Request $request)
    {
        $request->validate(['payment_intent_id' => 'required|string']);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                return response()->json(['message' => 'Pago no exitoso.', 'status' => $paymentIntent->status], 400);
            }

            DB::transaction(function () use ($venta, $paymentIntent) {
                $venta->update([
                    'estado' => 'completado',
                    'total_pagado' => $venta->total,
                    'fecha_pago' => now(),
                ]);

                MovimientoCuenta::where('venta_id', $venta->venta_id)->update([
                    'estado' => MovimientoCuenta::EST_LIBERADO,
                    'stripe_id' => $paymentIntent->id,
                ]);

                Pago::create([
                    'metodo_pago_id' => $venta->metodo_pago_id,
                    'user_id' => $venta->user_id,
                    'monto' => $venta->total,
                    'referencia' => $paymentIntent->id,
                    'estado' => 'completado',
                ]);
            });

            return response()->json([
                'venta_id' => $venta->venta_id,
                'codigo_venta' => $venta->codigo_venta,
                'total' => $venta->total,
                'estado' => $venta->estado,
            ], 200);

        } catch (ApiErrorException $e) {
            $venta->update(['estado' => 'cancelado']);
            MovimientoCuenta::where('venta_id', $venta->venta_id)
                ->update(['estado' => MovimientoCuenta::EST_CANCELADO]);

            return response()->json(['message' => 'Error validando pago.', 'error' => $e->getMessage()], 400);
        }
    }
        /** ▸ Historial de compras del usuario autenticado */
    public function listarCompras(Request $request)
    {
        $ventas = Venta::with(['detalles.producto', 'detalles.servicio', 'metodoPago'])
            ->where('user_id', $request->user()->id)
            ->orderBy('fecha_pago', 'desc') // Opcional, orden por más reciente
            ->get();

        return response()->json($ventas, 200);
    }
}
