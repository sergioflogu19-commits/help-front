<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requerimiento extends Model
{
    protected $connection = 'help';
    protected $table = 'public.requerimiento';
    protected $primaryKey = 'id_requerimiento';
    public $timestamps = false;

    protected $fillable = ['descripcion', 'usuario_id_usuario', 'interno', 'tipo_requerimiento_id_tipo_req', 'departamento_id_departamento'];
}