<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;

class ClientController extends Controller {

    public function register(Request $request){

        $json = $request->input('json', null);

        $parameters_object = json_decode($json);
        $parameters_array = json_decode($json, true);

        if(!empty($parameters_object) && !empty($parameters_array)){
            
            $parameters_array = array_map('trim', $parameters_array);

            $validate = Validator::make($parameters_array, [
                'name' => 'required|alpha', 
                'email' => 'required|email|unique:clients',
                'password' => 'required|min:6'  
            ]);

            if($validate -> fails() ){
          
                $data = array( 
                  'status'  => 'error',
                  'code'    =>  422,
                  'message' => 'Error al validar los datos.',
                  'error'   => $validate->errors()->first()
                );
    
            } else {
                
                $client           = new Client();
                $client->name     = $parameters_array['name'];  
                $client->email    = $parameters_array['email'];
                $client->password = bcrypt($parameters_array['password']);          
                $client->save(); 

                $data = array( 
                    'status'  => 'success',
                    'code'    =>  201,
                    'data'    => $client,
                    'message' => 'Cliente registrado.',
                    'client'  => $client,
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
                'message' => 'El cliente no se ha podido identificar',
                'errors' => $validate->errors()
            );

            return response()->json($data, $data['code']);

        }

        if(Client::where('email', $params_object->email)->count() <= 0 ) {

            $data = array(
                'status' => 'error',
                'code' => 404,
                "message" => "Cliente no existe",
                'errors' => $validate->errors()
            );
            
            return response()->json($data, $data['code']);
        }
        
        $client = Client::where('email', $params_object->email)->first();

        if(password_verify($params_object->password, $client->password)){
           
            return response( 
                
                array( 
                    "status" => "success", 
                    'code' => 200,
                    "message" => "Acceso exitoso",
                    "client" => $client,
                    "token" => $client->createToken('Token personal de acceso',['client'])->accessToken
            
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
                'code' => 400,
                'message' => 'ocurrio un error al cerrar sesion'
            ]);
        }
    }

    public function clientInfo() {
 
     $client = auth()->user();
         
     return response(       
        array( 
            "status" => "success", 
            'code' => 200,
            "message" => "Informacion de cliente",
            "client" => $client,
        ));
 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        //
    }
}