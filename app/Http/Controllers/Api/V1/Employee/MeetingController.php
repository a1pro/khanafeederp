<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Access\User\User;
use App\Models\Meeting;
use DB;
use Auth;
use Validation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\EmployeeLocation;
use Illuminate\Support\Str;


class MeetingController extends Controller
{
public function meeting(Request $request)
{
    try {
        $user = Auth::guard('employee')->user();

        if (!$user || !$user->id) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Authentication required.'
            ], 401);
        }

        $now = Carbon::now('Asia/Kolkata');

        // Validate input
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'nullable|file|mimetypes:image/*|max:5120',
            'role' => 'required|in:Dealer,Farmer'
        ]);

        $clientType = $request->header('X-Client-Type') ??
            (Str::contains($request->userAgent(), ['Android', 'iPhone', 'Mobile']) ? 'mobile' : 'web');

        DB::beginTransaction();

        // Save meeting
        $meeting = new Meeting();
        $meeting->emp_id = $user->id;
        $meeting->latitude = $request->latitude;
        $meeting->longitude = $request->longitude;
        $meeting->date = $now->toDateString();
        $meeting->time = $now->toTimeString();
        $meeting->agent_type = $clientType;
        $meeting->role = $request->role;

        // Dealer or Farmer logic
        if ($request->role === 'Dealer') {
            $request->validate([
                'dealer' => 'required|string|max:255',
                'brand' => 'required|array',
                'brand.*' => 'string|max:255',
            ]);

            $meeting->dealer = $request->dealer;
            $meeting->dealer_name = $request->dealer_name;
            $meeting->firm_name = $request->firm_name;
            $meeting->brand = json_encode($request->brand);
            $meeting->phone = $request->phone;
            $meeting->sale = $request->sale;
            $meeting->state = $request->state;
            $meeting->data = $request->data;
            $meeting->meeting_note = $request->meeting_note;
        } else {
            $meeting->dealer = $request->role;
            $meeting->farmer_name = $request->farmer_name;
            $meeting->dob = $request->dob;
            $meeting->anniversay_date = $request->anniversay_date;
            $meeting->phone = $request->phone;
            $meeting->address = $request->address;
            $meeting->record = $request->cattlerecord;
            $meeting->cattle_buff = $request->cattle_buff;
            $meeting->cattle_cow = $request->cattle_cow;
            $meeting->cattle_calf = $request->cattle_calf;
            $meeting->trail_bag = $request->trail_bag;
            $meeting->schedule = $request->schedule;
            $meeting->state = $request->state;
            $meeting->meeting_note = $request->meeting_note;
            $meeting->bags = $request->bags;
        }

        if ($request->hasFile('image')) {
            $meeting->image = $request->file('image')->store('meeting_images', 'public');
        }

        $meeting->save();

        // 🔁 Store all location records in employee_locations (multiple per day)
         EmployeeLocation::create([
            'emp_id' => $user->id,
            'meeting_date' => $now->toDateString(),
            'meeting_time' => $now->toTimeString(),
            'meeting_lat' => $request->latitude,
            'meeting_long' => $request->longitude,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Meeting and location recorded successfully.',
            'data' => $meeting
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'status_code' => 500,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ]);
    }
}


// ============================Get Meeting=====================================
public function getMeeting()
{
    if (!Auth::guard('employee')->check()) {
        return response()->json([
            'success' => false,
            'status_code' => 401,
            'message' => 'Employee not authenticated',
        ]);
    }

    try {
        $userId = Auth::guard('employee')->id();

        // Optional caching
        $meeting = Cache::remember("employee_meetings_{$userId}", 60, function () use ($userId) {
            return Meeting::select('id', 'dealer', 'data', 'image', 'date')
                ->where('emp_id', $userId)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($item) {
                    $item->image = $item->image ? asset('storage/' . $item->image) : null;
                    return $item;
                });
        });

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Meeting data fetched successfully',
            'data' => $meeting
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
