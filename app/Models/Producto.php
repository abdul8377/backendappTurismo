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
        'descripcion',
        'precio',
        'unidad',
        'categorias_productos_id', // Cambié de 'categorias_id' a 'categorias_productos_id'
        'stock',
        'capacidad_total'
    ];

    // Relaciones
    public function emprendimiento()
    {
        return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    public function categoria()
    {
        // Asegúrate de que los campos de clave foránea y primaria estén correctamente asignados
        return $this->belongsTo(CategoriaProducto::class, 'categorias_productos_id', 'categorias_productos_id');
    }
}
