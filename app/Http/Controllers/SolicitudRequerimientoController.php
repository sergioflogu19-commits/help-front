<?php

namespace App\Http\Controllers;

use App\Models\DptoSucursal;
use App\Models\Requerimiento;
use Illuminate\Http\Request;
use Namshi\JOSE\SimpleJWS;

class SolicitudRequerimientoController extends Controller
{
    public function solicitudReq(Request $request){
        $secreto = config('jwt.secret');
        $jws = SimpleJWS::load($request->input('token'));
        if (!$jws->isValid($secreto)){
            return  response()->json([
                'respuesta' => false,
                'message' => 'Tiempo de sesión ha terminado'
            ]);
        }

        DptoSucursal::create([
            'sucursal_id_sucursal' => $request['sucursal_id_sucursal'],
            'departamento_id_departamento' => $request['departamento_id_departamento'],
        ]);

        $requerimiento = Requerimiento::create([
            'descripcion' => $request->input('descripcion'),
            'interno' => $request->input('interno'),
            'usuario_id_usuario' => $request->input('usuario_id_usuario'),
            'departamento_id_departamento' => $request->input('departamento_id_departamento'),
            'tipo_requerimiento_id_tipo_req' => $request->input('tipo_requerimiento_id_tipo_req'),
        ]);
        if ($requerimiento){
            return response()->json([
                'respuesta' => true,
                'requerimiento' => $requerimiento
            ]);
        }
        return response()->json([
            'respuesta' => false,
            'mensaje' => 'Error al guardar los datos en la Base de Datos'
        ]);
    }
}
