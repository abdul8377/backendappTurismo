<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodo_pagos';
    protected $primaryKey = 'metodo_pago_id';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    // Relación uno a muchos con Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'metodo_pago_id', 'metodo_pago_id');
    }
}
