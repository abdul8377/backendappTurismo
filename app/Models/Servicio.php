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
        'descripcion',
        'precio',
        'capacidad_maxima',
        'duracion_servicio',
        'imagen_destacada',
        'categorias_servicios_id', // Relación con categoría de servicio

    ];

    /**
     * Relación con el emprendimiento
     */
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /**
     * Relación con la categoría de servicio
     */
    public function categoriaServicio()
    {
        return $this->belongsTo(CategoriaServicio::class, 'categorias_servicios_id', 'categorias_servicios_id');
    }

    public function categoria()
{
    return $this->belongsTo(CategoriaServicio::class, 'categorias_servicios_id');
}



}
