<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table      = 'detalle_ventas';
    protected $primaryKey = 'detalle_venta_id';

    protected $fillable = [
        'venta_id',
        'emprendimientos_id',   // ← ya lo tienes en la migración
        'user_id',
        'productos_id',
        'servicios_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descuento',
        'impuesto',
    ];

    /* ─────────── Relaciones existentes ─────────── */

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id', 'productos_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id', 'servicios_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /* ─────────── Relaciones nuevas ─────────── */

    /** Emprendimiento dueño del producto/servicio */
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Movimientos contables generados para este ítem */
    public function movimientos()
    {
        return $this->hasMany(MovimientoCuenta::class, 'detalle_venta_id', 'detalle_venta_id');
    }
}
