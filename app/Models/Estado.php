<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $connection = 'help';
    protected $table = 'public.estado';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = ['estado', 'cod'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];

    const EN_ESPERA = 1;
    const EN_PROCESO = 2;
    const CERRADO = 3;
}
