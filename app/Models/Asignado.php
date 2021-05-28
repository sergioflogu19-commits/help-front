<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asignado extends Model
{
    protected $connection = 'help';
    protected $table = 'public.asignado';
    protected $primaryKey = 'id_asignado';
    public $timestamps = false;

    protected $fillable = ['fecha', 'ticket_id_ticket', 'usuario_id_usuario', 'asignado'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
