<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAreaAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin has full access to all areas
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Extract area from route parameters
        $area = $request->route('area');
        $areaId = is_object($area) ? $area->id : $area;

        if ($areaId) {
            $hasAccess = $user->areas()->where('areas.id', $areaId)->exists();

            if (!$hasAccess) {
                abort(403, 'Unauthorized access to this accreditation area.');
            }
        }

        return $next($request);
    }
}
