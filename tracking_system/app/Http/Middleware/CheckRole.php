<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Redirect ke dashboard masing-masing jika salah akses
        return match ($userRole) {
            'Admin'    => redirect()->route('admin.dashboard'),
            'Aslap'    => redirect()->route('aslap.dashboard'),
            'Gizi'     => redirect()->route('gizi.dashboard'),
            'Akuntan'  => redirect()->route('akuntan.dashboard'),
            default    => redirect('/'),
        };
    }
}
