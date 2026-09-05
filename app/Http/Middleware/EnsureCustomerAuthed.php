<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomerAuthed
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('customer')->check()) {
            // Remembers the FULL URL (including ?return_url=&state=) so
            // that after logging in, the activation wizard's login()
            // method can send them straight back to /activate instead of
            // the generic dashboard — see PortalAuthController::login().
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('portal.login');
        }

        return $next($request);
    }
}
