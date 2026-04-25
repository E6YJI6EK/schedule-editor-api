<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return errorResponse('Неавторизован', 401);
        }

        $allowed = array_map(static fn (string $role) => strtoupper($role), $roles);
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if (!in_array($userRole, $allowed, true)) {
            return errorResponse('Доступ запрещен', 403);
        }

        return $next($request);
    }
}
