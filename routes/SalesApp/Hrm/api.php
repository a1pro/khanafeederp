<?php
use App\Http\Controllers\Api\V1\Employee\EmployeeLogin;
use App\Http\Controllers\Api\V1\Employee\EmployeProfileController;




Route::post('/employee-login', [EmployeeLogin::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/employee-profile', [EmployeProfileController::class, 'profile']);
});
