<?php

namespace App\Http\Controllers;
use App\Models\Division;
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
        $usuario->division_id_division = Division::OTROS;
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
            'token' => $jwt_token,
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

    public function recursoUsuario(Request $request)
    {
        if ($this->validaToken($request->input('token'))) {
            return response()->json([
                'respuesta' => false,
                'mensaje' => 'Tiempo de sesión ha terminado'
            ]);
        }
        $usuario = User::where('email', $request->input('email'))->first();
        return response()->json([
            'respuesta' => true,
            'id_rol' => $usuario->rol_id_rol
        ]);
    }

    private function validaToken($token){
        $secreto = config('jwt.secret');
        $jws = SimpleJWS::load($token);
        if (!$jws->isValid($secreto)){
            return true;
        }
        return false;
    }

    //para el administrador
    public function index(Request $request){
        if (($this->obtieneIdUsuario($request->input('email'), Rol::ADMINISTRADOR)) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        $users = User::where('baja_logica', false)
            ->orderBy('id_usuario', 'desc')
            ->get();
        return response()->json([
            'respuesta' => true,
            'users' => $users
        ]);
    }

    public function store($id, Request $request ){
        if (($this->obtieneIdUsuario($request->input('usuario'), Rol::ADMINISTRADOR)) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        $user = User::findOrFail($id);
        $user->nombre = $request->input('nombre');
        $user->ap_paterno = $request->input('ap_paterno');
        $user->ap_materno = $request->input('ap_materno');
        $user->ap_materno = $request->input('ap_materno');
        $user->rol_id_rol = $request->input('rol_id_rol');
        $user->cargo_id_cargo = $request->input('cargo_id_cargo');
        $user->division_id_division = $request->input('division_id_division');
        $user->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Usuario editado con exito'
        ]);
    }

    public function eliminar(Request $request){
        if (($this->obtieneIdUsuario($request->input('usuario'), Rol::ADMINISTRADOR)) == null){
            return  response()->json([
                'respuesta' => false,
                'mensaje' => 'Usuario no autorizado para ver las solicitudes'
            ]);
        }
        $user = User::findOrFail($request->input('id_usuario'));
        $user->baja_logica = true;
        $user->save();
        return response()->json([
            'respuesta' => true,
            'mensaje' => 'Usuario eliminado con exito'
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

}
