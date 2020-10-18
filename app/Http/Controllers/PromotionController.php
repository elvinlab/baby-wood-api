<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    //

    public function index()
    {
        //
        $data=Promotion::all();
        $response=array(
            'status'=>'success',
            'code'=>200,
            'data'=>$data
        );
        return response()->json($response,200);
    }


    public function store(Request $request){
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if(!empty($params_array)){

           $validate = \Validator::make($params_array, [
                'max' => 'required',
                'expiry' => 'required',
                'description' => 'required',
                'discount' => 'required',
            ]);

            if ($validate->fails()) {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado la promocion, faltan datos',
                    'error' => $validate->errors()
                ];
                
                return response()->json($data, $data['code']);
            }

                $promotion = new Promotion();
                $promotion->max = $params_array->max;
                $promotion->expiry = $params_array->expiry;
                $promotion->description = $params_array->description;
                $promotion->image = $params_array->image;
                $promotion->discount = $params_array->discount;
                $promotion->save();

                // Si no esoy mal este if va diferenciar entre cliente y administrador
                if (auth()->client()->promotions()->save($promotion)){
                      
                    $data = [
                      'code' => 200,
                      'status' => 'success',
                      'promotion' => $params_array
                      ];
  
                      return response()->json($data, $data['code']);
  
                    }else {
                      $data = [
                          'code' => 400,
                          'status' => 'error',
                          'message' => 'La promocion no se pudo guardar.'
                      ];
  
                      return response()->json($data, $data['code']);
  
                    }
  
              
            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha enviado ninguna promocion.'
                ];
                return response()->json($data, $data['code']);
            }
}

public function show(Promotion $promotion)
    {
        //
    }
    
public function update(Request $request, Promotion $promotion)
    {
        //
    }

public function destroy(Promotion $promotion)
    {
        //
    }
}