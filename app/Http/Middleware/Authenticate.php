<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Storage;

class Authenticate extends Middleware
{

    //add an array of routes to skip santize check

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        $installed = Storage::disk('public')->exists('installed');
    
        if ($installed === false) {
            return route('setup');
        }
    
        // API Case — if it's an API request, return JSON instead of redirect
        if ($request->expectsJson() || $request->is('api/*')) {
            // Return a proper API JSON error for unauthenticated
            abort(response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Unauthenticated.',
            ], 401));
        }
    
        // Web store login
        if ($request->is('store') || $request->is('store/*')) {
            return route('store.login');
        }
    
        // Default web login
        return route('login');
    }
    
}
