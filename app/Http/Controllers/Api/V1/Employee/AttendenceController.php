<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\EmployeeLocation;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AttendenceController extends Controller
{
public function startAttendance(Request $request)
{
    DB::beginTransaction();

    try {
        $user = Auth::guard('employee')->user();

        if (!$user || !$user->company_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $now = Carbon::now('Asia/Kolkata');

        // Check for duplicate attendance for today
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Attendance already marked for today.'], 400);
        }

        // Detect agent type: from header or fallback to user-agent
        $clientType = $request->header('X-Client-Type');
        if (!$clientType) {
            $userAgent = $request->userAgent();
            $clientType = Str::contains($userAgent, ['Android', 'iPhone', 'Mobile']) ? 'mobile' : 'web';
        }

        // Save attendance
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->company_id = $user->company_id;
        $attendance->employee_id = $user->id;
        $attendance->date = $now->toDateString();
        $attendance->clock_in = $now->toTimeString();
        $attendance->clock_in_latitude = $request->input('clock_in_latitude');
        $attendance->clock_in_longitude = $request->input('clock_in_longitude');
        $attendance->meter_reading = $request->input('meter_reading');
        $attendance->agent_type = $clientType; // ✅ Save agent type

        if ($request->hasFile('meter_image')) {
            $attendance->meter_image = $request->file('meter_image')->store('attendance_images', 'public');
        }

        $attendance->save();

        // Save employee location
        $location = new EmployeeLocation();
        $location->emp_id = $user->id;
        $location->check_in_lat = $request->input('clock_in_latitude');
        $location->check_in_long = $request->input('clock_in_longitude');
        $location->attendance_date = $now->toDateString();
        $location->attendance_time = $now->toTimeString();
        $location->locationcode = 'LOC-EMP' . $user->id . '-DATE' . $now->format('Ymd') . '-RAND' . Str::upper(Str::random(4));
        $location->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Clock-in recorded.',
            'data' => $attendance
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}
public function startBreak(Request $request)
{
    try {
        $user = Auth::guard('employee')->user();
        $now = Carbon::now('Asia/Kolkata');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        if ($attendance->break_start_time) {
            return response()->json(['success' => false, 'message' => 'Break already started.'], 400);
        }

        $attendance->break_start_time = $now->toTimeString();
        $attendance->break_start_latitude = $request->input('latitude');
        $attendance->break_start_longitude = $request->input('longitude');
        $attendance->save();


        $location = EmployeeLocation::where('emp_id', $user->id)
            ->whereDate('attendance_date', $now->toDateString())
            ->first();

        if ($location) {
            $location->break_in_lat = $request->input('latitude');
            $location->break_in_long = $request->input('longitude');
            $location->save();
        }

        return response()->json(['success' => true, 'message' => 'Break started.', 'data' => $attendance]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

public function endBreak(Request $request)
{
    try {
        $user = Auth::guard('employee')->user();
        $now = Carbon::now('Asia/Kolkata');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        if (!$attendance->break_start_time) {
            return response()->json(['success' => false, 'message' => 'Break not started.'], 400);
        }

        if ($attendance->break_end_time) {
            return response()->json(['success' => false, 'message' => 'Break already ended.'], 400);
        }

        $attendance->break_end_time = $now->toTimeString();
        $attendance->break_end_latitude = $request->input('latitude');
        $attendance->break_end_longitude = $request->input('longitude');
        $attendance->save();

        $location = EmployeeLocation::where('emp_id', $user->id)
            ->whereDate('attendance_date', $now->toDateString())
            ->first();

        if ($location) {
            $location->break_out_lat = $request->input('latitude');
            $location->break_out_long = $request->input('longitude');
            $location->save();
        }

        return response()->json(['success' => true, 'message' => 'Break ended.', 'data' => $attendance]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

public function endAttendance(Request $request)
{
    DB::beginTransaction();

    try {
        $user = Auth::guard('employee')->user();
        $now = Carbon::now('Asia/Kolkata');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        if ($attendance->clock_out) {
            return response()->json(['success' => false, 'message' => 'Already checked out.'], 400);
        }

        $attendance->clock_out = $now->toTimeString();
        $attendance->clock_out_latitude = $request->input('clock_out_latitude');
        $attendance->clock_out_longitude = $request->input('clock_out_longitude');
        $attendance->clock_out_meter_reading = $request->input('meter_reading');

        if ($request->hasFile('meter_image')) {
            $attendance->clock_out_meter_image = $request->file('meter_image')->store('attendance_images', 'public');
        }

        $attendance->save();

        // Update employee location
        $location = EmployeeLocation::where('emp_id', $user->id)
            ->where('attendance_date', $now->toDateString())
            ->first();

        if ($location) {
            $location->check_out_lat = $request->input('clock_out_latitude');
            $location->check_out_long = $request->input('clock_out_longitude');
            $location->attendance_time = $now->toTimeString();
            $location->save();
        }

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Clock-out recorded.', 'data' => $attendance]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

public function getAttendence()
{
    try {
        $user = Auth::guard('employee')->user();

        if (!$user || !$user->id) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Unauthorized user',
            ], 401);
        }

        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $attendance = Attendance::select(
                'clock_in',
                'clock_out',
                'date',
                'break_start_time',
                'break_end_time',
          
            )
            ->where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'status_code' => 200,
                'message' => 'No attendance found for today.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Attendance data retrieved successfully.',
            'data' => $attendance
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ]);
    }
}

    
}
