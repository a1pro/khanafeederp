<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Meeting;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class DashCounterController extends Controller
{
    public function attendanceSummary()
{
    try {
        $today = date('Y-m-d');
        $noonTime = strtotime("$today 12:00:00");

        // Get all employee IDs
        $allEmployeeIds = DB::table('employees')->pluck('id');

        // Get today's attendance records
        $attendances = DB::table('attendances')
            ->whereDate('date', $today)
            ->get()
            ->keyBy('employee_id');

        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;

        foreach ($allEmployeeIds as $employeeId) {
            $attendance = $attendances[$employeeId] ?? null;

            if ($attendance && $attendance->clock_in) {
                $clockInTime = strtotime($attendance->clock_in);

                if ($clockInTime <= $noonTime) {
                    $presentCount++; // On-time present
                } else {
                    $lateCount++; // Late entry
                }
            } else {
                $absentCount++; // No attendance record
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'total_employees' => count($allEmployeeIds)
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

    


    public function meetingcount()
    {
        try {
            $today = date('Y-m-d');
    
            // Meetings for company_id = 2 (Tarafeed)
            $tarafeed = DB::table('meetings')
                ->join('employees', 'meetings.emp_id', '=', 'employees.id')
                ->join('companies', 'employees.company_id', '=', 'companies.id')
                ->where('employees.company_id', 2)
                ->where('employees.status', 'Active') // ✅ fixed
                ->whereDate('meetings.date', $today)
                ->select('meetings.id as meeting_id', 'meetings.date', 'companies.name as company_name', 'employees.status')
                ->count();
    
            // Meetings for company_id = 3 (Khannafeed)
            $khannafeed = DB::table('meetings')
                ->join('employees', 'meetings.emp_id', '=', 'employees.id')
                ->join('companies', 'employees.company_id', '=', 'companies.id')
                ->where('employees.company_id', 3)
                ->where('employees.status', 'Active') // ✅ fixed
                ->whereDate('meetings.date', $today)
                ->select('meetings.id as meeting_id', 'meetings.date', 'companies.name as company_name')
                ->count();
    
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'message' => 'Meeting data fetched successfully.',
                'data' => [
                    'tarafeed_meetings' => $tarafeed,
                    'khannafeed_meetings' => $khannafeed
                ]
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

}
