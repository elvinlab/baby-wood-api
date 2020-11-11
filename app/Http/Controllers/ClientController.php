<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function validateData($parameters_array)
    {

        return Validator::make($parameters_array, [
            'name'              => 'required|alpha',
            'surname'           => 'required',
            'gender'            => 'required|alpha',
            'birth_year'        => 'required',
            'email'             => 'required|email',
            'password'          => 'required|min:6',
            'cel'               => 'required',
            'tel'               => 'required'
        ]);
    }

    public function register(Request $request)
    {

        $json = $request->input('json', null);

        $parameters_object = json_decode($json);
        $parameters_array = json_decode($json, true);

        if (!empty($parameters_object) && !empty($parameters_array)) {

            $parameters_array = array_map('trim', $parameters_array);

            if ($this->validateData($parameters_array)->fails()) {

                return response(array(
                    'status'  => 'error',
                    'message' => 'Error al validar los datos.',
                    'error'   => $this->validateData($parameters_array)->errors()->first()
                ), 422);
            } else if ($this->validEmail($parameters_object->email)) {

                return response(array(
                    'status'  => 'error',
                    'message' => 'Correo ya registrado',
                ), 422);
            } else {

                $client                 = new Client();
                $client->name           = $parameters_array['name'];
                $client->surname        = $parameters_array['surname'];
                $client->gender         = $parameters_array['gender'];
                $client->birth_year     = $parameters_array['birth_year'];
                $client->email          = $parameters_array['email'];
                $client->password       = bcrypt($parameters_array['password']);
                $client->cel            = $parameters_array['cel'];
                $client->tel            = $parameters_array['tel'];
                $client->save();

                return response(array(
                    'status'  => 'success',
                    'message' => 'Bienvenidos a la familia Baby Wood',
                    'data'    => $client,
                    "token" => $client->createToken('Token personal de acceso', ['client'])->accessToken,
                ), 201);
            }
        } else {

            return response(array(
                'status'  => 'error',
                'message' => 'Error, los datos enviados no son correctos.'
            ), 400);
        }
    }

    public function register_login_fb_google(Request $request)
    {

        $json = $request->input('json', null);

        $parameters_object = json_decode($json);
        $parameters_array = json_decode($json, true);

        if (!empty($parameters_object) && !empty($parameters_array)) {

            $parameters_array = array_map('trim', $parameters_array);

            if ($this->validateData($parameters_array)->fails()) {

                return response(array(
                    'status'  => 'error',
                    'message' => 'Error al validar los datos.',
                    'error'   => $this->validateData($parameters_array)->errors()->first()
                ), 422);
            } else if ($this->validEmail($parameters_object->email)) {


                $client = Client::where('email', $parameters_object->email)->first();

                $client->markEmailAsVerified();

                return response(array(
                    'status'  => 'success',
                    'message' => 'Es un gusto tenerte de nuevo',
                    'data'    => $client,
                    "token" => $client->createToken('Token personal de acceso', ['client'])->accessToken,
                ), 201);
            } else {

                $client                 = new Client();
                $client->name           = $parameters_array['name'];
                $client->surname        = $parameters_array['surname'];
                $client->gender         = $parameters_array['gender'];
                $client->birth_year     = $parameters_array['birth_year'];
                $client->email          = $parameters_array['email'];
                $client->password       = bcrypt($parameters_array['password']);
                $client->cel            = $parameters_array['cel'];
                $client->tel            = $parameters_array['tel'];
                $client->save();

                $client->markEmailAsVerified();

                return response(array(
                    'status'  => 'success',
                    'message' => 'Bienvenidos a la familia Baby Wood',
                    'data'    => $client,
                    "token" => $client->createToken('Token personal de acceso', ['client'])->accessToken,
                ), 201);
            }
        } else {

            return response(array(
                'status'  => 'error',
                'message' => 'Error, los datos enviados no son correctos.'
            ), 400);
        }
    }

    public function login(Request $request)
    {

        $json = $request->input('json', null);

        $params_object = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_object) && !empty($params_array)) {

            $validate = Validator::make($params_array, [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validate->fails()) {

                return response(array(
                    'status' => 'error',
                    'message' => 'El cliente no se ha podido identificar',
                    'errors' => $validate->errors()
                ), 404);
            }

            if (Client::where('email', $params_object->email)->count() <= 0) {

                return response(array(
                    'status' => 'error',
                    "message" => "Cliente no existe",
                ), 404);
            }

            $client = Client::where('email', $params_object->email)->first();

            if (!$client->hasVerifiedEmail()) {

                $client->sendEmailVerificationNotification();

                return response(array(
                    "status" => "error",
                    "message" => "Email no verificado, revisar corrreo"
                ), 400);
            }

            if (password_verify($params_object->password, $client->password)) {

                return response(array(
                    "status" => "success",
                    "message" => "Acceso exitoso",
                    "client" => $client,
                    "token" => $client->createToken('Token personal de acceso', ['client'])->accessToken
                ), 200);
            } else {

                return response(array(
                    "status" => "error",
                    "message" => "Credenciales incorrectos."
                ), 400);
            }
        } else {

            return response(array(
                'status'  => 'error',
                'message' => 'Error, los datos enviados no son correctos.'
            ), 400);
        }
    }

    public function logout()
    {

        if (Auth::user()) {

            $user = Auth::user()->token();
            $user->revoke();


            return response(array(
                "status" => "success",
                "message" => "Hasta la proxima",
            ), 200);
        } else {

            return response(array(
                "status" => "error",
                "message" => "Ocurrio un error al cerrar sesion"
            ), 400);
        }
    }

    public function sendPasswordResetEmail(Request $request)
    {

        if (!$this->validEmail($request->email)) {

            return response()->json([
                "status" => "error",
                'message' => 'El correo electrónico no existe.'
            ], Response::HTTP_NOT_FOUND);
        } else {

            $this->sendMail($request->email);

            return response()->json(
                [
                    "status" => "success",
                    'message' => 'Revise su bandeja de entrada, hemos enviado un enlace para restablecer el correo electrónico.'
                ],
                Response::HTTP_OK
            );
        }
    }

    public function sendMail($email)
    {

        $token = $this->generateToken($email);
        Mail::to($email)->send(new SendMail($token));
    }

    public function validEmail($email)
    {

        return !!Client::where('email', $email)->first();
    }

    public function generateToken($email)
    {

        $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

        if ($isOtherToken) {

            return $isOtherToken->token;
        }

        $token = Str::random(80);
        $this->storeToken($token, $email);

        return $token;
    }

    public function storeToken($token, $email)
    {

        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function passwordResetProcess(UpdatePasswordRequest $request)
    {

        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }

    private function updatePasswordRow($request)
    {

        return DB::table('password_resets')->where([

            'email' => $request->email,
            'token' => $request->passwordToken

        ]);
    }

    private function tokenNotFoundError()
    {

        return response()->json([
            "status" => "error",
            'message' => 'Su correo electrónico o token es incorrecto.'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function resetPassword($request)
    {

        $clientData = Client::whereEmail($request->email)->first();

        $clientData->update([
            'password' => bcrypt($request->password)
        ]);

        $this->updatePasswordRow($request)->delete();

        return response()->json([
            "status" => "success",
            'message' => 'Se actualizó la contraseña.'
        ], Response::HTTP_CREATED);
    }

    public function verify($client_id, Request $request)
    {

        if (!$request->hasValidSignature()) {

            return response()->json([
                "status" => "error",
                "message" => "Se ha proporcionado una URL no válida o caducada."
            ], 400);
        }

        $client = Client::findOrFail($client_id);

        if (!$client->hasVerifiedEmail()) {
            $client->markEmailAsVerified();
        }

        return view('verifyMail');
    }

    public function resend()
    {

        if (auth()->user()->hasVerifiedEmail()) {

            return response()->json([
                "status" => "error",
                "message" => "Correo electrónico ya verificado."
            ], 400);
        }

        auth()->user()->SendEmailVerificationNotification();

        return response()->json([
            "status" => "success",
            "message" => "Enlace de verificación de correo electrónico enviado en su identificación de correo electrónico"
        ], 200);
    }


    public function clientInfo()
    {

        $client = auth()->user();

        return response(array(
            "status" => "success",
            "message" => "Informacion de cliente",
            "client" => $client,
        ), 200);
    }

    public function index()
    {
    }

    public function update(Request $request, Client $client)
    {

        $json = $request->input('json', null);

        $parameters_object = json_decode($json);
        $parameters_array = json_decode($json, true);

        $client = auth()->user();

        if (!empty($parameters_object) && !empty($parameters_array)) {

            $parameters_array = array_map('trim', $parameters_array);

            $validate = Validator::make($parameters_array, [
                'name'              => 'required|alpha',
                'surname'           => 'required',
                'gender'            => 'required|alpha',
                'birth_year'        => 'required',
                'email'             => 'required|unique:clients,email,' . $client->id,
                'password'          => 'required|min:6',
                'cel'               => 'required',
                'tel'               => 'required',
                'country'           => 'required',
                'province'          => 'required',
                'city'              => 'required',
                'postal_code'       => 'required',
                'street_address'    => 'required',
            ]);

            if ($validate->fails()) {

                return response(array(
                    'status'  => 'error',
                    'message' => 'Error al validar los datos.',
                    'error'   => $validate->errors()->first()
                ), 422);
            } else {

                unset($parameters_array['id']);
                unset($parameters_array['role']);
                unset($parameters_array['created_at']);
                unset($parameters_array['remember_token']);

                $parameters_array['password'] =  bcrypt($parameters_array['password']);

                $client_update = Client::where('id', $client->id)->update($parameters_array);

                return response(array(
                    'status'  => 'success',
                    'message' => 'Datos actualizados correctamente',
                    'data'    => $client_update,
                ), 201);
            }
        } else {

            return response(array(
                'status'  => 'error',
                'message' => 'Error, los datos enviados no son correctos.'
            ), 400);
        }
    }
}
