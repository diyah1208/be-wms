<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInputOpen
{
    public function handle($request, Closure $next)
    {
        $today = now()->day;

        if ($today === 5) {
            return response()->json([
                'message' => 'Input ditutup tanggal 5'
            ], 403);
        }

        return $next($request);
    }

}
