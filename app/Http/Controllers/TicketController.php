<?php

namespace App\Http\Controllers;

use App\Models\Asignado;
use App\Models\CalificacionTicket;
use App\Models\Estado;
use App\Models\Requerimiento;
use App\Models\Rol;
use App\Models\Ticket;
use App\User;
use Illuminate\Http\Request;
use Namshi\JOSE\SimpleJWS;

class TicketController extends Controller
{
    public function listadoReq(Request $request)
    {
        //se obtiene la division del usuario
        $usuario = User::where('email', $request->input('email'))
            ->where('baja_logica', false)
            ->first();

        $tickets = Ticket::listadoTikets($usuario->division_id_division);
        return response()->json([
            'respuesta' => true,
            'tickets' => $tickets
        ]);
    }

    public function editar(Request $request)
    {
        $ticket = Ticket::findOrFail($request->id);
        $ticket->baja_logica = true;
        $ticket->save();
    }

    public function elegirTicket(Request $request)
    {
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        if ($ticket->estado_id_estado == Estado::EN_ESPERA) {
            $ticket->fecha_registro = date('d/m/Y h:i:sa');
            $ticket->save();
            $respuesta = User::where('email', $request->input('email'))
                ->where('baja_logica', false)
                ->first();
            $usuario = "$respuesta->nombre $respuesta->ap_paterno";
        } else {
            $respuesta = Ticket::usuarioTicket($ticket->id_ticket);
            foreach ($respuesta as $item) {
                $usuario = $item->usuario;
            }
        }
        return response()->json([
            'respuesta' => true,
            'usuario' => $usuario,
            'fechaTicket' => $ticket->fecha_registro
        ]);
    }

