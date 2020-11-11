<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DirectionController extends Controller
{
    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if (!empty($params) && !empty($params_array)) {
            $validate = Validator::make($params_array, [
                'country'   => 'required',
                'province'  => 'required',
                'city'      => 'required',
                'zipCode'   => 'required',
                'streetAddress' => 'required',
            ]);
            if ($validate->fails()) {
                $data = array(
                    'status'    => 'error',
                    'code'      => 404,
                    'message'   => 'Error, hay campos vacíos.',
                    'data'      => $validate->errors()
                );
            } else {
                $direction = new Direction();
                $direction->clientId = auth()->user()->id;
                $direction->country = $params->country;
                $direction->province = $params->province;
                $direction->city = $params->city;
                $direction->zipCode = $params->zipCode;
                $direction->streetAddress = $params->streetAddress;
                $direction->save();
                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'message'   => 'Dirección registrada correctamente.',
                    'data'      => $params_array
                ];
            }
        } else {
            $data = [
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'No has ingresado ningún dato.'
            ];
        }
        return response()->json($data, $data['code']);
    }
    public function show($id)
    {
        $direction = Direction::find($id);
        if (is_object($direction)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'data' => $direction
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Direccion no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function update(Request $request, $id)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        );

        if (!empty($params_array)) {

            $validate = Validator::make($params_array, [
                'country'   => 'required',
                'province'  => 'required',
                'city'      => 'required',
                'zipCode'   => 'required',
                'streetAddress' => 'required',
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            unset($params_array['id']);
            unset($params_array['clientId']);
            unset($params_array['created_at']);
            unset($params_array['client']);

            $direction = Direction::where('id', $id)->where('clientId', auth()->user()->id)->first();

            if (!empty($direction) && is_object($direction)) {

                $direction->update($params_array);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'direction' => $direction,
                    'changes' => $params_array
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function destroy($id)
    {
        $direction = Direction::where('id', $id)->where('clientId', auth()->user()->id)->first();

        if (!empty($direction)) {

            $direction->delete();

            $data = [
                'code' => 200,
                'status' => 'success',
                'direction' => $direction
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Direccion no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function directionsByClient($id)
    {
        $directions = Direction::where('clientId', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $directions
        ], 200);
    }
}
