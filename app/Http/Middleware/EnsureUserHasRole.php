<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator sekolah.']);
        }

        // If specific roles are required, verify that user possesses at least one
        if (! empty($roles) && ! $user->hasRole($roles)) {
            abort(403, 'Akses tidak diizinkan. Peran akun Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}

