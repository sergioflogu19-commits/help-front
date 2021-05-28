<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $connection = 'help';
    protected $table = 'public.municipio';
    protected $primaryKey = 'id_lugar';
    public $timestamps = false;

    protected $fillable = ['lugar', 'cod'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
