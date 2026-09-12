<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if ($user) {
            if (! $user->is_active) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Your account has been deactivated. Please contact Super Admin.');
            }

            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            if (empty($permissions)) {
                return $next($request);
            }

            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    return $next($request);
                }
            }

            abort(403, 'Unauthorized action. You do not have permission to access this resource.');
        }

        if (! app()->environment('testing')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
