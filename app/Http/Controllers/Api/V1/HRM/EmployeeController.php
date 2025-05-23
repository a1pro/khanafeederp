<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\Hash;
use App\Models\EmployeeLocation;
use DB;
use Illuminate\Support\Facades\Validator;
class EmployeeController extends Controller
{
    public function employee()
    {
        DB::beginTransaction();
    
        try {
            $employees = DB::table('employees')
                ->leftJoin('companies', 'employees.company_id', '=', 'companies.id')
                ->leftJoin('departments', function($join) {
                    $join->on('employees.department_id', '=', 'departments.id')
                         ->on('departments.company_id', '=', 'employees.company_id');
                })
                ->leftJoin('designations', function($join) {
                    $join->on('employees.designation_id', '=', 'designations.id')
                         ->on('designations.company_id', '=', 'employees.company_id')
                         ->on('designations.department_id', '=', 'employees.department_id');
                })
                ->select(
                    'employees.firstname',
                    'employees.lastname',
                    'employees.username',
                    'employees.phone',
                    'employees.email',
                    'employees.state',

                    'employees.id as emp_id',
                    'employees.status as emp_status',
                    'companies.name as company_name',
                    'departments.department as department_name',
                    'designations.designation as designation_name'
                )
                ->where('employees.status', 'Active')
                ->orderBy('emp_id', 'desc')
                ->get();
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }


    // ==================Employee Delete========================================

 
public function deleteEmployee(Request $request)
{
    DB::beginTransaction();

    try {
        $id = $request->input('id');

        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'status_code' => 404,
                'message' => 'Employee not found'
            ], 404);
        }

        // Instead of deleting, update the status to "Inactive"
        $employee->status = 'Inactive';
        $employee->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Employee status set to Inactive successfully'
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}




// ===================Employee Location==========================
public function getEmployeeLocation()
{
    DB::beginTransaction();
    try {
        // Fetch employee location with employee name and phone
        $locations = EmployeeLocation::select(
                'employee_locations.*',
                'employees.firstname as employee_name',
                'employees.phone'
            )
            ->leftJoin('employees', 'employee_locations.emp_id', '=', 'employees.id')
            ->orderBy('employee_locations.id', 'desc')
            ->get();

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Employee locations fetched successfully.',
            'data' => $locations
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch employee locations: ' . $e->getMessage()
        ], 500);
    }
}



// ===================Employee Update Profile=========================
   
public function updateEmploye(Request $request)
{
    $this->validate($request,[
        'firstname' => 'required|string',
        'lastname'  =>  'required|string',
        'phone'     =>  'required',
        'password'  =>  'required'
       
    ]);
    DB::beginTransaction();

    try {
        $id = $request->id;

        // Fetch the employee by ID
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Employee not found'
            ]);
        }

        // Update the employee's data
        $employee->firstname = $request->firstname;
        $employee->lastname  = $request->lastname;
        $employee->phone     = $request->phone;
        $employee->email     = $request->email;

        // Only update password if provided
        if ($request->filled('password')) {
            $employee->password = Hash::make($request->password);
        }

        $employee->save();

        DB::commit();

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => 'Employee updated successfully',
            'data' => $employee
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}


// =====================Employee Count======================
public function countUser()
{
    try{
        $count = Employee::count();
        return response()->json(['success'=>true,'status_code'=>200,'message'=>'User Count Fetch Successfully','data'=>$count]);
    }
    catch(\Exception $e){
        return response()->json(['success'=>false,'status_code'=>500,'message'=>'Something went wrong' .$e->getMessage()]);
    }
}

}