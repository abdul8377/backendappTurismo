<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaServicio extends Model
{
    use HasFactory;

    protected $table = 'categorias_servicios';
    protected $primaryKey = 'categorias_servicios_id';

    // Campos asignables
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen_url',
        'icono_url',
    ];

    // Accesores virtuales no necesarios ya, pero puedes mantenerlos si quieres compatibilidad
    // O simplemente exponer los campos tal cual

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

    // Relación con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'categorias_servicios_id');
    }
}
