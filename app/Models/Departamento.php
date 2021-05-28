<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $connection = 'help';
    protected $table = 'public.departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps    = false;

    protected $fillable = ['departamento', 'cod'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
