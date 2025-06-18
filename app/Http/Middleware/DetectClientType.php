<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectClientType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
      public function handle(Request $request, Closure $next)
    {
        $clientType = $request->header('X-Client-Type');

        if (!$clientType) {
            $userAgent = $request->userAgent();
            $clientType = Str::contains($userAgent, ['Android', 'iPhone', 'Mobile']) ? 'mobile' : 'web';
        }

        // Attach to request object
        $request->attributes->set('client_type', $clientType);

        return $next($request);
    }
}
