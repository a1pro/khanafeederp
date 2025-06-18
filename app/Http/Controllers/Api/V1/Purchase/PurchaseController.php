<?php

namespace App\Http\Controllers\Api\V1\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Purchase;
use Auth;
use Carbon\Carbon;


class PurchaseController extends Controller
{
    public function storePurchase(Request $request){

     

    	DB::beginTransaction();
    	$userId = Auth::id();
 $now = Carbon::now('Asia/Kolkata');


    	try{

    		$purchase = new Purchase();
    		$purchase->user_id = $userId;
    		$purchase->provider_id = $request->provider_id;
    		$purchase->warehouse_id = 2;
    		$purchase->item_id = $request->item_id;
    		$purchase->quantity = $request->quantity;
    		$purchase->price = $request->price;
            $purchase->measure = $request->measure;
    		$purchase->expected_deliver = $request->expected_deliver;
    		$purchase->date = $now->toDateString();
    		$purchase->save();

    		DB::commit();
    		return response()->json([
    			'success' => true,
    			'status_code'=>200,
    			'message'=> 'Purchase Added Successfully',
    			'data'  =>  $purchase
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
