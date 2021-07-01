<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Requerimiento extends Model
{
    protected $connection = 'help';
    protected $table = 'public.requerimiento';
    protected $primaryKey = 'id_requerimiento';
    public $timestamps = false;

    protected $fillable = [
        'descripcion', 'usuario_id_usuario', 'interno', 'tipo_requerimiento_id_tipo_req', 'departamento_id_departamento', 'sucursal_id_sucursal'
    ];
    protected $hidden = [
        'baja_logica', 'fecha_registro', 'usuario_registro', 'ip_registro'
    ];

    public static function listadoRequerimiento(){

    }

    public static function requerimiento($idRequerimiento){
        return DB::connection('help')->select(
            "select  a.id_requerimiento, 
                            a.descripcion,
                            (select nombre || ' ' || ap_paterno || ' ' || ap_materno
                            from public.usuario where id_usuario = a.usuario_id_usuario limit 1) usuario,
                            interno,
                            (select sub_cat
                            from public.tipo_requerimiento where id_tipo_req = a.tipo_requerimiento_id_tipo_req limit 1) requerimiento,
                            (select departamento
                            from public.departamento where id_departamento = a.departamento_id_departamento limit 1) departamento,
                            (select sucursal
                            from public.sucursal where id_sucursal = a.sucursal_id_sucursal limit 1) sucursal,
                            a.fecha_registro        
                    from public.requerimiento a
                    where id_requerimiento = ?", [$idRequerimiento]
        );
    }

    public static function requerimientoDetalle($idRequerimiento){
        return DB::connection('help')->select(
            "SELECT 
                  a.id_requerimiento,
                  a.descripcion,
                  a.usuario_id_usuario,
                  a.interno,
                  a.tipo_requerimiento_id_tipo_req,
                  (select categoria_id_categoria 
                  from public.tipo_requerimiento 
                  where id_tipo_req = a.tipo_requerimiento_id_tipo_req limit 1) categoria_id,
                  a.departamento_id_departamento,
                  a.sucursal_id_sucursal,
                  (select municipio_id_lugar
                  from public.sucursal 
                  where id_sucursal = a.sucursal_id_sucursal limit 1) municipio_id
                FROM public.requerimiento a
                where id_requerimiento = ?", [$idRequerimiento]
        );
    }
}









