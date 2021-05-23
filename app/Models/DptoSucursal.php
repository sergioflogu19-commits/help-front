<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DptoSucursal extends Model
{
    protected $connection = 'help';
    protected $table = 'public.dpto_sucursal';
    protected $primaryKey = 'id_dptosucursal';
    public $timestamps = false;

    protected $fillable = ['sucursal_id_sucursal', 'departamento_id_departamento'];
}
