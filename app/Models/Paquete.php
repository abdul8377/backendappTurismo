<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $table = 'paquetes';
    protected $primaryKey = 'paquetes_id';
    public $timestamps = false;

    protected $fillable = [
        'emprendimientos_id',
        'nombre',
        'descripcion',
        'precio_total',
        'estado'
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleServicioPaquete::class, 'paquetes_id', 'paquetes_id');
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'detalle_servicio_paquete', 'paquetes_id', 'servicios_id');
    }

    public function images()
    {
        return $this->morphToMany(Images::class, 'imageable');
    }

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

}

