<?php

namespace App\Http\Middleware;

use App\Models\TokenBot;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->headers->get('TOKEN');

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        $tokenData = TokenBot::where('token', $token)->first();

        if (!$tokenData) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
