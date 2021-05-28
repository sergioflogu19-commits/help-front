<?php

namespace App\Http\Controllers;

use App\Models\Requerimiento;
use App\Models\Ticket;
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
}
