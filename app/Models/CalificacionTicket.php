<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionTicket extends Model
{
    protected $connection = 'help';
    protected $table = 'public.calificacion_ticket_usuario';
    protected $primaryKey = 'id_calificacion_ticket';
    public $timestamps    = false;

    protected $fillable = ['calificacion_id_calificacion', 'ticket_id_ticket', 'usuario_id_usuario'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
