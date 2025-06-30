<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\MensajeEnviado;          // ①  importa tu nuevo Event

class Mensaje extends Model
{
    use HasFactory;

    protected $table      = 'mensajes';
    protected $primaryKey = 'mensajes_id';

    protected $fillable = [
        'conversaciones_id',
        'emisor',          // 'usuario', 'emprendimiento'
        'contenido',
        'imagen_url',
        'leido',
    ];

    protected $casts = [
        'leido' => 'boolean',
    ];

    /*  Relación inversa */
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'conversaciones_id');
    }

    /*  Disparar evento tras crear el mensaje  */
    protected static function booted(): void
    {
        static::created(function (self $msg) {
            broadcast(new MensajeEnviado($msg))->toOthers();   // ②  usa la clase ya importada
        });
    }
}
