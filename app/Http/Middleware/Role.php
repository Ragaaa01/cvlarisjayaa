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
            \Log::info('Role Middleware: User not authenticated, redirecting to login', [
                'session_id' => $request->session()->getId()
            ]);
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::guard('web')->user();
        if (!$user->role || !in_array($user->role->nama_role, ['administrator', 'karyawan', 'pelanggan'])) {
            Auth::guard('web')->logout();
            \Log::warning('Role Middleware: Invalid role, logging out', [
                'email' => $user->email ?? 'N/A',
                'role' => $user->role ? $user->role->nama_role : 'null'
            ]);
            $request->session()->invalidate();
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki role yang valid.');
        }

        \Log::debug('Role Middleware: Checking role', [
            'email' => $user->email,
            'user_role' => $user->role->nama_role,
            'required_roles' => $roles,
            'path' => $request->path()
        ]);

        if (in_array($user->role->nama_role, $roles)) {
            \Log::info('Role Middleware: Access granted', [
                'email' => $user->email,
                'role' => $user->role->nama_role,
                'required_roles' => $roles,
                'path' => $request->path()
            ]);
            return $next($request);
        }

        \Log::warning('Role Middleware: Access denied', [
            'email' => $user->email,
            'role' => $user->role->nama_role,
            'required_roles' => $roles,
            'path' => $request->path()
        ]);
        return redirect()->route($user->role->nama_role === 'administrator' ? 'dashboard' : ($user->role->nama_role === 'karyawan' ? 'karyawan.dashboard' : 'user.home'))
            ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}