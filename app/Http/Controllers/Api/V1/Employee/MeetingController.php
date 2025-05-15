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

        // Optional: Validate required fields
        $request->validate([
            'dealer' => 'required|string|max:255',
            'data' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        DB::beginTransaction();

        $meeting = new Meeting();
        $meeting->emp_id = $user->id;
        $meeting->dealer = $request->dealer;
        $meeting->data = $request->data;
        $meeting->latitude = $request->latitude;
        $meeting->longitude = $request->longitude;
        $meeting->date = $now->toDateString();
        $meeting->time = $now->toTimeString();

        if ($request->hasFile('image')) {
            $meeting->image = $request->file('image')->store('meeting_images', 'public');
        }

        $meeting->save();
        DB::commit();

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'message' => 'Meeting recorded successfully.',
            'data' => $meeting
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
