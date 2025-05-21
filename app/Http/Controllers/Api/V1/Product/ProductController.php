<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

use DB;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
	public function storeProductCategory(Request $request)
	{
		$request->validate([
			'name' => 'required|string',
			'code' => 'required'
		]);

		DB::beginTransaction();

		try{
			 $category = new Category();
			 $category->name = $request->name;
			 $category->code = $request->code;
			 $category->save();

			DB::commit();

			return response()->json([
				'success'=>true,
				'status_code'=>200,
				'message'=>'Product Category added Successfully',
				'data'=> $category
			]);
		}
		catch(\Exception $e){
			DB::rollback();
			return response()->json([
				'succcess' => false,
				'status_code'=>500,
				'message'=> 'Something Went Wrong' .$e->getMessage()
			])
		}
	}




    public function storeProduct(Request $request)
    {
    	$request->validate([
    		'name' =>  'required|string|max:250',
    		'cost' =>  'nullable',
    		'price'=> 'required',

    	]);
    }
}
