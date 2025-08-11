<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        // Tambahkan log untuk daftar rute
        \Log::debug('Available routes', Route::getRoutes()->getRoutesByName());

        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                \Log::info('RedirectIfAuthenticated: User authenticated', [
                    'email' => $user->email ?? 'N/A',
                    'guard' => $guard,
                    'role' => $user->role ? $user->role->nama_role : 'null',
                    'session_id' => $request->session()->getId(),
                    'intended_url' => $request->session()->get('url.intended')
                ]);

                if (!$user->role || !in_array($user->role->nama_role, ['administrator', 'karyawan', 'pelanggan'])) {
                    Auth::guard($guard)->logout();
                    \Log::warning('RedirectIfAuthenticated: Invalid or unknown role, logging out', ['email' => $user->email ?? 'N/A']);
                    $request->session()->invalidate();
                    return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role yang valid.');
                }

                if ($user->role->nama_role === 'administrator') {
                    \Log::info('RedirectIfAuthenticated: Redirecting to admin dashboard', ['email' => $user->email]);
                    return redirect()->route('dashboard');
                } elseif ($user->role->nama_role === 'karyawan') {
                    \Log::info('RedirectIfAuthenticated: Redirecting to karyawan dashboard', ['email' => $user->email]);
                    return redirect()->route('karyawan.dashboard');
                } elseif ($user->role->nama_role === 'pelanggan') {
                    \Log::info('RedirectIfAuthenticated: Redirecting to user.home for pelanggan', ['email' => $user->email]);
                    return redirect()->route('user.home');
                }
            }
        }

        \Log::info('RedirectIfAuthenticated: User not authenticated, proceeding', ['session_id' => $request->session()->getId()]);
        return $next($request);
    }
}