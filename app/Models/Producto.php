<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'productos_id';
    public $timestamps = false;

    protected $fillable = [
        'emprendimientos_id',
        'nombre',
        'imagen_url',
        'descripcion',
        'precio',
        'unidad',
        'categorias_productos_id',
        'stock',
        'capacidad_total'
    ];

    // Accesor para la URL completa de la imagen
    public function getImagenUrlAttribute($value): ?string
    {
        return $value ? asset('storage/' . $value) : null;
    }

    // Relaciones
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProducto::class, 'categorias_productos_id', 'categorias_productos_id');
    }
}
