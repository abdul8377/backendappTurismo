<?php
// app/Models/MovimientoCuenta.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCuenta extends Model
{
    use HasFactory;

    /** Tabla y PK */
    protected $table      = 'movimiento_cuentas';
    protected $primaryKey = 'movimiento_id';

    /** Atributos modificables en masa */
    protected $fillable = [
        'emprendimientos_id',
        'venta_id',
        'detalle_venta_id',
        'tipo',
        'monto',
        'estado',
        'stripe_id',
    ];

    /** Casts */
    protected $casts = [
        'monto' => 'decimal:2',
    ];

    /** Tipos de movimiento */
    public const TIPO_VENTA    = 'venta';
    public const TIPO_COMISION = 'comision';
    public const TIPO_AJUSTE   = 'ajuste';
    public const TIPO_RETIRO   = 'retiro';

    /** Estados */
    public const EST_PENDIENTE  = 'pendiente';
    public const EST_LIBERADO   = 'liberado';
    public const EST_EN_RETIRO  = 'en_retiro';
    public const EST_PAGADO     = 'pagado';
    public const EST_CANCELADO  = 'cancelado';

    /* ─────────────── Relaciones ─────────────── */

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    public function detalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_venta_id', 'detalle_venta_id');
    }

    /* ─────────────── Scopes auxiliares ─────────────── */

    public function scopeDeEmprendimiento($q, $id)
    {
        return $q->where('emprendimientos_id', $id);
    }

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }

    public function scopeTipo($q, $tipo)
    {
        return $q->where('tipo', $tipo);
    }
}
