<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{SolicitudRetiro, MovimientoCuenta};
use Stripe\Stripe;
use Stripe\Transfer;
use Stripe\Exception\ApiErrorException;

class RetiroController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth:sanctum', 'role:Administrador']);
    // }

    /* ───────────────── LISTAR SOLICITUDES ───────────────── */

    public function index(Request $request)
    {
        $estado = $request->query('estado');

        $q = SolicitudRetiro::with('emprendimiento');

        if ($estado) {
            $q->where('estado', $estado);
        }

        return response()->json(
            $q->latest()->paginate($request->query('per_page', 20))
        );
    }

    /* ───────────────── APROBAR ───────────────── */

    public function aprobar($id, Request $request)
    {
        $retiro = SolicitudRetiro::with('emprendimiento')->findOrFail($id);

        if ($retiro->estado !== SolicitudRetiro::EST_PENDIENTE) {
            return response()->json(['message' => 'Solicitud ya procesada'], 422);
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));

            /* ⚠️ Aquí usarías Stripe Connect (destination charges o payouts).
               Para modo demo enviamos un Transfer al vendedor‑dummy: */
            $tr = Transfer::create([
                'amount'             => $retiro->monto * 100,
                'currency'           => 'usd',
                'destination'        => $retiro->emprendimiento->stripe_account_id ?? 'acct_demo',
                'description'        => "Retiro emprendimiento {$retiro->emprendimientos_id}",
            ]);

            DB::transaction(function () use ($retiro, $tr) {
                /* 1) actualizar solicitud */
                $retiro->update([
                    'estado'              => SolicitudRetiro::EST_PAGADO,
                    'stripe_transfer_id'  => $tr->id,
                ]);

                /* 2) actualizar movimiento */
                MovimientoCuenta::where([
                        'emprendimientos_id' => $retiro->emprendimientos_id,
                        'estado'             => MovimientoCuenta::EST_EN_RETIRO,
                        'tipo'               => MovimientoCuenta::TIPO_RETIRO,
                        'monto'              => -$retiro->monto
                    ])
                    ->orderByDesc('movimiento_id')
                    ->limit(1)
                    ->update([
                        'estado'    => MovimientoCuenta::EST_PAGADO,
                        'stripe_id' => $tr->id,
                    ]);
            });

            return response()->json(['message' => 'Retiro pagado', 'transfer' => $tr->id]);

        } catch (ApiErrorException $e) {
            return response()->json(['message' => 'Stripe error', 'error' => $e->getMessage()], 502);
        }
    }

    /* ───────────────── RECHAZAR ───────────────── */

    public function rechazar($id)
    {
        $retiro = SolicitudRetiro::findOrFail($id);

        if ($retiro->estado !== SolicitudRetiro::EST_PENDIENTE) {
            return response()->json(['message' => 'Solicitud ya procesada'], 422);
        }

        DB::transaction(function () use ($retiro) {
            /* 1) solicitud */
            $retiro->update(['estado' => SolicitudRetiro::EST_RECHAZADO]);

            /* 2) devolver saldo al emprendedor */
            MovimientoCuenta::where([
                    'emprendimientos_id' => $retiro->emprendimientos_id,
                    'estado'             => MovimientoCuenta::EST_EN_RETIRO,
                    'tipo'               => MovimientoCuenta::TIPO_RETIRO,
                    'monto'              => -$retiro->monto
                ])
                ->orderByDesc('movimiento_id')
                ->limit(1)
                ->update(['estado' => MovimientoCuenta::EST_LIBERADO]);
        });

        return response()->json(['message' => 'Solicitud rechazada']);
    }

    /* ───────────────── MOVIMIENTOS GLOBALES ───────────────── */

    public function movimientos(Request $request)
    {
        $q = MovimientoCuenta::with('emprendimiento');

        if ($estado = $request->query('estado')) {
            $q->where('estado', $estado);
        }
        if ($tipo = $request->query('tipo')) {
            $q->where('tipo', $tipo);
        }

        return response()->json(
            $q->latest()->paginate($request->query('per_page', 30))
        );
    }
}
