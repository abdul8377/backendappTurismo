<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Emprendimiento extends Model
{
    protected $table      = 'emprendimientos';
    protected $primaryKey = 'emprendimientos_id';
    public    $timestamps = false;

    protected $fillable = [
        'codigo_unico',
        'nombre',
        'descripcion',
        'tipo_negocio_id',
        'direccion',
        'telefono',
        'estado',
        'fecha_registro',
    ];

    protected $appends = ['imagenes_url'];

    /* ─────────── Imágenes polimórficas ─────────── */

    public function images(): MorphToMany
    {
        return $this->morphToMany(
            Images::class,
            'imageable',
            'imageables',
            'imageable_id',
            'images_id'
        )->withTimestamps();
    }

    public function getImagenesUrlAttribute(): array
    {
        return $this->images
            ->map(fn($img) => Str::startsWith($img->url, ['http://', 'https://'])
                ? $img->url
                : URL::to('storage/' . $img->url))
            ->toArray();
    }

    /* ─────────── Boot: genera código único ─────────── */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            do {
                $codigo = strtoupper(Str::random(6));
            } while (self::where('codigo_unico', $codigo)->exists());

            $model->codigo_unico = $codigo;
        });
    }

    /* ─────────── Relaciones de negocio ─────────── */

    public function tipoDeNegocio()
    {
        return $this->belongsTo(TipoDeNegocio::class, 'tipo_negocio_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class,
            'emprendimiento_usuarios',
            'emprendimientos_id',
            'users_id')
            ->withPivot('rol_emprendimiento', 'fecha_asignacion')
            ->withTimestamps();
    }

    /* ─────────── Relaciones añadidas ─────────── */

    /** Productos que ofrece el emprendimiento */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Servicios que ofrece */
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Ítems vendidos (DetalleVenta) */
    public function detallesVendidos()
    {
        return $this->hasMany(DetalleVenta::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Movimientos contables (libro mayor) */
    public function movimientos()
    {
        return $this->hasMany(MovimientoCuenta::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /** Solicitudes de retiro de fondos */
    public function retiros()
    {
        return $this->hasMany(SolicitudRetiro::class, 'emprendimientos_id', 'emprendimientos_id');
    }
}
