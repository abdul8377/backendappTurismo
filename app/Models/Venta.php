<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'venta_id';

    protected $fillable = [
        'user_id',
        'metodo_pago_id',
        'codigo_venta',
        'total',
        'estado'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'metodo_pago_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id', 'venta_id');
    }
}
