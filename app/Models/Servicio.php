<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';
    protected $primaryKey = 'servicios_id';
    public $timestamps = true;

    protected $fillable = [
        'emprendimientos_id',
        'nombre',
        'imagen_url',
        'descripcion',
        'precio',
        'capacidad_maxima',
        'duracion_servicio',
        'imagen_destacada',
        'categorias_servicios_id',
    ];

    // Accesor para imagen_url
    public function getImagenUrlAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    // Accesor para imagen_destacada
    public function getImagenDestacadaAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    // Relaciones

    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    public function categoriaServicio()
    {
        return $this->belongsTo(CategoriaServicio::class, 'categorias_servicios_id', 'categorias_servicios_id');
    }

    // alias si quieres
    public function categoria()
    {
        return $this->categoriaServicio();
    }
}
