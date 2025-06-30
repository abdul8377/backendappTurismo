<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'pago_id';

    protected $fillable = [
        'metodo_pago_id',
        'user_id',
        'monto',
        'referencia',
        'estado'
    ];

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'metodo_pago_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
