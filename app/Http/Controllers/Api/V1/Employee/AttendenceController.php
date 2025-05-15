<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;


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

        // Prevent multiple clock-ins in 24 hours
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Attendance already marked for today.'], 400);
        }

        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->company_id = $user->company_id;
        $attendance->employee_id = $user->id;
        $attendance->date = $now->toDateString();
        $attendance->clock_in = $now->toTimeString();
        $attendance->clock_in_latitude = $request->input('latitude');
        $attendance->clock_in_longitude = $request->input('longitude');

        // ✅ Save clock-in meter reading and image
        $attendance->meter_reading = $request->input('meter_reading');

        if ($request->hasFile('meter_image')) {
            $attendance->meter_image = $request->file('meter_image')->store('attendance_images', 'public');
        }

        $attendance->save();

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
        $now = Carbon::now('Asia/Kolkata')->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::now('Asia/Kolkata')->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        if ($attendance->break_start_time) {
            return response()->json(['success' => false, 'message' => 'Break already started.'], 400);
        }

        $attendance->break_start_time = $now;
        $attendance->break_start_latitude = $request->input('latitude');
        $attendance->break_start_longitude = $request->input('longitude');
        $attendance->save();

        return response()->json(['success' => true, 'message' => 'Break started.', 'data' => $attendance]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}



public function endBreak(Request $request)
{
    try {
        $user = Auth::guard('employee')->user();
        $now = Carbon::now('Asia/Kolkata')->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::now('Asia/Kolkata')->toDateString())
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

        $attendance->break_end_time = $now;
        $attendance->break_end_latitude = $request->input('latitude');
        $attendance->break_end_longitude = $request->input('longitude');
        $attendance->save();

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
        $now = Carbon::now('Asia/Kolkata')->toTimeString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::now('Asia/Kolkata')->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found.'], 404);
        }

        if ($attendance->clock_out) {
            return response()->json(['success' => false, 'message' => 'Already checked out.'], 400);
        }

        $attendance->clock_out = $now;
        $attendance->clock_out_latitude = $request->input('latitude');
        $attendance->clock_out_longitude = $request->input('longitude');
        $attendance->clock_out_meter_reading = $request->input('meter_reading'); // ✅ update for end

        if ($request->hasFile('meter_image')) {
            $attendance->clock_out_meter_image = $request->file('meter_image')->store('attendance_images', 'public');
        }

        $attendance->save();
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
                'status_code' => 404,
                'message' => 'No attendance found for today.',
            ], 404);
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
