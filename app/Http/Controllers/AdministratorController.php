<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdministratorController extends Controller{

    public function register(Request $request){

        $json = $request->input('json', null);

        $parameters_object = json_decode($json);
        $parameters_array = json_decode($json, true);

        if(!empty($parameters_object) && !empty($parameters_array)){

            $parameters_array = array_map('trim', $parameters_array);

            $validate = Validator::make($parameters_array, [
                'name' => 'required|alpha',
                'email' => 'required|email|unique:administrators',
                'password' => 'required|min:6',
                'cel' => 'required'
            ]);

            if($validate -> fails() ){

                $data = array(
                  'status'  => 'error',
                  'code'    =>  422,
                  'message' => 'Error al validar los datos.',
                  'error'   => $validate->errors()->first()
                );

            } else {

                $administrator           = new Administrator();
                $administrator->name     = $parameters_array['name'];
                $administrator->email    = $parameters_array['email'];
                $administrator->password = bcrypt($parameters_array['password']);
                $administrator->cel = $parameters_array['cel'];
                $administrator->save();

                $data = array(
                    'status'  => 'success',
                    'code'    =>  201,
                    'data'    => $administrator,
                    'message' => 'Administrador registrado.',
                  );

            }

        }else{
            $data = array(
              'status'  => 'error',
              'code'    =>  400,
              'message' => 'Error, los datos enviados no son correctos.'
            );

          }

          return response()->json($data, $data['code']);
    }

    /**
     * Login Req
     */
    public function login(Request $request){

        $json = $request->input('json', null);

        $params_object = json_decode($json);
        $params_array = json_decode($json, true);

        if(!empty($parameters_object) && !empty($parameters_array)){

            return response( array(
                "status" => "error",
                'code' => 400,
                "message" => "Credenciales incorrectos." ) );
        }

        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        if ($validate->fails()) {

            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El administra$administratore no se ha podido identificar',
                'errors' => $validate->errors()
            );

            return response()->json($data, $data['code']);

        }

        if(Administrator::where('email', $params_object->email)->count() <= 0 ){

            $data = array(
                'status' => 'error',
                'code' => 404,
                "message" => "Administador no existe" ,
            );

            return response()->json($data, $data['code']);

        }

        $administrator = Administrator::where('email', $params_object->email)->first();

        if(password_verify($params_object->password, $administrator->password)){

            return response(
                array(
                    "status" => "success",
                    'code' => 200,
                    "message" => "Inicio de sesion exitoso.",
                    "administrator" => $administrator,
                    "token" => $administrator->createToken('Token personal de acceso',['administrator'])->accessToken
                ));

        } else {
            return response( array(
                "status" => "error",
                'code' => 400,
                "message" => "Credenciales incorrectos." ) );
        }
    }

    public function logout(Request $request){

        if (Auth::user()) {

            $user = Auth::user()->token();
            $user->revoke();

        return response()->json([
            "status" => "success",
            'code' => 200,
            'message' => 'Cierre de sesion exitoso'
        ]);

        }else {

            return response()->json([
                "status" => "error",
                'message' => 'ocurrio un error al cerrar sesion'
            ]);

        }
    }

    public function adminInfo() {

        $administrator = auth()->user();

        return response(

            array(
                "status" => "success",
                'code' => 200,
                "message" => "Informacion de usuario.",
                "administrator" => $administrator,
                "token" => $administrator->createToken('Token personal de acceso',['administrator'])->accessToken
            ));

    }


    public function index(){

    }

    public function update(Request $request, Administrator $administrator){

    }

    public function destroy(Administrator $administrator){

    }
}
