<?php

namespace App\Http\Controllers;
use App\Models\Rol;
use App\Models\Usuario;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Namshi\JOSE\SimpleJWS;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioController extends Controller
{
    public $loginAfterSignUp = true;

    public function registro(Request $request)
    {
        $usuario = new User();
        $usuario->nombre = $request->input('nombre');
        $usuario->ap_paterno = $request->input('ap_paterno');
        $usuario->ap_materno = $request->input('ap_materno');
        $usuario->email = $request->input('email');
        $usuario->password = bcrypt($request->input('password'));
        $usuario->rol_id_rol = Rol::FUNCIONARIO;
        $usuario->cargo_id_cargo = $request->input('cargo_id_cargo');
        $usuario->division_id_division = $request->input('division_id_division');
        $usuario->save();

        if ($this->loginAfterSignUp) return $this->login($request);
        return  response()->json([
            'respuesta' => true,
            'usuario' => $usuario
        ]);
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($input)){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Correo o contraseña no válidos'
            ]);
        }
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Inicio de sesion autorizado',
            'token' => $jwt_token
        ]);
    }

    public function logout(Request $request){
        $this->validate($request, [
           'token' => 'required'
        ]);
        try{
            $secreto = config('jwt.secret');
            $jws = SimpleJWS::load($request->input('token'));
            if (!$jws->isValid($secreto)){

            }

            JWTAuth::invalidate($request->input('token'));
            return response()->json([
                'respuesta' => true,
                'mensaje' => 'Cierre de sesión exitoso'
            ]);
        }catch (JWTException  $exception) {
            return  response()->json([
                'respuesta' => false,
                'message' => 'Al usuario no se le pudo cerrar la sesión'
            ]);
        }
    }
}
