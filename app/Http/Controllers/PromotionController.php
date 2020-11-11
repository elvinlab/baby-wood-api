<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::all();

        $response=array(
            'status'=>'success',
            'code'=>200,
            'data'=>$promotions
        );

        return response()->json($response,200);

    }


    public function store(Request $request)
    {
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);

        if(!empty($params_array)){

           $validate = \Validator::make($params_array, [
                'tittle' => 'required',  
                'description' => 'required',
                'coupon' => 'required|unique:promotions', 
                'amount' => 'required', 
                'max' => 'required',
                'discount' => 'required',
                'expiry' => 'required',
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
                $promotion->tittle = $params_array['tittle'];
                $promotion->description = $params_array['description'];
                $promotion->coupon = $params_array['coupon'];
                $promotion->image = $params_array['image'];
                $promotion->amount = $params_array['amount'];
                $promotion->max = $params_array['max'];
                $promotion->discount = $params_array['discount'];
                $promotion->expiry = $params_array['expiry'];
                $promotion->save();

                $data = [
                      'code' => 200,
                      'status' => 'success',
                      'promotion' => $params_array
                      ];
  
                      return response()->json($data, $data['code']);

            } else {
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha enviado ninguna promocion.'
                ];
                return response()->json($data, $data['code']);
            }
        
    
        }

    public function show($id)
    {
        $promotion = Promotion::find($id);

        if(is_object($promotion)){
           
            $data = [
                'code'=> 200,
                'status' => 'success',
                'message' => 'La promocion existe, info de la promocion',
                'data' => $promotion,
            ];

        } else {
            $data = [
                'code'=> 404,
                'status' => 'error',
                'message' => 'La promocion no existe',
            ];
        }

        return response()->json($data,$data['code']);
      
    }
    
    public function update(Request $request, $id)
    {
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        


        if(!empty($params_array)){
           
            $params_array = array_map('trim', $params_array);
 
            $validate = \Validator::make($params_array, [
                'tittle' => 'required',  
                'description' => 'required',
                'coupon' => 'required|unique:promotions', 
                'amount' => 'required', 
                'max' => 'required',
                'discount' => 'required',
                'expiry' => 'required',

            ]);
            if($validate->fails()){
               $data=[
                    'status' => '400',
                    'message' => 'Error al validar los datos',
                    'error' => $validate->errors()->first()
               ];
               
            } else {
                unset($params_array['tittle']);
                unset($params_array['description']);
                unset($params_array['amount']);
                unset($params_array['max']);
                unset($params_array['discount']);
                unset($params_array['expiry']);
                $params_array->save();

                $promotion_update = Promotion::where('id'.$promotion->id)-update($params_array);
                
              $data=[
                    'status' => 'succes',
                    'message' =>'Datos actualizados correctamente',
                    'data'=> $promotion_update,
              ];
              
            }
        } else {
           $data=[
               'code' => 400,
               'status' => 'error',
               'massege' => 'Error, los datos enviados no son correctos.'
           ];
          
    }
}


public function destroy(Request $request, $id)
    {
        
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);
        
        if (!empty($promotion)>0){
            Promotion::delete('delete from promotion where id=?',[$id]);

            $data =[
                'code'=>200,
                'status'=> 'success',
                'Promotion'=> $promotion
            ];
        }else{
            $data=[
                'code'=> 404,
                'status'=>'success',
                'promotion'=>'La promocion no existe'
            ];
        }
        return response()->json($data, $data['code']);
        //
    
}

}