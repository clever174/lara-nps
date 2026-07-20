<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key', '');
        $expectedKey = (string) config('api.key');

        if ($key === '' || $expectedKey === '' || ! hash_equals($expectedKey, $key)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
