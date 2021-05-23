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
}
