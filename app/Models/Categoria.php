<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $connection = 'help';
    protected $table = 'public.categoria';
    protected $primaryKey = 'id_categoria';
    public $timestamps    = false;

    protected $fillable = ['categoria', 'cod'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
