<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;

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
			]);
		}
	}




   public function storeProduct(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:250',
        'cost' => 'nullable|numeric',
        'price' => 'required|numeric',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    DB::beginTransaction();

    try {
        $product = new Product();
        $product->type = 'is_single';
        $product->code = $request->code;
        $product->name = $request->name;
        $product->cost = $request->cost;
        $product->price = $request->price;

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = $path;
        }

        $product->category_id = $request->category_id;
        $product->brand_id = 1;
        $product->unit_id = 1;
        $product->unit_sale_id = 1;
        $product->unit_purchase_id = 1;
        $product->save();

        DB::commit();

        return response()->json(['message' => 'Product created successfully.'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Something went wrong.', 'details' => $e->getMessage()], 500);
    }
}


}