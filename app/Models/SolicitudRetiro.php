<?php
// app/Models/SolicitudRetiro.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudRetiro extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_retiros';   // ← coincide con la migración
    protected $primaryKey = 'retiro_id';

    protected $fillable = [
        'emprendimientos_id',
        'monto',
        'cuenta_bancaria',
        'estado',
        'stripe_transfer_id',
    ];

    protected $casts = [
        'monto'           => 'decimal:2',
        'cuenta_bancaria' => 'array',     // JSON → array
    ];

    /** Estados */
    public const EST_PENDIENTE = 'pendiente';
    public const EST_APROBADO  = 'aprobado';
    public const EST_RECHAZADO = 'rechazado';
    public const EST_PAGADO    = 'pagado';

    /* ─────────────── Relaciones ─────────────── */

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Relación 1‑a‑1 opcional con el movimiento “retiro” */
    public function movimiento()
    {
        return $this->hasOne(
            MovimientoCuenta::class,
            'stripe_id',          // clave en movimientos
            'stripe_transfer_id'  // clave en retiro
        );
    }

    /* ─────────────── Scopes auxiliares ─────────────── */

    public function scopePendiente($q)
    {
        return $q->where('estado', self::EST_PENDIENTE);
    }
}
