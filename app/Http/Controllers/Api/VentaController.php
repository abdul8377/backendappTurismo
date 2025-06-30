<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\{Venta, DetalleVenta, Carrito, Producto, Servicio,
               MetodoPago, Pago, MovimientoCuenta, ParametroFinanciero};
use Stripe\{Stripe, Charge};
use Stripe\Exception\ApiErrorException;

class VentaController extends Controller
{
    /** ▸ Paso 1: checkout */
    public function store(Request $request)
    {
        $user   = $request->user();
        $items  = $user->carrito()->where('estado', 'en proceso')->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'No tienes productos o servicios en el carrito.'], 422);
        }

        /* Verificar stock */
        foreach ($items as $item) {
            if ($item->productos_id) {
                $p = Producto::find($item->productos_id);
                if ($p->stock < $item->cantidad) {
                    return response()->json(['message' => "No hay suficiente stock de {$p->nombre}."], 422);
                }
            }
        }

        /* ───────── Transacción ───────── */
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

                /* Determinar a qué emprendimiento pertenece el ítem */
                $emprendimientoId = null;

                if ($it->productos_id) {
                    $producto          = Producto::find($it->productos_id);
                    $emprendimientoId  = $producto->emprendimientos_id;
                    $producto->decrement('stock', $it->cantidad);
                } elseif ($it->servicios_id) {
                    $servicio          = Servicio::find($it->servicios_id);
                    $emprendimientoId  = $servicio->emprendimientos_id;
                }

                /* Crear detalle */
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

                /* Movimientos contables */
                $neto      = $detalle->subtotal * (1 - $comisionPct / 100);
                $comision  = $detalle->subtotal - $neto;

                // crédito al emprendimiento
                MovimientoCuenta::create([
                    'emprendimientos_id' => $emprendimientoId,
                    'venta_id'           => $venta->venta_id,
                    'detalle_venta_id'   => $detalle->detalle_venta_id,
                    'tipo'               => MovimientoCuenta::TIPO_VENTA,
                    'monto'              =>  $neto,
                    'estado'             => MovimientoCuenta::EST_PENDIENTE,
                ]);

                // débito de comisión (plataforma)
                MovimientoCuenta::create([
                    'emprendimientos_id' => config('app.id_plataforma'), // o null
                    'venta_id'           => $venta->venta_id,
                    'detalle_venta_id'   => $detalle->detalle_venta_id,
                    'tipo'               => MovimientoCuenta::TIPO_COMISION,
                    'monto'              => -$comision,
                    'estado'             => MovimientoCuenta::EST_PENDIENTE,
                ]);
            }

            /* Vaciar carrito */
            $user->carrito()->where('estado', 'en proceso')->update(['estado' => 'completado']);

            return $venta;
        });

        /* Paso 2: procesar pago */
        return $this->procesarPago($venta, $request);
    }

    /** ▸ Paso 2: pago Stripe */
    public function procesarPago(Venta $venta, Request $request)
    {
        $metodoPago = MetodoPago::find($venta->metodo_pago_id);
        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no válido.'], 400);
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));

            $charge = Charge::create([
                'amount'      => $venta->total * 100,
                'currency'    => 'usd',
                'source'      => $request->token,
                'description' => 'Compra en appTurismo',
            ]);

            /* 1) actualizar venta */
            $venta->update([
                'estado'     => 'completado',
                'total_pagado'=> $venta->total,
                'fecha_pago' => now(),
            ]);

            /* 2) liberar movimientos */
            MovimientoCuenta::where('venta_id', $venta->venta_id)
                ->update([
                    'estado'    => MovimientoCuenta::EST_LIBERADO,
                    'stripe_id' => $charge->id,
                ]);

            /* 3) registrar pago */
            Pago::create([
                'metodo_pago_id' => $metodoPago->metodo_pago_id,
                'user_id'        => $venta->user_id,
                'monto'          => $venta->total,
                'referencia'     => $charge->id,
                'estado'         => 'completado',
            ]);

            return response()->json([
                'venta_id'     => $venta->venta_id,
                'codigo_venta' => $venta->codigo_venta,
                'total'        => $venta->total,
                'estado'       => $venta->estado,
            ], 200);

        } catch (ApiErrorException $e) {

            $venta->update(['estado' => 'cancelado']);

            MovimientoCuenta::where('venta_id', $venta->venta_id)
                ->update(['estado' => MovimientoCuenta::EST_CANCELADO]);

            return response()->json([
                'message'       => 'Pago fallido',
                'error'         => $e->getMessage(),
                'stripe_error'  => optional($e->getError())->message,
                'code'          => $e->getCode(),
            ], 400);
        }
    }

    /** ▸ Historial de compras del turista */
    public function listarCompras(Request $request)
    {
        $ventas = Venta::with(['detalles.producto','detalles.servicio','metodoPago'])
                       ->where('user_id', $request->user()->id)
                       ->get();

        return response()->json($ventas);
    }
}
