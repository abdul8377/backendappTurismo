<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Conversacion extends Model
    {
        protected $table = 'conversaciones';
        protected $primaryKey = 'conversaciones_id';

        protected $fillable = [
            'users_id',
            'destinatario_users_id',
            'emprendimientos_id',
            'tipo',
        ];

        /* Relaciones */
        public function usuarioOrigen()      { return $this->belongsTo(User::class,  'users_id'); }
        public function usuarioDestino()     { return $this->belongsTo(User::class,  'destinatario_users_id'); }
        public function emprendimiento()     { return $this->belongsTo(Emprendimiento::class, 'emprendimientos_id'); }
        public function mensajes()           { return $this->hasMany(Mensaje::class, 'conversaciones_id'); }

        /* Scope para encontrar dialogo ya existente */
        public function scopeEntreUsuarios($q, $u1, $u2)
        {
            return $q->where('tipo', 'usuario_usuario')
                    ->where(function($q) use ($u1,$u2) {
                        $q->where('users_id', $u1)->where('destinatario_users_id', $u2)
                        ->orWhere(function($q) use ($u1,$u2) {
                            $q->where('users_id', $u2)->where('destinatario_users_id', $u1);
                        });
                    });
        }
    }
