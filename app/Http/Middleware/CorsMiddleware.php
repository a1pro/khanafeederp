<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
{
    $allowedOrigins = [
        'http://localhost:3000',
        'https://khannatesting.a1professionals.net',
        'https://khannafeederpweb.a1professionals.net',
        'https://your-other-allowed-domain.com',
    ];

    $origin = $request->headers->get('Origin');

    $response = $next($request);

    if ($request->getMethod() === "OPTIONS") {
        return response('', 204)->withHeaders([
            'Access-Control-Allow-Origin'      => in_array($origin, $allowedOrigins) ? $origin : '',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization',
            'Access-Control-Allow-Credentials' => 'true',
        ]);
    }

    if (in_array($origin, $allowedOrigins)) {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
    }

    return $response;
}

}
