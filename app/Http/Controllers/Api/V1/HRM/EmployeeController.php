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
                ->where('employees.company_id', '!=', 1)
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



    // ===============================In active Users================================
    public function Inactiveemployee()
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
                ->where('employees.status', 'Inactive')
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



    // ===========================End=========================================

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
        $locations = EmployeeLocation::select(
                'employee_locations.*',
                'employees.firstname as employee_name',
                'employees.phone',
                'employees.status'
            )
            ->leftJoin('employees', 'employee_locations.emp_id', '=', 'employees.id')
            ->where('employees.status', 'Active')
            ->orderBy('employee_locations.attendance_date', 'desc')
            ->orderBy('employee_locations.id', 'desc')
            ->get()
            ->map(function ($location) {
                // Decode meeting locations JSON
                $location->meeting_locations = $location->meeting_locations 
                    ? json_decode($location->meeting_locations, true) 
                    : [];

                return $location;
            });

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
    $this->validate($request, [
        'id'        => 'required|exists:employees,id',
        'firstname' => 'required|string|max:255',
        'lastname'  => 'required|string|max:255',
        'phone'     => 'required|string|max:20',
        'email'     => 'nullable|email|max:255',
        'company_id'=> 'nullable|exists:companies,id',
        'password'  => 'nullable|string|min:6'
    ]);

    DB::beginTransaction();

    try {
        $employee = Employee::find($request->id);

        // Update fields
        $employee->firstname  = $request->firstname;
        $employee->lastname   = $request->lastname;
        $employee->phone      = $request->phone;
        $employee->email      = $request->email ?? $employee->email;
        $employee->company_id = $request->company_id ?? $employee->company_id;
        $employee->state      = $request->state ?? NULL;
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
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ]);
    }
}



// =====================Employee Count======================
public function countUser()
{
    try {
        // Case 1: All users
        $allUsers = Employee::count();

        // Case 2: Active users
        $activeUsers = Employee::where('status', 'Active')->count();

        // Case 3: Inactive users
        $inactiveUsers = Employee::where('status', 'Inactive')->count();

         

        $tarafeed = DB::table('employees')->join('companies','employees.company_id', '=' ,'companies.id')->select('companies.name','employees.company_id','employees.id')->where('employees.company_id',2)->where('status','Active')->count();


        $khannafeed = DB::table('employees')->join('companies','employees.company_id', '=' ,'companies.id')->select('companies.name','employees.company_id','employees.id')->where('employees.company_id',3)->where('status','Active')->count();

        // Case 5: Active users of company 3
        $company3Active = Employee::where('company_id', 3)->where('status', 'Active')->count();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'User counts fetched successfully.',
            'data' => [
                'all_users' => $allUsers,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
                
            
                'tarafeed' =>  $tarafeed,
                'khannafeed' =>  $khannafeed
            ]
        ]);     
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

// =================Status Update=========================================
    public function employeactive(Request $request)
{
    $id = $request->userId;
 
    DB::beginTransaction();

    try {
        $user = Employee::where('id', $id)->update(['status' => 'Active']);

        DB::commit();

        return response()->json(['message' => 'Employee activated successfully.'], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json(['error' => 'Failed to activate employee.'], 500);
    }
}

}