<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return response()->json([
                'code' => 200,
                'status' => 'success',
                'products' => $products
        ],200);

    }

    public function store(Request $request)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'categoryId'    => 'required',
                'name'          => 'required|unique:products',
                'price'         => 'required',
                'amount'        => 'required',
                'description'   => 'required',
                'wood'          => 'required',
                'woodFinish'    => 'required',
            ]);

            if($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Faltan datos o son invalidos.',
                    'errors' =>  $validate->errors()
                ];
            } else {
                $product = new Product();
                $product->categoryId = $params_array['categoryId'];
                $product->name = $params_array['name'];
                $product->price = $params_array['price'];
                $product->amount = $params_array['amount'];
                $product->description = $params_array['description'];
                $product->wood = $params_array['wood'];
                $product->woodFinish = $params_array['woodFinish'];
                $product->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'product' => $product
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No has enviado ningun producto.'
            ];
        }

        return response()->json($data, $data['code']);

    }

    public function show(Product $product)
    {

    }

    public function update(Request $request,  $id)
    {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true, JSON_UNESCAPED_UNICODE);

        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectos'
        );

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'categoryId'    => 'required',
                'name'          => 'required|unique:products',
                'price'         => 'required',
                'amount'        => 'required',
                'description'   => 'required',
                'wood'          => 'required',
                'woodFinish'    => 'required',
            ]);

            if($validate->fails()){
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }

            unset($params_array['id']);
            unset($params_array['created_at']);

            $product = Product::where('id', $id)->first();

            if(!empty($product) && is_object($product)){

                $product->update($params_array);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'product' => $product,
                    'changes' => $params_array
                );
            }

        }

        return response()->json($data, $data['code']);
    }

    public function destroy(Product $product)
    {

    }


    public function upload(Request $request, $id)
    {
        $image = $request->file('file0');


        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if (!$image || $validate->fails()) {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('products')->put($image_name, \File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        $isset = \Storage::disk('products')->exists($filename);

        if($isset){

            $file = \Storage::disk('images')->get($filename);

            return new Response($file, 200);
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function getProductByCategory($id){
        $products = Product::where('categoryId', $id)->get();

        return response()->json([
            'status' => 'success',
            'products' => $products
        ], 200);
    }

}
