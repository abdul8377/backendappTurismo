<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table      = 'ventas';
    protected $primaryKey = 'venta_id';

    protected $fillable = [
        'user_id',
        'metodo_pago_id',
        'codigo_venta',
        'total',
        'estado',
        'total_pagado',
        'fecha_pago',
    ];

    /* ─────────────── Relaciones ─────────────── */

    /** Usuario comprador */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /** Método de pago (Stripe, Yape, etc.) */
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'metodo_pago_id');
    }

    /** Detalle de productos/servicios vendidos */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id', 'venta_id');
    }

    /** ▼  RELACIÓN QUE FALTABA  ▼
     *  Movimientos contables generados por esta venta
     *  (crédito al emprendimiento, débito de comisión, etc.)
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoCuenta::class, 'venta_id', 'venta_id');
    }
}
