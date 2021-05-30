<?php

namespace App\Http\Controllers;

use App\Models\Asignado;
use App\Models\Estado;
use App\Models\Requerimiento;
use App\Models\Rol;
use App\Models\Ticket;
use App\User;
use Illuminate\Http\Request;

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
            $respuesta = User::where('email', $request->input('usuario'))
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
        if ($this->obtieneIdUsuario($request->input('usuario')) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para la asignacion de Ticket'
            ]);
        }
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        $ticket->estado_id_estado = Estado::EN_PROCESO;
        $ticket->save();
        $usuario = User::where('email', $request->input('usuario'))
            ->where('baja_logica', false)
            ->first();

        $asignado = new Asignado();
        $asignado->usuario_id_usuario = $usuario->id_usuario;
        $asignado->ticket_id_ticket = $ticket->id_ticket;
        $asignado->fecha = date('d/m/Y');
        $asignado->asignado = '';
        $asignado->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket tomado con exito'
        ]);
    }

    public function terminarTicket(Request $request){
        if ($this->obtieneIdUsuario($request->input('usuario')) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para la asignacion de Ticket'
            ]);
        }
        $ticket = Ticket::findOrFail($request->input('idTicket'));
        $ticket->estado_id_estado = Estado::CERRADO;
        $ticket->fecha_registro = date('d/m/Y h:i:sa');
        $ticket->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Ticket terminado con exito'
        ]);
    }

    private function obtieneIdUsuario($email){
        $usuario = User::where('email', $email)
            ->where('baja_logica', false)
            ->where('rol_id_rol', Rol::AGENTE)
            ->first();
        if ($usuario == null) return null;
        else return $usuario->id_usuario;
    }
}
