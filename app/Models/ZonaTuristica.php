<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
     * 2) Accesor que construye la URL pública de la primera imagen
     */
    public function getImagenUrlAttribute(): ?string
    {
        // Siempre obtenemos la colección para usar Collection::first()
        $img = $this->images()->get()->first();
        if (! $img) {
            return null;
        }
        // Si url ya es absoluta, devuélvela
        $url = $img->url;
        if (Str::startsWith($url, ['http://','https://'])) {
            return $url;

        }
        // En otro caso, construye usando el enlace storage
        return URL::to("storage/{$url}");

    }
}
