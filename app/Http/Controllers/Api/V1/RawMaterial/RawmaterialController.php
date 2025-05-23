<?php

namespace App\Http\Controllers\Api\V1\RawMaterial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\RawMaterial;
use App\Models\RawMaterialCategory;
use App\Models\WeightUnit;

class RawmaterialController extends Controller
{
    public function raw_material_store(Request $request)
    {
    	
    	$request->validate([
    		'name' => 'required|string|max:255',
    		'category_id' => 'required',
    	]);
    	DB::beginTransaction();


    	try{

    		$rawmaterial = new RawMaterial();
    		$rawmaterial->name = $request->name;
    		$rawmaterial->category_id = $request->category_id;
            $rawmaterial->supplier_id = $request->supplier_id;
    		$rawmaterial->save();


    		DB::commit();

    		return response()->json([
    			'success' => true,
    			'status_code'=>200,
    			'message' => 'Raw Material added successfully',
    			'data'	=> $rawmaterial
    		]);
    	}
    		catch(\Exception $e){
    			DB::rollback();
    			return response()->json([
    				'success' => false,
    				'status_code'=>500,
    				'message'=>'Something went wrong' .$e->getMessage()
    			]);
    		}
    	}

   // ==================================Get Raw Material===========================

    	public function get_rawmaterial(Request $request)
    	{
    		DB::beginTransaction();

            $id = $request->supplier_id;

    		try{
    			$RawMaterial = RawMaterial::select('id','name','category_id','supplier_id')->where('status','Active')->where('supplier_id',$id)->get();
    			DB::commit();

    			return response()->json([
    				'success'=>true,
    				'status_code'=>200,
    				'message'=>'Raw material fetch successfully',
    				'data'=> $RawMaterial
    			]);
    		}

    		catch(\Exception $e){
    			DB::rollback();
    			return response()->json([
    				'success' =>false,
    				'status_code'=>500,
    				'message' => 'Something went wrong'  .$e->getMessage()
    			]);
    		}
    	}


// ===============================Add RawMaterial Category===================================
    	public function raw_material_category_store(Request $request)
    	{
    		DB::beginTransaction();

    		try{
    			$category = new  RawMaterialCategory();
    			$category->name = $request->name;
    			$category->save();

    			DB::commit();

    			return response()->json([
    				'success' => true,
    				'status_code'=>200,
    				'message'=>'Raw meterial store successfully',
    				'data'   =>  $category
    			]);
    		}
    		catch(\Exception $e){
    			DB::rollback();
    			return response()->json([
    				'success' => false,
    				'status_code'=>500,
    				'message'=>'Something went wrong' .$e->getMessage()

    			]);
    		}
    	}



   // =========================Get Raw Material Category=========================
    	public function getRawMaterial()
    	{
    		DB::beginTransaction();
    		try{
    			$category = RawMaterialCategory::select('id','name')->where('status','Active')->get();

    			DB::commit();
    			return response()->json([
    				'success'=>true,
    				'status_code'=>200,
    				'message'=>'Raw material fetch successfully',
    				'data' => $category
    			]);
    		}
    		catch(\Exception $e){
    			DB::rollback();
    			return response()->json([
    				'success'  => true,
    				'status_code'=>500,
    				'message' => 'Something went wrong' .$e->getMessage()
    			]);
    		}
    	}

        // ==================Weigh Measures=========================
        public function measure()
        {
            try{
                $weight = WeightUnit::where('status','Active')->get();
                DB::commit();
                    return response()->json([
                    'success' =>true,
                    'status_code'=>200,
                    'message'=> 'Weight Unit Fetch Successfully',
                    'data' => $weight

                ]);
            }
            catch(\Exception $e){
                return response()->json([
                    'success'=>false,
                    'status_code'=>500,
                    'message'=>'Something went wrong' .$e->getMessage()
                ]);
            }
        }

}
