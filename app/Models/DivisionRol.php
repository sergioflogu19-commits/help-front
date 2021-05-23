<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionRol extends Model
{
    protected $connection = 'help';
    protected $table = 'public.division_rol';
    protected $primaryKey = 'id_rol_division';
    public $timestamps = false;

    protected $fillable = ['division_id_division', 'rol_id_rol'];
}
