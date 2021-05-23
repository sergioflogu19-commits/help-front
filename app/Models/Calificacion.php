<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $connection = 'help';
    protected $table = 'public.calificacion';
    protected $primaryKey = 'id_calificacion';
    public $timestamps = false;

    protected $fillable = ['calificacion', 'cod'];
}
