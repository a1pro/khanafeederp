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
            $today = date('Y-m-d');
            $noonTime = strtotime("$today 12:00:00");
    
            // Get all employees with their company/department/designation info
            $employees = DB::table('employees')
                ->join('companies', 'employees.company_id', '=', 'companies.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->join('designations', 'employees.designation_id', '=', 'designations.id')
                ->select(
                    'employees.id as employee_id',
                    'employees.firstname',
                    'employees.lastname',
                    'employees.email',
                    'companies.name as company_name',
                    'departments.department as department_name',
                    'designations.designation'
                )
                ->get();
    
            // Get today's attendance records
            $attendances = DB::table('attendances')
                ->whereDate('date', $today)
                ->get()
                ->keyBy('employee_id');
    
            // Combine records
            $records = $employees->map(function ($employee) use ($attendances, $noonTime) {
                $attendance = $attendances[$employee->employee_id] ?? null;
    
                $record = (object)[
                    'firstname' => $employee->firstname,
                    'lastname' => $employee->lastname,
                    'email' => $employee->email,
                    'company_name' => $employee->company_name,
                    'department_name' => $employee->department_name,
                    'designation' => $employee->designation,
                    'status' => 'Absent',
                    'clock_in' => null,
                    'clock_out' => null,
                    'meter_image' => null,
                    'clock_out_meter_image' => null,
                    'meter_reading' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'date' => date('Y-m-d'),
                    'break_start_time' => null,
                    'break_end_time' => null,
                    'break_start_latitude' => null,
                    'break_start_longitude' => null,
                    'break_end_latitude' => null,
                    'break_end_longitude' => null,
                    'clock_out_latitude' => null,
                    'clock_out_longitude' => null,
                    'clock_out_meter_reading' => null,
                    'agent_type' => null,
                ];
    
                if ($attendance) {
                    $clockInTimestamp = strtotime($attendance->clock_in);
                    $record->status = ($clockInTimestamp <= $noonTime) ? 'Present' : 'Late';
    
                    // Fill attendance data
                    $record->clock_in = $attendance->clock_in;
                    $record->clock_out = $attendance->clock_out;
                    $record->meter_image = $attendance->meter_image 
                        ? url('storage/' . ltrim($attendance->meter_image, '/')) 
                        : null;
                    $record->clock_out_meter_image = $attendance->clock_out_meter_image 
                        ? url('storage/' . ltrim($attendance->clock_out_meter_image, '/')) 
                        : null;
                    $record->meter_reading = $attendance->meter_reading;
                    $record->latitude = $attendance->latitude;
                    $record->longitude = $attendance->longitude;
                    $record->break_start_time = $attendance->break_start_time;
                    $record->break_end_time = $attendance->break_end_time;
                    $record->break_start_latitude = $attendance->break_start_latitude;
                    $record->break_start_longitude = $attendance->break_start_longitude;
                    $record->break_end_latitude = $attendance->break_end_latitude;
                    $record->break_end_longitude = $attendance->break_end_longitude;
                    $record->clock_out_latitude = $attendance->clock_out_latitude;
                    $record->clock_out_longitude = $attendance->clock_out_longitude;
                    $record->clock_out_meter_reading = $attendance->clock_out_meter_reading;
                    $record->agent_type = $attendance->agent_type;
                }
    
                return $record;
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