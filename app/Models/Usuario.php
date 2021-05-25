<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Usuario extends Model
{
    protected $connection = 'help';
    protected $table = 'public.usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = ['nombre', 'ap_paterno', 'ap_materno', 'email', 'password', 'rol_id_rol', 'cargo_id_cargo'];
    protected $hidden = ['password'];

    /*
       Añadiremos estos dos métodos
   */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

}
