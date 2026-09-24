<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Endpoint ini khusus Pemilik Kost.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
