<?php

namespace App\Http\Controllers\Api\V1\State;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;

class StateController extends Controller
{
    public function state()
    {
        try{
        $states = State::whereIn('state_title', [
            'Punjab',
            'Gujarat',
            'Uttar Pradesh',
            'Madhya Pradesh',
            'Rajasthan',
            'Haryana',
            'Maharashtra'
        ])->get();

        return response()->json(['success'=>true,'status_code'=>200,'message'=>'state data fetch successfully','data'=>$states]);
        }

        catch(\Exception $e){
            return response()->json(['success'=>false,'status_code'=>500,'message'=>'Something went wrong' .$e->getMessage()]);
        }

    }
}

