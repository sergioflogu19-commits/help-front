<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $connection = 'help';
    protected $table = 'public.estado';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = ['estado', 'cod'];
}
