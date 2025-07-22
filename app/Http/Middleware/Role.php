<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::guard('web')->user();
        if (!$user->role) {
            Auth::guard('web')->logout();
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role yang valid.');
        }

        if (in_array($user->role->nama_role, $roles)) {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}