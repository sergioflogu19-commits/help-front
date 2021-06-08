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
    public function listadoReq(){
        $tickets = Ticket::listadoTikets();
        return response()->json([
            'respuesta' => true,
            'tickets' => $tickets
        ]);
    }

    public function editar(Request $request){
        $ticket = Ticket::findOrFail($request->id);
        $ticket->baja_logica = true;
        $ticket->save();
    }

    public function elegirTicket(Request $request){
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        if ($ticket->estado_id_estado == Estado::EN_ESPERA){
            $ticket->fecha_registro = date('d/m/Y h:i:sa');
            $ticket->save();
            $respuesta = User::where('email', $request->input('email'))
                ->where('baja_logica', false)
                ->first();
            $usuario = "$respuesta->nombre $respuesta->ap_paterno";
        }else{
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

    public function tomarTicket(Request $request){
        //validando que solamente un AGENTE puede tomar un ticket
        if (($idUsuario = $this->obtieneIdUsuario($request->input('email'), Rol::AGENTE)) == null){
            return  response()->json([
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
        $ticketActivo->comentarios = $ticket->comentarios;
        $ticketActivo->save();
        //Completamos en la tabla Asignado
        $asignado = new Asignado();
        $asignado->usuario_id_usuario = $idUsuario;
        $asignado->ticket_id_ticket = $ticketActivo->id_ticket;
        $asignado->fecha = date('d/m/Y');
        //TODO
        $asignado->asignado = '';
        $asignado->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket tomado con exito'
        ]);
    }

    public function terminarTicket(Request $request){
        //validando que solamente un AGENTE puede tomar un ticket
        if (($idUsuario = $this->obtieneIdUsuario($request->input('email'), Rol::AGENTE)) == null){
            return  response()->json([
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
        $ticketActivo->comentarios = $ticket->comentarios;
        $ticketActivo->save();
        //Completamos en la tabla Asignado
        $asignado = new Asignado();
        $asignado->usuario_id_usuario = $idUsuario;
        $asignado->ticket_id_ticket = $ticketActivo->id_ticket;
        $asignado->fecha = date('d/m/Y');
        //TODO
        $asignado->asignado = '';
        $asignado->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket terminado con exito'
        ]);
    }

    private function obtieneIdUsuario($email, $idRol){
        $usuario = User::where('email', $email)
            ->where('baja_logica', false)
            ->where('rol_id_rol', $idRol)
            ->first();
        if ($usuario == null) return null;
        else return $usuario->id_usuario;
    }

    private function validaToken($token){
        $secreto = config('jwt.secret');
        $jws = SimpleJWS::load($token);
        if (!$jws->isValid($secreto)){
            return true;
        }
        return false;
    }

    public function verSolicitudes(Request $request){
        //validamos el token enviado
        if ($this->validaToken($request->input('token'))){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Tiempo de sesión ha terminado'
            ]);
        }
        //validamos el usurio tipo FUncionario
        if (($idUsuario = $this->obtieneIdUsuario($request->input('email'), Rol::FUNCIONARIO)) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        $tickets = Ticket::listadoTicketFuncionario($idUsuario);
        return response()->json([
            'respuesta' => true,
            'tickets' => $tickets
        ]);
    }

    public function calificacion(Request $request){
        //validamos el token enviado
        if ($this->validaToken($request->input('token'))){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Tiempo de sesión ha terminado'
            ]);
        }
        //validamos el usurio tipo FUncionario
        if (($idUsuario = $this->obtieneIdUsuario($request->input('email'), Rol::FUNCIONARIO)) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        //consultamos que no haya sido calificada anteriormenete
        $consulta = CalificacionTicket::where('ticket_id_ticket', $request->input('ticket'))
            ->where('usuario_id_usuario', $idUsuario)
            ->first();
        if ($consulta != null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Evaluación ya calificada'
            ]);
        }
        CalificacionTicket::create([
            'calificacion_id_calificacion' => $request->input('calificacion'),
            'ticket_id_ticket' => $request->input('ticket'),
            'usuario_id_usuario' => $idUsuario
        ]);
        return  response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket calificado con exito'
        ]);
    }
}
