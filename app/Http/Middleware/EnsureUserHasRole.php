<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowed = array_map(fn (string $role) => Role::from($role), $roles);

        if ($user === null || ! in_array($user->role, $allowed, true)) {
            return response()->json([
                'message' => 'ليس لديك صلاحية للوصول إلى هذا الطلب.',
            ], 403);
        }

        return $next($request);
    }
}
