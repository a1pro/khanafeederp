<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use DB;

class EmployeProfileController extends Controller
{
    public function profile()
    {
        if (!Auth::guard('employee')->check()) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Employee not authenticated',
            ]);
        }
    
        DB::beginTransaction();
    
        try {
            $employee = Auth::guard('employee')->user(); // already the Employee model instance
    
            // Load company and designation relationships
            $employee->load('company', 'designation');
    
            $data = [
                'username'    => $employee->username,
                'company'     => optional($employee->company)->name,           // Use optional() to avoid null errors
                'designation' => optional($employee->designation)->designation // Same here
            ];
    
            DB::commit();
    
            return response()->json([
                'success'     => true,
                'status_code' => 200,
                'data'        => $data,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success'     => false,
                'status_code' => 500,
                'message'     => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
    
}
