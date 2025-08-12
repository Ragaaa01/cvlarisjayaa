<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate as Middleware;


class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        Log::info('Authenticate middleware: Redirecting to login', [
            'path' => $request->path(),
            'is_authenticated' => Auth::guard('web')->check(),
            'session_id' => session()->getId(),
        ]);
        return $request->expectsJson() ? null : route('login');
    }
}
