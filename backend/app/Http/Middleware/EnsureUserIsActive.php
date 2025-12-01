<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
    * Handle an incoming request.
    */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account inactive. Contact admin.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is inactive. Please contact an admin.',
            ]);
        }

        return $next($request);
    }
}
