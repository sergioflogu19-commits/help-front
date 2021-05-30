<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $connection = 'help';
    protected $table = 'public.rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = ['rol', 'cod'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];

    const FUNCIONARIO = 1;
    const AGENTE = 2;
    const ADMINISTRADOR = 3;
}
