<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'productos_id';
    public $timestamps = false;

    protected $fillable = [
        'emprendimientos_id',
        'categorias_productos_id',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'capacidad_total',
        'estado',
    ];
    protected $appends = ['imagenes_url','imagen_url'];

    public function images(): MorphToMany
    {
        return $this->morphToMany(
            Images::class,
            'imageable',
            'imageables',
            'imageable_id',
            'images_id'
        )
            ->withTimestamps();
    }


    public function getImagenesUrlAttribute(): array
    {
        return $this->images
            ->map(function ($img) {
                $url = $img->url;
                if (Str::startsWith($url, ['http://', 'https://'])) {
                    return $url;
                }
                return URL::to('storage/' . $url);
            })
            ->toArray();
    }

    public function getImagenUrlAttribute(): ?string
    {
        $img = $this->images()->first();
        if (! $img) {
            return null;
        }
        $url = $img->url;
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }
        return URL::to("storage/{$url}");
    }

    // Producto.php  (añade la relación que faltaba)
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
