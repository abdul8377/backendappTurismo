<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';
    protected $primaryKey = 'carrito_id';

    protected $fillable = [
        'user_id',
        'productos_id',
        'servicios_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'total_carrito', // Agregado para permitir la asignación masiva del total del carrito
        'estado'          // Agregado para permitir el cambio de estado (en proceso, completado)
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con los productos
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id', 'productos_id');
    }

    /**
     * Relación con los servicios
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicios_id', 'servicios_id');
    }

    /**
     * Método para actualizar el total del carrito (puede ser llamado cuando se agrega un producto o cambia la cantidad).
     */
    public function updateTotal()
    {
        // Sumar todos los subtotales de los productos en el carrito
        $total = $this->user->carrito()->sum('subtotal');

        // Actualizar el total del carrito
        $this->update(['total_carrito' => $total]);
    }

    /**
     * Método para actualizar el estado del carrito (cuando se convierte en una venta).
     */
    public function actualizarEstado($nuevoEstado)
    {
        $this->update(['estado' => $nuevoEstado]);
    }
}
