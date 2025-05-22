<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\URL;

class CategoriaServicio extends Model
{
    use HasFactory;

    protected $table = 'categorias_servicios';
    protected $primaryKey = 'categorias_servicios_id';

    // Campos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'icono',
    ];

    protected $appends = ['imagen_url', 'icono_url'];
    protected $hidden  = ['imagen', 'icono'];

    // Relación polimórfica correcta
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(
            Images::class,       // Modelo destino
            'imageables',        // Nombre de la tabla pivote
            'imageable_id',      // FK en pivote que apunta a categorias_servicios.categorias_servicios_id
            'images_id'          // FK en pivote que apunta a images.id
        )
        ->wherePivot('imageable_type', self::class)  // Sólo las filas cuyo imageable_type coincida
        ->withTimestamps();                          // Para usar created_at / updated_at en pivote
    }

    public function getImagenUrlAttribute(): ?string
    {
        $imagen = $this->images->firstWhere('titulo', 'NOT LIKE', '%Icono%');
        return $imagen ? asset("storage/{$imagen->url}") : null;
    }

    public function getIconoUrlAttribute(): ?string
    {
        $icono = $this->images->firstWhere('titulo', 'LIKE', '%Icono%');
        return $icono ? asset("storage/{$icono->url}") : null;
    }


    // Relación con servicios
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'categorias_servicios_id');
    }
}
