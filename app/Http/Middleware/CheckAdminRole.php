<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!auth('admin')->check()) {
        return redirect()->route('admin.login');
    }

    $admin = auth('admin')->user();

    // Super admin has access to everything
    if ($admin->role === 'super_admin') {
        return $next($request);
    }

    // Check if admin has required role
    if (!in_array($admin->role, $roles)) {
        // Redirect to their allowed page instead of 403
        if ($admin->role === 'manager') {
            return redirect()->route('admin.pdf-manager.index')
                ->with('error', 'You do not have permission to access that page.');
        }
        
        abort(403, 'Unauthorized access. You do not have permission to view this page.');
    }

    return $next($request);
}

}
