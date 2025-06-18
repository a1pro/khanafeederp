<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        // Check if the employee is authenticated
        if (!Auth::guard('employee')->check()) {
            return response()->json([
                'success'     => false,
                'status_code' => 401,
                'message'     => 'Employee not authenticated',
            ]);
        }
    
        // Validate input passwords
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8', // requires new_password_confirmation
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status_code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ]);
        }
    
        DB::beginTransaction();
    
        try {
            // Get authenticated employee
            $employee = Auth::guard('employee')->user();
    
            // Check if current password matches
            if (!Hash::check($request->current_password, $employee->password)) {
                return response()->json([
                    'success'     => false,
                    'status_code' => 400,
                    'message'     => 'Current password is incorrect.',
                ]);
            }
    
            // Check if new password is different from current
            if (Hash::check($request->new_password, $employee->password)) {
                return response()->json([
                    'success'     => false,
                    'status_code' => 400,
                    'message'     => 'New password must be different from the current password.',
                ]);
            }
    
            // Update password
            $employee->password = Hash::make($request->new_password);
            $employee->save();
    
            DB::commit();
    
            return response()->json([
                'success'     => true,
                'status_code' => 200,
                'message'     => 'Password changed successfully.',
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success'     => false,
                'status_code' => 500,
                'message'     => 'Something went wrong: ' . $e->getMessage(),
            ]);
        }
    }
    
}
