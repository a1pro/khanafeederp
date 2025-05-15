<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use DB;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
 
	public function attendance()
{
    DB::beginTransaction();

    try {
        $user = Auth::user();

        $records = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->join('companies', 'employees.company_id', '=', 'companies.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->join('designations', 'employees.designation_id', '=', 'designations.id')
            
            ->select(
                'employees.firstname',
                'employees.lastname',
                'employees.email',
                'companies.name as company_name',
                'departments.department as department_name',
                'designations.designation',
                'attendances.clock_in',
                'attendances.clock_out',
                'attendances.meter_image',
                'attendances.clock_out_meter_image',
                'attendances.meter_reading',
                'attendances.latitude',
                'attendances.longitude',
                'attendances.break_start_time',
                'attendances.break_end_time',
                'attendances.break_start_latitude',
                'attendances.break_start_longitude',
                'attendances.break_end_latitude',
                'attendances.break_end_longitude',
                'attendances.clock_out_latitude',
                'attendances.clock_out_longitude',
                'attendances.clock_out_meter_reading'
            )
            ->get()
            ->map(function ($item) {
                $item->meter_image_url = $item->meter_image 
                    ? asset('storage/' . $item->meter_image) 
                    : null;

                $item->clock_out_meter_image_url = $item->clock_out_meter_image 
                    ? asset('storage/' . $item->clock_out_meter_image) 
                    : null;

                return $item;
            });

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $records
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}