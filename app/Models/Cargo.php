<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $connection = 'help';
    protected $table = 'public.cargo';
    protected $primaryKey = 'id_cargo';
    public $timestamps    = false;

    protected $fillable = ['cargo', 'cod', 'prioridad_id_prioridad'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
