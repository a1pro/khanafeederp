<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
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
            $absentCount = 0;
    
            foreach ($allEmployeeIds as $employeeId) {
                $attendance = $attendances[$employeeId] ?? null;
    
                if ($attendance) {
                    $clockInTime = strtotime($attendance->clock_in);
                    if ($clockInTime <= $noonTime) {
                        $presentCount++; // Present before 12 PM
                    } else {
                        $presentCount++; // Optional: you could classify as 'Late' separately
                    }
                } else {
                    $absentCount++;
                }
            }
    
            return response()->json([
                'success' => true,
                'data' => [
                    'present' => $presentCount,
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
    
}
