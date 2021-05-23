<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $connection = 'help';
    protected $table = 'public.ticket';
    protected $primaryKey = 'id_ticket';
    public $timestamps = false;

    protected $fillable = ['numero', 'estado_id_estado', 'requerimiento_id_requerimiento', 'comentarios'];
}
