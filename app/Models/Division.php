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
}
