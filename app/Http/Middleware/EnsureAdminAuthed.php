<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdminAuthed
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            $request->session()->put('url.intended.admin', $request->fullUrl());

            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
