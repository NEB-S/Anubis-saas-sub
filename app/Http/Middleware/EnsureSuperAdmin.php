<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->user()?->is_super_admin) {
            abort(403, 'Only super admins can access this.');
        }

        return $next($request);
    }
}
