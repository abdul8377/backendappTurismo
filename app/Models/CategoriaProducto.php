<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProducto extends Model
{
    use HasFactory;

    protected $table = 'categorias_productos';
    protected $primaryKey = 'categorias_productos_id';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_url',
        'icono_url',
    ];

    // Accesores para devolver la URL completa desde el storage
    public function getImagenUrlAttribute(): ?string
    {
        return $this->attributes['imagen_url']
            ? asset('storage/' . $this->attributes['imagen_url'])
            : null;
    }

    public function getIconoUrlAttribute(): ?string
    {
        return $this->attributes['icono_url']
            ? asset('storage/' . $this->attributes['icono_url'])
            : null;
    }

    // Relación con productos (uno a muchos)
    public function productos()
    {
        return $this->hasMany(Producto::class, 'categorias_productos_id', 'categorias_productos_id');
    }
}
