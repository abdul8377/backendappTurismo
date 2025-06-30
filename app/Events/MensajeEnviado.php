<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class MensajeEnviado implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Mensaje $mensaje) {}

    /*  Canal donde se emitirá el mensaje */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel("chat.{$this->mensaje->conversaciones_id}");
    }

    /*  Nombre del evento en el frontend */
    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }

    /*  Carga adicional si quieres transformar la data */
    public function broadcastWith(): array
    {
        return [
            'id'        => $this->mensaje->mensajes_id,
            'contenido' => $this->mensaje->contenido,
            'emisor'    => $this->mensaje->emisor,
            'imagen'    => $this->mensaje->imagen_url,
            'enviado_en'=> $this->mensaje->enviado_en,
        ];
    }
}
