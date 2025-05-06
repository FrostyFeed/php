<?php

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 

class RoleMiddleware
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles 
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$requiredRoles)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $user = Auth::user();
        
        foreach ($requiredRoles as $requiredRole) {
            if ($requiredRole === User::ROLE_ADMIN && $user->isAdmin()) {
                return $next($request);
            }
            if ($requiredRole === User::ROLE_MANAGER && ($user->isManager() || $user->isAdmin())) {
                return $next($request);
            }
            if ($requiredRole === User::ROLE_CLIENT && ($user->isClient() || $user->isManager() || $user->isAdmin())) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Forbidden. You do not have the required role for this action.'], 403);
    }
}