<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->role !== 'anggota' || ! $request->user()->anggota) {
            abort(403, 'Halaman ini hanya dapat diakses oleh anggota perpustakaan.');
        }

        return $next($request);
    }
}