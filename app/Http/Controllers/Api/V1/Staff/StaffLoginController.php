<?php

namespace App\Http\Controllers\Api\V1\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
 use App\Models\User;

class StaffLoginController extends Controller
{
  
public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success'     => false,
            'status_code' => 401,
            'message'     => 'Invalid credentials',
        ]);
    }

    // ✅ Now this works with Sanctum
    $token = $user->createToken('UserAppToken')->plainTextToken;

    return response()->json([
        'success'      => true,
        'status_code'  => 200,
        'message'      => 'Logged in successfully',
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'user'         => [
            'firstname' => $user->firstname,
            'email'     => $user->email,
            'role'      => $user->role_id,
            'id'        => $user->id,
        ],
    ]);
}

}
