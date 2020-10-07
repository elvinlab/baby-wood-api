<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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
    public function store(Request $request){

          $json = $request->input('json', null);
          $params_array = json_decode($json, true);
  
          if (!empty($params_array)) {
  
              $validate = \Validator::make($params_array, [
                  'name' => 'required|unique:categories',
              ]);
  
              if ($validate->fails()) {
                  $data = [
                      'code' => 400,
                      'status' => 'error',
                      'message' => 'No se ha guardado la categoria.',
                      'error' => $validate->errors()
                  ];

                return response()->json($data, $data['code']);

              } 
                  
                  $category = new Category();
                  $category->name = $params_array['name'];
                  $category->description = $params_array['description'];
                  $category->save();
                  
                  if (auth()->client()->categories()->save($category)){
                      
                  $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $params_array
                    ];

                    return response()->json($data, $data['code']);

                  }else {
                    $data = [
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'La categoria no se pudo guardar.'
                    ];

                    return response()->json($data, $data['code']);

                  }

            
          } else {
              $data = [
                  'code' => 400,
                  'status' => 'error',
                  'message' => 'No se enviado ninguna categoria.'
              ];
              return response()->json($data, $data['code']);
          }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //
    }
}
