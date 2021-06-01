<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $connection = 'help';
    protected $table = 'public.division';
    protected $primaryKey = 'id_division';
    public $timestamps = false;

    protected $fillable = ['cod', 'division'];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];

    const OTROS = 6;
}
