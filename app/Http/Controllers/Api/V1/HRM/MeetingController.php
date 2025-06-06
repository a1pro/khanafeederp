<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Employee;
use DB;


class MeetingController extends Controller
{
	    public function meeting()
	    {
	    	DB::beginTransaction();

	    	try{
	    		$records= DB::table('meetings')
	            ->join('employees', 'meetings.emp_id', '=', 'employees.id')
	            ->join('companies', 'employees.company_id', '=', 'companies.id')
	            ->join('departments', 'employees.department_id', '=', 'departments.id')
	            ->join('designations', 'employees.designation_id', '=', 'designations.id')
	            
	            ->select(
	                'employees.firstname',
	                'employees.lastname',
	                'employees.email',
	                'employees.state',
	                'employees.phone',
	                'companies.name as company_name',
	                'departments.department as department_name',
	                'designations.designation',
	                'meetings.id',
	                'meetings.dealer',
	                'meetings.data',
	                'meetings.image',
	                'meetings.time',
	                'meetings.latitude',
	                'meetings.longitude',
	                'meetings.date',
	                'meetings.agent_type'
	                
	            )->where('meetings.id','desc')
	            ->get()
	            ->map(function ($item) {
	                $item->image = $item->image 
	                    ? asset('storage/' . $item->image) 
	                    : null;

	              

	                return $item;
	            });
	    		DB::commit();

	    		return response()->json([

	    			'success'  => true,
	    			'success_code'  => 200,
	    			'data'	=>   $records,

	    		]);

	    	}
	    	catch(\Exception $e){
	    		DB::rollback();
	    		return respone()->json([
	    			'success'   =>  false,
	    			'status_code' => 500,
	    			'message'  => 'Something went wrong' .$e->getMessage(),
	    		]);
	    	}
	    }
}