    public function tomarTicket(Request $request)
    {
        //validando que solamente un AGENTE puede tomar un ticket
        if (($usuario = $this->obtieneIdUsuario($request->input('email'), Rol::AGENTE)) == null) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para la asignacion de Ticket'
            ]);
        }
        //buscamos el anterior ticket para inactivarlo
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        $ticket->activo = Ticket::INACTIVO;
        $ticket->save();
        //cramos el nuevo ticket para que se convierta en el Activo
        $ticketActivo = new Ticket();
        $ticketActivo->id_padre = $ticket->id_ticket;
        $ticketActivo->numero = $ticket->numero;
        $ticketActivo->estado_id_estado = Estado::EN_PROCESO;
        $ticketActivo->requerimiento_id_requerimiento = $ticket->requerimiento_id_requerimiento;
        $ticketActivo->comentarios = $request->input('comentario');
        $ticketActivo->save();
        //Completamos en la tabla Asignado
        $asignado = new Asignado();
        $asignado->usuario_id_usuario = $usuario->id_usuario;
        $asignado->ticket_id_ticket = $ticketActivo->id_ticket;
        $asignado->fecha = date('d/m/Y');
        //TODO
        $asignado->asignado = '';
        $asignado->save();
        //se prepara el correo para el solicitante a su cuenta
        $detalles = [
            'titulo' => 'Alerta',
            'body' => "Su solicitud fue tomada por $usuario->nombre $usuario->ap_paterno $usuario->ap_materno"
        ];
        $requerimiento = Requerimiento::findOrFail($ticket->requerimiento_id_requerimiento);
        $usuarioRequerimiento = User::findOrFail($requerimiento->usuario_id_usuario);
        \Mail::to($usuarioRequerimiento->email)->send(new \App\Mail\InvoiceMail($detalles));
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket tomado con exito'
        ]);
    }

    public function terminarTicket(Request $request)
    {
        //validando que solamente un AGENTE puede tomar un ticket
        if (($usuario = $this->obtieneIdUsuario($request->input('email'), Rol::AGENTE)) == null) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para la asignacion de Ticket'
            ]);
        }
        //buscamos el anterior ticket para inactivarlo
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        $ticket->activo = Ticket::INACTIVO;
        $ticket->save();
        //cramos el nuevo ticket para que se convierta en el Activo
        $ticketActivo = new Ticket();
        $ticketActivo->id_padre = $ticket->id_padre;
        $ticketActivo->numero = $ticket->numero;
        $ticketActivo->estado_id_estado = Estado::CERRADO;
        $ticketActivo->requerimiento_id_requerimiento = $ticket->requerimiento_id_requerimiento;
        $ticketActivo->comentarios = $request->input('comentario');
        $ticketActivo->save();
        //Completamos en la tabla Asignado
        $asignado = new Asignado();
        $asignado->usuario_id_usuario = $usuario->id_usuario;
        $asignado->ticket_id_ticket = $ticketActivo->id_ticket;
        $asignado->fecha = date('d/m/Y');
        //TODO
        $asignado->asignado = '';
        $asignado->save();
        //se prepara el correo para el solicitante a su cuenta
        $detalles = [
            'titulo' => 'Alerta',
            'body' => "Su solicitud fue terminado por $usuario->nombre $usuario->ap_paterno $usuario->ap_materno"
        ];
        $requerimiento = Requerimiento::findOrFail($ticket->requerimiento_id_requerimiento);
        $usuarioRequerimiento = User::findOrFail($requerimiento->usuario_id_usuario);
        \Mail::to($usuarioRequerimiento->email)->send(new \App\Mail\InvoiceMail($detalles));
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket terminado con exito'
        ]);
    }

    private function obtieneIdUsuario($email, $idRol)
    {
        $usuario = User::where('email', $email)
            ->where('baja_logica', false)
            ->where('rol_id_rol', $idRol)
            ->first();
        if ($usuario == null) return null;
        else return $usuario;
    }

    private function validaToken($token)
    {
        $secreto = config('jwt.secret');
        $jws = SimpleJWS::load($token);
        if (!$jws->isValid($secreto)) {
            return true;
        }
        return false;
    }

    public function verSolicitudes(Request $request)
    {
        //validamos el token enviado
        if ($this->validaToken($request->input('token'))) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Tiempo de sesión ha terminado'
            ]);
        }
        //validamos el usurio tipo FUncionario
        if (($usuario = $this->obtieneIdUsuario($request->input('email'), Rol::FUNCIONARIO)) == null) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        $tickets = Ticket::listadoTicketFuncionario($usuario->id_usuario);
        return response()->json([
            'respuesta' => true,
            'tickets' => $tickets
        ]);
    }

    public function calificacion(Request $request)
    {
        //validamos el token enviado
        if ($this->validaToken($request->input('token'))) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Tiempo de sesión ha terminado'
            ]);
        }
        //validamos el usurio tipo FUncionario
        if (($idUsuario = $this->obtieneIdUsuario($request->input('email'), Rol::FUNCIONARIO)) == null) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        //consultamos que no haya sido calificada anteriormenete
        $consulta = CalificacionTicket::where('ticket_id_ticket', $request->input('ticket'))
            ->where('usuario_id_usuario', $idUsuario)
            ->first();
        if ($consulta != null) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Evaluación ya calificada'
            ]);
        }
        CalificacionTicket::create([
            'calificacion_id_calificacion' => $request->input('calificacion'),
            'ticket_id_ticket' => $request->input('ticket'),
            'usuario_id_usuario' => $idUsuario
        ]);
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket calificado con exito'
        ]);
    }

    public function historico(Request $request)
    {
        $tickets = Ticket::historial($request->input('numero'));
        foreach ($tickets as $ticket) {
            $idRequerimiento = $ticket->requerimiento_id_requerimiento;
        }
        $requerimientos = Requerimiento::requerimiento($idRequerimiento);
        return response()->json([
            'respuesta' => true,
            'tickets' => $tickets,
            'requerimiento' => $requerimientos[0]
        ]);
    }

    public function ticket($id_ticket)
    {
        $requerimiento = Requerimiento::findOrFail($id_ticket);
        $query = Requerimiento::requerimientoDetalle($requerimiento->id_requerimiento);
        foreach ($query as $item) {
            $requerimientoDetalle = $item;
        }
        return response()->json([
            'respuesta' => true,
            'requerimiento' => $requerimientoDetalle
        ]);
    }

    public function cambiarEstado(Request $request)
    {
        $tickets = Ticket::where('numero', $request->input('numero'))
            ->get();
        foreach ($tickets as $ticket) {
            if ($ticket->id_padre == null) {
                $ticket->activo = true;
            } else {
                $ticket->activo = false;
                $ticket->baja_logica = true;
                $asignados = Asignado::where('ticket_id_ticket', $ticket->id_ticket)
                    ->where('baja_logica', false)
                    ->get();
                foreach ($asignados as $asignado) {
                    $asignado->baja_logica = true;
                    $asignado->save();
                }
            }
            $ticket->save();
        }
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Se cambio el estado del Ticket'
        ]);
    }

    public function cambioAgente(Request $request)
    {
        $asignados = Asignado::where('ticket_id_ticket', $request->input('id_ticket'))
            ->where('baja_logica', false)
            ->get();
        foreach ($asignados as $asignado) {
            $asignado->usuario_id_usuario = $request->input('id_usuario');
            $asignado->save();
        }
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Se cambio el Agente del Ticket'
        ]);
    }

    public function ticketProceso(){
        $tickets = Ticket::ticketsProceso();
        $correos = [];
        foreach ($tickets as $ticket) {
            if ($ticket->dias_pasados >= 3){
                //se prepara el correo para el solicitante a su cuenta
                $detalles = [
                    'titulo' => 'Alerta de Ticket en Proceso',
                    'body' => "Sr. $ticket->nombre $ticket->ap_paterno: \n Tiene el ticket Nª: $ticket->numero en proceso hace mas de 3 dias, tiene que TERMINAR"
                ];
                \Mail::to($ticket->email)->send(new \App\Mail\InvoiceMail($detalles));
                array_push($correos, $ticket->email);
            }
        }
        if (count($correos) == 0){
            return response()->json([
                'mensaje' => 'No hay tickets de mora'
            ]);
        }
        return response()->json([
            'mensaje' => 'Se ha enviado correos a los tickets en proceso con mora',
            'correos' => $correos
        ]);
    }

    public function ticketEnEspera(){
        $tickets = Ticket::ticketsEnEspera();
        $correos = [];
        foreach ($tickets as $ticket) {
            if ($ticket->dias_pasados >= 3){
                //se prepara el correo para el solicitante a su cuenta
                $detalles = [
                    'titulo' => 'Alerta de Ticket en Espera',
                    'body' => "Sr. $ticket->nombre $ticket->ap_paterno: \n Tiene el ticket Nª: $ticket->numero en espera hace mas de 3 dias, tiene que TOMARLO"
                ];
                \Mail::to($ticket->email)->send(new \App\Mail\InvoiceMail($detalles));
                array_push($correos, $ticket->email);
            }
        }
        if (count($correos) == 0){
            return response()->json([
                'mensaje' => 'No hay tickets de mora'
            ]);
        }
        return response()->json([
            'mensaje' => 'Se ha enviado correos a los tickets en espera con mora',
            'correos' => $correos
        ]);
    }
}
