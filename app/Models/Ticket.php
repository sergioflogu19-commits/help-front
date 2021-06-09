<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    protected $connection = 'help';
    protected $table = 'public.ticket';
    protected $primaryKey = 'id_ticket';
    public $timestamps = false;

    protected $fillable = ['numero', 'estado_id_estado', 'requerimiento_id_requerimiento', 'comentarios', 'id_padre', 'activo', 'fecha_registro'];
    protected $hidden = [
        'baja_logica', 'usuario_registro', 'ip_registro'
    ];

    const ACTIVO = true;
    const INACTIVO = false;

    public static function listadoTikets(){
        return DB::connection('help')->select(
            "SELECT a.id_ticket,
                            a.numero,
                            d.categoria,
                            c.sub_cat,
                            a.estado_id_estado,
                            (select estado from public.estado where id_estado = a.estado_id_estado limit 1) estado,
                            c.division_id_division,
                            (select division from public.division where id_division = c.division_id_division limit 1),
                            a.fecha_registro fecha_ticket,
                            b.fecha_registro fecha_solicitud,
                            (select y.nombre || ' ' || y.ap_paterno || ' ' || y.ap_materno
                            from public.asignado x
                            inner join  public.usuario y on x.usuario_id_usuario = y.id_usuario
                            where x.ticket_id_ticket = a.id_ticket
                            limit 1) usuario,
                            b.descripcion,
                            (select departamento from public.departamento where b.departamento_id_departamento = id_departamento limit 1) departamento,
                            (select sucursal from public.sucursal where b.sucursal_id_sucursal = id_sucursal limit 1) sucursal,
                            e.nombre || ' ' || e.ap_paterno || ' ' || e.ap_materno as usuario_requerimiento
                    from public.ticket a        
                    inner join public.requerimiento b on a.requerimiento_id_requerimiento = b.id_requerimiento
                    inner join public.tipo_requerimiento c on b.tipo_requerimiento_id_tipo_req = c.id_tipo_req
                    inner join public.categoria d on c.categoria_id_categoria = d.id_categoria
                    inner join public.usuario e on b.usuario_id_usuario = e.id_usuario
                    where a.baja_logica is false and a.activo is true
                    order by 10 desc"
        );
    }

    public static function usuarioTicket($idTicket){
        return DB::connection('help')->select(
            "select y.nombre || ' ' || y.ap_paterno as usuario
                    from public.asignado x
                    inner join  public.usuario y on x.usuario_id_usuario = y.id_usuario
                    where x.ticket_id_ticket = ?
                    limit 1", [$idTicket]
        );
    }

    public static function listadoTicketFuncionario($idUsuario){
        return DB::connection('help')->select(
            "SELECT a.id_ticket,
                            a.numero,
                            d.categoria,
                            c.sub_cat,
                            a.estado_id_estado,
                            (select estado from public.estado where id_estado = a.estado_id_estado limit 1) estado,
                            c.division_id_division,
                            (select division from public.division where id_division = c.division_id_division limit 1),
                            a.fecha_registro::date fecha_ticket,
                            b.fecha_registro::Date fecha_solicitud,
                            (select y.nombre || ' ' || y.ap_paterno 
                            from public.asignado x
                            inner join  public.usuario y on x.usuario_id_usuario = y.id_usuario
                            where x.ticket_id_ticket = a.id_ticket
                            limit 1) usuario,
                            b.descripcion,
                            (select departamento from public.departamento where b.departamento_id_departamento = id_departamento limit 1) departamento,
                            (select sucursal from public.sucursal where b.sucursal_id_sucursal = id_sucursal limit 1) sucursal
                    from public.ticket a        
                    inner join public.requerimiento b on a.requerimiento_id_requerimiento = b.id_requerimiento
                    inner join public.tipo_requerimiento c on b.tipo_requerimiento_id_tipo_req = c.id_tipo_req
                    inner join public.categoria d on c.categoria_id_categoria = d.id_categoria
                    inner join public.usuario e on b.usuario_id_usuario = e.id_usuario
                    where a.baja_logica is false
                    and a.activo is true 
                    and b.usuario_id_usuario = ?
                    order by 2 desc", [$idUsuario]
        );
    }

    public static function historial($numero){
        return DB::connection('help')->select(
            "(select a.numero, a.estado_id_estado, a.requerimiento_id_requerimiento,
                            a.fecha_registro, a.comentarios, a.activo,
                            c.nombre, c.ap_paterno, c.ap_materno
                    from public.ticket a
                    inner join public.asignado b on a.id_ticket = b.ticket_id_ticket
                    inner join public.usuario c on b.usuario_id_usuario = c.id_usuario
                    where a.numero = ?)
                    union 
                    (select a.numero, a.estado_id_estado, a.requerimiento_id_requerimiento,
                            a.fecha_registro, a.comentarios, a.activo, NULL, null, null
                    from public.ticket a
                    where a.numero = ? and a.id_padre is null)
                    order by fecha_registro", [$numero, $numero]
        );
    }
}
