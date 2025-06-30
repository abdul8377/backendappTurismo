<?php

namespace App\Http\Controllers\Api\Emprendimiento;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{MovimientoCuenta, SolicitudRetiro, ParametroFinanciero};

class RetiroController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware(['auth:sanctum', 'role:Emprendedor']);
    // }

    /* ───────────────── SALDO ───────────────── */

    public function saldo(Request $request)
    {
        $empId = $request->user()->emprendimientoUsuarios()->first()->emprendimientos_id;

        $disponible = MovimientoCuenta::deEmprendimiento($empId)
                        ->estado(MovimientoCuenta::EST_LIBERADO)
                        ->sum('monto');

        $pendiente  = MovimientoCuenta::deEmprendimiento($empId)
                        ->estado(MovimientoCuenta::EST_PENDIENTE)
                        ->sum('monto');

        $enRetiro   = MovimientoCuenta::deEmprendimiento($empId)
                        ->estado(MovimientoCuenta::EST_EN_RETIRO)
                        ->sum('monto');

        return response()->json(compact('disponible','pendiente','enRetiro'));
    }

    /* ───────────────── MOVIMIENTOS ───────────────── */

    public function movimientos(Request $request)
    {
        $empId = $request->user()->emprendimientoUsuarios()->first()->emprendimientos_id;

        $movs = MovimientoCuenta::deEmprendimiento($empId)
                ->latest()
                ->paginate($request->query('per_page', 15));

        return response()->json($movs);
    }

    /* ───────────────── SOLICITAR RETIRO ───────────────── */

    public function store(Request $request)
    {
        $request->validate([
            'monto'            => 'required|numeric|min:1',
            'cuenta_bancaria'  => 'required|array',
        ]);

        $empId      = $request->user()->emprendimientoUsuarios()->first()->emprendimientos_id;
        $montoReq   = number_format($request->monto, 2, '.', '');

        $saldoDisp  = MovimientoCuenta::deEmprendimiento($empId)
                        ->estado(MovimientoCuenta::EST_LIBERADO)
                        ->sum('monto');

        if ($montoReq > $saldoDisp) {
            return response()->json(['message' => 'Saldo insuficiente'], 422);
        }

        $retiro = DB::transaction(function () use ($empId, $montoReq, $request) {

            /* 1) Solicitud */
            $retiro = SolicitudRetiro::create([
                'emprendimientos_id' => $empId,
                'monto'              => $montoReq,
                'cuenta_bancaria'    => $request->cuenta_bancaria,
                'estado'             => SolicitudRetiro::EST_PENDIENTE,
            ]);

            /* 2) Movimiento bloquea saldo */
            MovimientoCuenta::create([
                'emprendimientos_id' => $empId,
                'tipo'               => MovimientoCuenta::TIPO_RETIRO,
                'monto'              => -$montoReq,
                'estado'             => MovimientoCuenta::EST_EN_RETIRO,
                'detalle_venta_id'   => null,
                'venta_id'           => null,
            ]);

            return $retiro;
        });

        return response()->json($retiro, 201);
    }

    /* ───────────────── VER RETIRO ───────────────── */

    public function show($id, Request $request)
    {
        $empId = $request->user()->emprendimientoUsuarios()->first()->emprendimientos_id;

        $retiro = SolicitudRetiro::where('emprendimientos_id', $empId)->findOrFail($id);

        return response()->json($retiro);
    }
}
