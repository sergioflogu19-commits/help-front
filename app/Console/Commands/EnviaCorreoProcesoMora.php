<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class EnviaCorreoProcesoMora extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proceso:agentes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia correo electronico a los agentes que tomaron el proceso y paso mas de 3 dias';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tickets = Ticket::ticketsProceso();
        foreach ($tickets as $ticket) {
            if ($ticket->dias_pasados >= 3){
                //se prepara el correo para el solicitante a su cuenta
                $detalles = [
                    'titulo' => 'Alerta de Ticket en Espera',
                    'body' => "Sr. $ticket->nombre $ticket->ap_paterno: \n Tiene el ticket Nª: $ticket->numero en espera hace mas de 3 dias, tiene que TERMINAR"
                ];
                \Mail::to($ticket->email)->send(new \App\Mail\InvoiceMail($detalles));
            }
        }
    }
}
