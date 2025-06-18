<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use Validation;


class ProviderController extends Controller
{
    public function storeProvider(Request $request)
    {
    	DB::beginTransaction();
    	
    	if(!Auth::user()){
    		return response()->json([
    			'success' => false,
    			'status_code'=> 401,
    			'message' => 'Authentication required'

    		]);
    	}


    	try{

    		$provider = new Provider();
    		$provider->name = $request->name;
    		$provider->email = $request->email;
    		$provider->phone = $request->phone;
    		$provider->country = 'India';
    		$provider->state = $request->state;
    		$provider->district = $request->district;
    		$provider->tehsil = $requst->tehsil;
    		$provider->zipcode = $request->zipcode;
    		$provider->adresse = $request->adresse;
    		$provider->save();

    		DB::commit();
    		return response()->json([
    			'success' => true,
    			'status_code' => 200,
    			'message' => 'Supplier Added Succesfully',
    			'data'    =>  $provider,

    		]);



    	}
    	catch(\Exception $e){
    		 DB::rollBack();
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
    	}

    }


// ======================Get Provider Api==================================

    public function getProvider()
    {
    	DB::beginTransaction();


    	try{
    		$provider = Provider::select('id','name')->where('status','Active')->get();
    		DB::commit();
    		return response()->json([
    			'success' => true,
    			'status_code'=>200,
    			'message'=>'Supplier list fetech successfully',
    			'data' =>  $provider
    		]);
    	}
    	catch(\Exception $e){
    		DB::rollBack();
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ]);
    	}
    }


}
