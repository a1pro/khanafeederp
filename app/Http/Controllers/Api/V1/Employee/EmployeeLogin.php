<?php
  namespace App\Http\Controllers\Api\V1\Employee;

  use App\Http\Controllers\Controller;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\Hash;
  use App\Models\Employee;
  
  class EmployeeLogin extends Controller
  {
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required',
            'password' => 'required|string',
        ]);
    
        $employee = Employee::where('phone', $request->phone)->first();
    
        if (
            !$employee || 
            !Hash::check($request->password, $employee->password) || 
            $employee->status !== 'Active'
        ) {
            return response()->json([
                'success'     => false,
                'status_code' => 401,
                'message'     => 'Invalid credentials or inactive account',
            ]);
        }
    
        // ✅ Create a Sanctum token
        $token = $employee->createToken('EmployeeAppToken')->plainTextToken;
    
        return response()->json([
            'success'      => true,
            'status_code'  => 200,
            'message'      => 'Logged in successfully',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'employee'     => [
                'firstname' => $employee->firstname,
                // 'email'     => $employee->email,
                'phone'     => $employee->phone,
                'username'  => $employee->username,
            ],
        ]);
    }
    
    
    
  }
  