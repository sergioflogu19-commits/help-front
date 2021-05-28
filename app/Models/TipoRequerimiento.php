<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRequerimiento extends Model
{
    protected $connection = 'help';
    protected $table = 'public.tipo_requerimiento';
    protected $primaryKey = 'id_tipo_req';
    public $timestamps = false;

    protected $fillable = [
        'sub_cat', 'cod', 'categoria_id_categoria', 'division_id_division'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];
}
