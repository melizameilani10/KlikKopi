<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pemakaian di route: ->middleware('role:admin') atau 'role:admin,manager'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Belum login (mis. sesi kedaluwarsa): arahkan ke login, bukan 403.
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $role = strtolower((string) $request->user()->role);

        if (! in_array($role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
