<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Emprendimiento extends Model
{
    protected $table = 'emprendimientos';
    protected $primaryKey = 'emprendimientos_id';  // Clave primaria
    public $timestamps = false;

    protected $fillable = [
        'codigo_unico',
        'nombre',
        'descripcion',
        'tipo_negocio_id',
        'direccion',
        'telefono',
        'estado',
        'fecha_registro',
        'imagen_destacada',
    ];

    /**
     * Boot method to generate codigo_unico automatically before creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($emprendimiento) {
            do {
                $codigo = strtoupper(Str::random(6));  // 6 caracteres alfanuméricos en mayúsculas
            } while (self::where('codigo_unico', $codigo)->exists());

            $emprendimiento->codigo_unico = $codigo;
        });
    }

    // Relaciones

    /**
     * Relación uno a muchos consigo mismo por tipo de negocio
     */
    public function emprendimientos()
    {
        return $this->hasMany(Emprendimiento::class, 'tipo_negocio_id');
    }

    /**
     * Relación uno a muchos con Blogs
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class, 'emprendimientos_id', 'emprendimientos_id');
    }

    /**
     * Relación muchos a muchos con Usuarios (con pivot para rol y fecha)
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'emprendimiento_usuarios', 'emprendimientos_id', 'users_id')
                    ->withPivot('rol_emprendimiento', 'fecha_asignacion')
                    ->withTimestamps();
    }

    /**
     * Relación inversa muchos a muchos con usuarios (sin pivot)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'emprendimiento_usuarios', 'emprendimientos_id', 'users_id');
    }

    /**
     * Relación con tipo de negocio (muchos a uno)
     */
    public function tipoDeNegocio()
    {
        return $this->belongsTo(TipoDeNegocio::class, 'tipo_negocio_id');
    }
}
