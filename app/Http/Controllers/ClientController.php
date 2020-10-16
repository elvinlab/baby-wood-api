<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\UpdatePasswordRequest;
use App\Mail\WelcomeMail;
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
                'email' => 'required|email',
                'password' => 'required|min:6'  
            ]);

            $client = Client::where('email', $parameters_object->email)->first();

            if($client && $client->email === $parameters_object->email){

                return response( array( 
                        'status'  => 'success',
                        'code'    =>  201,
                        'message' => 'Bienvenido de nuevo',
                        'data'    => $client,
                        "token" => $client->createToken('Token personal de acceso',['client'])->accessToken,
                    ));

            }
            
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

                Mail::to($client->email)->send(new WelcomeMail($client));

                $data = array( 
                    'status'  => 'success',
                    'code'    =>  201,
                    'message' => 'Bienvenidos a la familia Baby Wood',
                    'data'    => $client,
                    "token" => $client->createToken('Token personal de acceso',['client'])->accessToken,
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

        if (!$client->hasVerifiedEmail()) {
           
            $client->markEmailAsVerified();

            return response( array( 
                "status" => "error", 
                'code' => 400,
                "message" => "Email no verificado, revisar corrreo" ) );
        }

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

    public function logout(){
        
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

    public function sendPasswordResetEmail(Request $request){

        if(!$this->validEmail($request->email)) {
            
            return response()->json([
                "status" => "error", 
                'code' => 400,
                'message' => 'El correo electrónico no existe.'
            ], Response::HTTP_NOT_FOUND);

        } else {
          
            $this->sendMail($request->email);

            return response()->json([
                "status" => "success", 
                'code' => 200,
                'message' => 'Revise su bandeja de entrada, hemos enviado un enlace para restablecer el correo electrónico.'],
                 Response::HTTP_OK); 

        }

    }

    public function sendMail($email){

        $token = $this->generateToken($email);
        Mail::to($email)->send(new SendMail($token));

    }

    public function validEmail($email) {

       return !!Client::where('email', $email)->first();

    }
    
    public function generateToken($email){

      $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

      if($isOtherToken) {

        return $isOtherToken->token;

      }

      $token = Str::random(80);
      $this->storeToken($token, $email);

      return $token;
    }

    public function storeToken($token, $email){
        
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);

    }

    public function passwordResetProcess(UpdatePasswordRequest $request){
        
        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
        
    }

    private function updatePasswordRow($request){

         return DB::table('password_resets')->where([
             
            'email' => $request->email,
             'token' => $request->passwordToken

         ]);

    }
  
    private function tokenNotFoundError() {
         
        return response()->json([
            "status" => "error", 
            'code' => 400,
            'message' => 'Su correo electrónico o token es incorrecto.'
          ],Response::HTTP_UNPROCESSABLE_ENTITY);
          
    }
  
      private function resetPassword($request) {

          $clientData = Client::whereEmail($request->email)->first();

          $clientData->update([
            'password'=>bcrypt($request->password)
          ]);

          $this->updatePasswordRow($request)->delete();
  
          return response()->json([
            "status" => "success", 
            'code' => 200,
            'message'=>'Se actualizó la contraseña.'
          ],Response::HTTP_CREATED);

      }

      public function verify($client_id, Request $request) {
       
        if (!$request->hasValidSignature()) {
            
            return response()->json([
                "status" => "error", 
                'code' => 400,
                "message" => "Se ha proporcionado una URL no válida o caducada."]);
        }
    
        $client = Client::findOrFail($client_id);
    
        if (!$client->hasVerifiedEmail()) {
            $client->markEmailAsVerified();
        }
       
        return response()->json([
            "status" => "success", 
            'code' => 200,
            'message' => 'Cuenta verificada exitosamente'
        ]);

    }
    
    public function resend() {

        if (auth()->user()->hasVerifiedEmail()) {

            return response()->json([
                "status" => "error", 
                'code' => 400,
                "message" => "Correo electrónico ya verificado."]);

        }
    
        auth()->user()->sendEmailVerificationNotification();
    
        return response()->json([
            "status" => "success", 
            'code' => 200,
            "message" => "Enlace de verificación de correo electrónico enviado en su identificación de correo electrónico"]);

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

    public function index(){
       
    }

    public function update(Request $request, Client $client){
       
    }

}