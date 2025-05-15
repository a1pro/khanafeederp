<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
 
use DB;

class EmployeeController extends Controller
{
    public function employee()
{
    DB::beginTransaction();

    try {
        $employees = DB::table('employees')
            ->join('companies', 'employees.company_id', '=', 'companies.id')
            ->join('departments', function($join) {
                $join->on('employees.department_id', '=', 'departments.id')
                     ->on('departments.company_id', '=', 'employees.company_id');
            })
            ->join('designations', function($join) {
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
                'companies.name as company_name',
                'departments.department as department_name',
                'designations.designation as designation_name'
            )
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
}
