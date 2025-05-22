<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\URL;

class ZonaTuristica extends Model
{
    protected $table = 'zonas_turisticas';
    protected $primaryKey = 'zonas_turisticas_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'estado',
    ];

    // Para exponer automáticamente la URL de la imagen
    protected $appends = ['imagen_url'];

    /**
     * Relación many-to-many polimórfica vía tabla `imageables`
     */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Images::class, // Modelo destino
            'imageables',              // Tabla pivote
            'imageable_id',            // FK pivote -> zonas_turisticas.zonas_turisticas_id
            'images_id'                // FK pivote -> images.id
        )
        ->wherePivot('imageable_type', self::class)
        ->withTimestamps();
    }

    /**
     * Accesor para la URL de la primera imagen
     */
    public function getImagenUrlAttribute(): ?string
    {
        $img = $this->images->first();
        return $img ? URL::to("storage/{$img->url}") : null;
    }
}
