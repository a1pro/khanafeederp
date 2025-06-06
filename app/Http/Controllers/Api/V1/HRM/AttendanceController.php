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
                'employees.state',
                'employees.phone',
                'employees.status',
                'companies.name as company_name',
                'departments.department as department_name',
                'designations.designation'
            )
            ->where('employees.status', 'Active')
            ->get()
            ->keyBy('employee_id');

        // Fetch all attendance records
        $attendanceRecords = DB::table('attendances')->get();

        // Group attendances by employee
        $grouped = $attendanceRecords->groupBy('employee_id');

        $allRecords = collect();

        foreach ($employees as $employee_id => $employee) {
            $employeeAttendances = $grouped[$employee_id] ?? collect();

            if ($employeeAttendances->isEmpty()) {
                $allRecords->push((object)[
                    'employee_id' => $employee_id,
                    'firstname' => $employee->firstname,
                    'lastname' => $employee->lastname,
                    'email' => $employee->email,
                    'company_name' => $employee->company_name,
                    'department_name' => $employee->department_name,
                    'designation' => $employee->designation,
                    'state' => $employee->state,
                    'status' => 'Absent',
                    'date' => null,
                    'clock_in' => null,
                    'clock_out' => null,
                    'meter_image' => null,
                    'clock_out_meter_image' => null,
                    'meter_reading' => null,
                    'latitude' => null,
                    'longitude' => null,
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
                ]);
            } else {
                foreach ($employeeAttendances as $attendance) {
                    $noonTime = strtotime(date('Y-m-d', strtotime($attendance->date)) . ' 12:00:00');
                    $clockInTimestamp = strtotime($attendance->clock_in);

                    $status = 'Absent';
                    if ($attendance->clock_in) {
                        $status = ($clockInTimestamp <= $noonTime) ? 'Present' : 'Late';
                    }

                    $allRecords->push((object)[
                        'employee_id' => $employee_id,
                        'firstname' => $employee->firstname,
                        'lastname' => $employee->lastname,
                        'email' => $employee->email,
                        'company_name' => $employee->company_name,
                        'department_name' => $employee->department_name,
                        'designation' => $employee->designation,
                        'state' => $employee->state,
                        'phone'=> $employee->phone,
                        'status' => $status,
                        'date' => $attendance->date,
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'meter_image' => $attendance->meter_image ? url('storage/' . ltrim($attendance->meter_image, '/')) : null,
                        'clock_out_meter_image' => $attendance->clock_out_meter_image ? url('storage/' . ltrim($attendance->clock_out_meter_image, '/')) : null,
                        'meter_reading' => $attendance->meter_reading,
                        'latitude' => $attendance->latitude,
                        'longitude' => $attendance->longitude,
                        'break_start_time' => $attendance->break_start_time,
                        'break_end_time' => $attendance->break_end_time,
                        'break_start_latitude' => $attendance->break_start_latitude,
                        'break_start_longitude' => $attendance->break_start_longitude,
                        'break_end_latitude' => $attendance->break_end_latitude,
                        'break_end_longitude' => $attendance->break_end_longitude,
                        'clock_out_latitude' => $attendance->clock_out_latitude,
                        'clock_out_longitude' => $attendance->clock_out_longitude,
                        'clock_out_meter_reading' => $attendance->clock_out_meter_reading,
                        'agent_type' => $attendance->agent_type,
                    ]);
                }
            }
        }

        // Optional: sort by date descending
        $sortedRecords = $allRecords->sortByDesc('date')->values();

        DB::commit();

        return response()->json([
            'success' => true,
            'data' => $sortedRecords
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

    

    // ===========================HRM Sales Attendance=================================
    public function employeeAttendanceDetails(Request $request)
{
    try {
        // Parse date range from request or use current month's range
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        // Get all active employees with designation
        $employees = DB::table('employees')
            ->join('designations', 'employees.designation_id', '=', 'designations.id')
            ->select(
                'employees.id as employee_id',
                'employees.firstname',
                'employees.lastname',
                'employees.state',
                'designations.designation'
            )
            ->where('employees.status', 'Active')
            ->get();

        // Get all attendance records in the selected date range
        $attendances = DB::table('attendances')
            ->select('employee_id', 'date', 'clock_in', 'clock_out')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->groupBy('employee_id');

        // Get all unique dates in range
        $dates = DB::table('attendances')
            ->select('date')
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->toArray();

        $employeeDetails = [];

        foreach ($employees as $employee) {
            $employeeData = [
                'employee_id' => $employee->employee_id,
                'name' => $employee->firstname . ' ' . $employee->lastname,
                'state' => $employee->state,
                'designation' => $employee->designation,
                'attendance' => [],
                'monthly_hours' => [],
                'monthly_present_days' => [],
                'total_working_hours' => 0 // NEW: Total working hours in selected range
            ];

            $employeeAttendances = $attendances[$employee->employee_id] ?? collect();

            $monthlyHours = [];
            $monthlyPresentCount = [];
            $totalWorkingHours = 0;

            foreach ($dates as $date) {
                $attendance = $employeeAttendances->firstWhere('date', $date);
                $status = 'Absent';
                $dailyHours = 0;

                if ($attendance && $attendance->clock_in) {
                    $status = 'Present';

                    if ($attendance->clock_out) {
                        $start = strtotime($attendance->clock_in);
                        $end = strtotime($attendance->clock_out);
                        $diffInSeconds = $end - $start;
                        $dailyHours = round($diffInSeconds / 3600, 2);
                        $totalWorkingHours += $dailyHours;
                    }

                    // Monthly present count
                    $month = date('Y-m', strtotime($date));
                    if (!isset($monthlyPresentCount[$month])) {
                        $monthlyPresentCount[$month] = 0;
                    }
                    $monthlyPresentCount[$month]++;
                }

                // Monthly hours
                $month = date('Y-m', strtotime($date));
                if (!isset($monthlyHours[$month])) {
                    $monthlyHours[$month] = 0;
                }
                $monthlyHours[$month] += $dailyHours;

                $employeeData['attendance'][$date] = [
                    'status' => $status,
                    'daily_hours' => $dailyHours
                ];
            }

            $employeeData['monthly_hours'] = $monthlyHours;
            $employeeData['monthly_present_days'] = $monthlyPresentCount;
            $employeeData['total_working_hours'] = $totalWorkingHours;

            $employeeDetails[] = $employeeData;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employeeDetails,
                'dates' => $dates,
                'range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}


}